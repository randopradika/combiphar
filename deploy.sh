#!/usr/bin/env bash
#
# deploy.sh — Combiphar dev-server deploy (Docker Compose).
#
# Pulls the latest code, rebuilds the app image when needed, installs
# dependencies, builds the front-end assets, runs migrations, and clears
# caches. Idempotent — safe to run repeatedly.
#
# Usage (on the dev server, from the repo root):
#   bash deploy.sh                    # deploy origin/main
#   DEPLOY_BRANCH=some-branch bash deploy.sh
#
# Invoked automatically by .github/workflows/deploy.yml over SSH on every
# push to main.
#
# One-time server requirements (see docs/DEPLOY.md):
#   - git, docker, docker compose on PATH
#   - the repo cloned at this path, with a configured .env
#
set -euo pipefail

BRANCH="${DEPLOY_BRANCH:-main}"

log()  { printf '\n\033[1;35m==> %s\033[0m\n' "$*"; }
fail() { printf '\n\033[1;31mDEPLOY FAILED:\033[0m %s\n' "$*" >&2; exit 1; }

# Always operate from the directory this script lives in (the repo root).
cd "$(dirname "$0")" || fail "cannot cd to script directory"

command -v git    >/dev/null 2>&1 || fail "git not found on PATH"
command -v docker >/dev/null 2>&1 || fail "docker not found on PATH"

# 1) Pull the latest code, then re-exec the freshly-pulled copy of this
#    script, so bash never executes a half-overwritten file. The hard reset
#    mirrors origin exactly: tracked edits are discarded; untracked files
#    (.env, storage/app/public uploads, the local-only OfficesSeeder) survive.
if [ -z "${DEPLOY_REEXEC:-}" ]; then
    log "Fetching latest code (branch '$BRANCH')"
    git fetch --prune origin
    git reset --hard "origin/$BRANCH"
    exec env DEPLOY_REEXEC=1 bash "$(basename "$0")" "$@"
fi

[ -f .env ] || fail ".env is missing — copy .env.example and configure it first (see docs/DEPLOY.md)"

DC="docker compose"
EXEC="$DC exec -T app"

log "Starting / rebuilding containers"
$DC up -d --build

log "Installing PHP dependencies (composer)"
$EXEC composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

log "Installing JS dependencies + building assets"
$EXEC npm ci
$EXEC npm run build

log "Running database migrations"
$EXEC php artisan migrate --force

log "Clearing caches + linking storage"
$EXEC php artisan optimize:clear
# Cache the merged config (skips parsing .env + every config file per request).
# Safe: no runtime env() calls outside config/ (APP_FORCE_HTTPS reads config).
$EXEC php artisan config:cache
# Routes and Blade views are cacheable too — web.php keeps every route
# closure-free for exactly this. The deliberately unnamed fallback routes get
# "generated::" names in the cache; Localize::base() maps those to null, so
# 404 pages behave the same cached as uncached.
$EXEC php artisan route:cache
$EXEC php artisan view:cache
$EXEC php artisan storage:link --force || true

# The SSR daemon (started by the image CMD) caches the bundle at boot; restart
# so it picks up the ssr.js that npm run build just produced.
log "Restarting app container (SSR daemon reloads the fresh bundle)"
$DC restart app

# Verify SSR actually came back. A dead daemon falls back to client-side
# rendering, so the site looks fine while every page ships an empty shell —
# this check is the only thing that makes that failure visible. Non-fatal: a
# CSR-only site still works, and failing the deploy here would help nobody.
log "Verifying SSR daemon"
ssr_ok=0
for _ in 1 2 3 4 5 6 7 8 9 10; do
    if $EXEC curl -sf -m 3 http://127.0.0.1:13714/health >/dev/null 2>&1; then
        ssr_ok=1
        break
    fi
    sleep 3
done

if [ "$ssr_ok" = 1 ] && $EXEC curl -sf -m 10 http://127.0.0.1:80/id 2>/dev/null | grep -q '<section'; then
    log "SSR healthy (daemon up, server-rendered markup confirmed)"
else
    printf '\n\033[1;31mWARNING:\033[0m SSR is NOT serving rendered markup — the site is falling back to CSR.\n' >&2
    printf 'Daemon health on 127.0.0.1:13714: %s\n' "$([ "$ssr_ok" = 1 ] && echo up || echo DOWN)" >&2
    printf -- '--- last 40 lines of storage/logs/ssr.log ---\n' >&2
    $EXEC tail -n 40 storage/logs/ssr.log 2>&1 || echo '(no ssr.log)'
    printf -- '--- end ssr.log ---\n' >&2
fi

log "Deploy complete"
$DC ps
