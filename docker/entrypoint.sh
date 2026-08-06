#!/bin/sh
# Container entrypoint: prepare the storage symlink, then hand off to supervisor.
set -e

# `storage:link` is idempotent, but it fails when public/storage already exists
# as a real directory. That failure must never stop the container: the site
# still serves without the link, whereas exiting here would take nginx, PHP-FPM
# and the SSR daemon down with it. (The old CMD used `|| true` for the same
# reason — a `&&` there once silently prevented SSR from ever starting.)
php artisan storage:link --force || true

# PHP-FPM runs as www-data, whereas the old `artisan serve` ran as root — so
# these trees were root-owned 755 and every framework write failed. The symptom
# is not obvious: Filament's login page 500s on a bare `tempnam()` warning with
# nothing in laravel.log. Uploads live under storage/app too, so it must be the
# whole tree, not just framework/ and logs/.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# exec so supervisord becomes PID 1 and receives SIGTERM directly — without it
# docker stop waits the full timeout and then kills the container, cutting
# in-flight requests instead of draining them.
#
# -n keeps supervisord in the foreground. Without it Debian's supervisord
# daemonizes, the entrypoint returns 0, and the container exits immediately
# after the storage:link above — which is exactly what happened on first build.
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
