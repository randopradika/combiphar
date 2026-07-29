# Auto-deploy to the dev server

Every push to `main` triggers **`.github/workflows/deploy.yml`**, which SSHes
into the dev server and runs **`deploy.sh`** there. The deploy runs the app via
**Docker Compose** (same setup as local) and applies **migrations only** — it
does not seed (content is CMS/import-driven, and `OfficesSeeder` is intentionally
local-only).

```
push to main ──▶ GitHub Actions ──▶ ssh dev server ──▶ bash deploy.sh
                                                          ├─ git fetch + reset --hard origin/main
                                                          ├─ docker compose up -d --build
                                                          ├─ composer install (--no-dev)
                                                          ├─ npm ci && npm run build
                                                          ├─ php artisan migrate --force
                                                          └─ php artisan optimize:clear
```

You can also trigger it manually: **Actions tab → “Deploy to dev server” → Run workflow.**

---

## 1. Required GitHub secrets

Set these in the repo: **Settings → Secrets and variables → Actions → New repository secret.**

| Secret           | Example                              | Purpose                                            |
| ---------------- | ------------------------------------ | -------------------------------------------------- |
| `DEV_SSH_HOST`   | `203.0.113.10` or `dev.example.com`  | Dev server hostname / IP                           |
| `DEV_SSH_USER`   | `deploy`                             | SSH user that owns the repo checkout               |
| `DEV_SSH_KEY`    | *(full private key, incl. header)*   | Private key whose public half is in the server's `~/.ssh/authorized_keys` |
| `DEV_SSH_PORT`   | `22`                                 | SSH port                                           |
| `DEV_APP_PATH`   | `/var/www/combiphar-web`             | Absolute path to the repo on the server            |

### Generating a deploy key

On any machine:

```bash
ssh-keygen -t ed25519 -C "github-deploy-combiphar" -f deploy_key
```

- Append `deploy_key.pub` to the server user's `~/.ssh/authorized_keys`.
- Paste the **entire** `deploy_key` (private, including the `-----BEGIN/END-----`
  lines) into the `DEV_SSH_KEY` secret.

---

## 2. One-time server bootstrap

Do this once, as the `DEV_SSH_USER`, so the first auto-deploy has something to
update. Requires `git`, `docker`, and `docker compose` on the server.

```bash
cd /var/www
git clone https://github.com/randopradika/combiphar.git combiphar-web
cd combiphar-web

cp .env.example .env
# Edit .env — see the notes below, then:

docker compose up -d --build
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate --force
```

### `.env` notes for the dev server (Docker + TLS proxy)

The app talks to MySQL over the compose network, and the dev server (gluvia)
terminates TLS in front of the app, so:

```dotenv
APP_ENV=staging             # NOT 'production' on dev: robots sitemap, X-Robots-Tag noindex and GA are gated on it
APP_DEBUG=false             # true only while actively debugging
APP_URL=https://<your-dev-host>
APP_FORCE_HTTPS=true        # gluvia terminates TLS; forces https:// links (avoids mixed-content)

SESSION_SECURE_COOKIE=true  # cookies only over https (there is a TLS proxy in front)
# TRUSTED_PROXIES=<proxy-ip>  # optional: defaults to loopback + private ranges
                              # (see config/app.php 'trusted_proxies'); set the
                              # exact proxy IP/CIDR to tighten further

DB_CONNECTION=mysql
DB_HOST=mysql               # the docker-compose service name, NOT 127.0.0.1
DB_PORT=3306
DB_DATABASE=combiphar
DB_USERNAME=combiphar       # or root
DB_PASSWORD=secret          # docker-compose.yml reads DB_DATABASE/DB_USERNAME/
                            # DB_PASSWORD (+ optional DB_ROOT_PASSWORD) from this
                            # same .env, so the two always match. NOTE: mysql only
                            # applies them on FIRST init of an empty data volume —
                            # rotating a password later needs ALTER USER in mysql
                            # plus the matching .env edit.
```

> **MySQL is published on loopback only** (`127.0.0.1:3306`) — host-local tools
> (mysql CLI, dumps, phpMyAdmin on the server) still connect via `127.0.0.1`,
> but the port is not internet-reachable. Don't widen this: published Docker
> ports bypass ufw-style host firewalls.

> `.env` is gitignored, so it is **never** overwritten by a deploy — configure
> it once. `git reset --hard` in the deploy only touches tracked files.

---

## 3. What a deploy does / does not touch

**Does:** pull `origin/main`, rebuild the image if the Dockerfile changed, install
PHP + JS deps, build front-end assets (`public/build` is gitignored, so it must be
built on the server), run migrations, clear caches, and re-link storage.

**Does not:** overwrite `.env`, delete uploaded images in `storage/app/public/`
(gitignored, transferred separately), or seed the database. The local-only
`OfficesSeeder`/`DatabaseSeeder` are untracked, so they are preserved but not run.

`git reset --hard origin/main` discards any **tracked** local edits on the server
— treat the dev checkout as a mirror of `main`, not a place to hand-edit code.

---

## 4. Manual deploy (no GitHub Actions)

SSH into the server and run the script directly:

```bash
cd /var/www/combiphar-web
bash deploy.sh                       # deploy main
DEPLOY_BRANCH=my-branch bash deploy.sh   # deploy a different branch
```

---

## 5. Troubleshooting

- **Workflow can't connect / “handshake failed”** — check `DEV_SSH_HOST`,
  `DEV_SSH_PORT`, and that `deploy_key.pub` is in the server's `authorized_keys`
  for `DEV_SSH_USER`.
- **`.env is missing`** — run the bootstrap in §2 first.
- **Job times out** — the first build compiles PHP extensions + installs Node; the
  workflow allows 30m. Subsequent deploys reuse cached image layers and are fast.
- **DB connection refused** — confirm `DB_HOST=mysql` (not `127.0.0.1`) in the
  server `.env`, and that the `mysql` container is healthy (`docker compose ps`).
- **Watch the run** — Actions tab → the run → the “Deploy over SSH” step streams
  the full `deploy.sh` output.

---

## 6. ⚠️ The dev server runs its own deploy script (open issue)

**Observed 2026-07-29.** The dev server does not run the `script:` block from
`.github/workflows/deploy.yml`. Three different scripts were sent — multi-line
diagnostics, and a single line chaining
`&& docker compose up -d --build --force-recreate app` after a `deploy.sh` that
exited 0 — and every run produced byte-identical output. None of the added
commands executed.

What does run logs `==> Starting app container`, a string that appears in **no
commit** of `deploy.sh` (every committed version says
`Starting / rebuilding containers`). That script has no `up -d --build` and no
container restart, which is why `docker compose ps` reported the app container
`Up 10 days` across several deploys.

The signature fits a **forced command on the deploy key** — a `command="..."`
prefix on the key's line in the server's `~/.ssh/authorized_keys` — invoking a
copy of `deploy.sh` kept outside the repo. SSH ignores the client's command when
that is set.

**Consequences:** the app container is never rebuilt or restarted, so the
Inertia SSR daemon never comes back once it dies (every page then silently
serves the client-rendered shell), and `config:cache` plus the SSR health check
in the tracked `deploy.sh` never run. App code, assets and migrations *do*
deploy, because the script still does `git reset --hard` + `npm run build` +
`migrate`.

**To fix, on the server (needs shell access):**

```bash
# 1) Bring SSR back right now — the rebuild picks up the supervised CMD.
cd /var/www/combiphar-web        # the dir holding docker-compose.yml
docker compose up -d --build --force-recreate app
docker compose exec -T app curl -sf http://127.0.0.1:13714/health   # expect {"status":"OK",...}

# 2) Find what the deploy key actually runs, and point it at the repo's script.
grep -n 'command=' ~/.ssh/authorized_keys
```

Remove the `command="..."` prefix (or repoint it at
`cd <repo> && bash deploy.sh`) so the tracked `deploy.sh` — which rebuilds,
restarts, and verifies SSR — is the script that runs.

**Verify from anywhere:** `curl -s https://<dev-host>/id | grep -c '<section'`
returns 0 when SSR is dead and a non-zero count when it is healthy.
