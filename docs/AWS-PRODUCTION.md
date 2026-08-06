# AWS production — go-live checklist

Companion to `DEPLOY.md` (which covers the existing dev server). Written from the
2026-08-04 security audit; every item here closes a finding from it.

## 1. Environment

Set through **Secrets Manager / SSM Parameter Store injected at task start** — not
a `.env` file baked into the image or sitting on disk.

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=                      # generate FRESH for prod; never reuse dev's
APP_URL=https://combiphar.com
APP_FORCE_HTTPS=true
TRUSTED_PROXIES=<ALB / CloudFront CIDRs>   # never '*' — it lets any client
                                           # spoof X-Forwarded-For past the
                                           # per-IP rate limiters
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
SESSION_DRIVER=redis
CACHE_STORE=redis
REDIS_HOST=<elasticache endpoint>
LOG_LEVEL=warning

DB_HOST=<rds endpoint>        # private subnet, not publicly accessible
DB_PASSWORD=<strong, rotated>

FILESYSTEM_DISK=s3
FILAMENT_FILESYSTEM_DISK=s3   # Filament defaults to 'public' — set it too, or
                              # CMS uploads keep landing on local disk
AWS_BUCKET=<uploads bucket>
AWS_DEFAULT_REGION=ap-southeast-1
AWS_URL=https://<cloudfront-domain>   # public base URL for Storage::url()
```

Prefer an **IAM task role** over `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`.

Image build must run `composer install --no-dev --optimize-autoloader`,
`npm run build`, then `php artisan optimize`.

## 2. Uploads must move to S3

Not optional on Fargate: `storage/app/public` is ephemeral container storage, so
every CMS upload is lost on redeploy and invisible to other tasks.

`league/flysystem-aws-s3-v3` is already installed — this is configuration only
(§1). Two things to carry over that nginx was doing:

- **SVG must not render as a document.** Any CMS user can upload one, and an SVG
  navigated to directly executes its scripts in your origin. Serve uploads from a
  **separate cookieless domain**, and add a CloudFront response-headers policy
  setting `Content-Disposition: attachment` on `*.svg`. (`<img>` rendering is
  unaffected — Content-Disposition does not apply there.)
- **Bucket stays private**, reached only through CloudFront OAC. Block Public
  Access on. `config/filesystems.php` now has `'throw' => true` so a failed
  upload surfaces instead of silently saving a row that points at a 404.

## 3. Network

- **CloudFront + AWS WAF** in front. Managed rule groups: Core, Known Bad Inputs,
  PHP Application, SQLi. Add a rate-based rule, and a rule restricting `/admin*`
  to office IPs.
- **Lock the ALB security group to CloudFront's managed prefix list.** Otherwise
  the origin is directly reachable and the WAF is trivially bypassed.
- **RDS MySQL** in private subnets, Multi-AZ, KMS encryption at rest, automated
  backups, security group allowing only the app's SG.
- `/up` is restricted to RFC1918 in `docker/nginx-site.conf` so only the ALB can
  reach it. If health checks fail, widen that allow-list rather than removing it.

## 4. Deploy pipeline

Do **not** reproduce the dev server's arrangement, where a forced `command=` on
the SSH key runs a `deploy.sh` kept outside the repo — nobody can audit what
actually executes, and the repo's own deploy script never runs (see `DEPLOY.md`
§6).

Instead: GitHub Actions → **OIDC role** (no long-lived AWS keys) → build image →
push to ECR → ECS rolling deploy. Also fix, in `.github/workflows/deploy.yml`:

- `appleboy/ssh-action@v1` is a floating tag despite the comment claiming a SHA
  pin. Pin it, or drop the action entirely with the move to ECS.
- `script_stop: true` is not a valid input for that action, so a failing deploy
  step can pass silently.

## 5. Still open after the 2026-08-04 remediation

- **MFA on `/admin`.** Roles now exist (`users.role`, allow-listed in
  `User::canAccessPanel()`), but there is still no second factor. Filament 3 has
  none built in, so it needs a package.
- **Per-resource least privilege.** `role` distinguishes `admin` / `editor` /
  `disabled`, but no resource restricts by it yet; the investor document
  resources are the first that should.
- **Per-field `maxSize()`** on the 29 image uploads. The effective cap today is
  Livewire's 50MB plus nginx's `client_max_body_size`; tightening per field was
  deliberately skipped to avoid rejecting large investor PDFs.
- **`composer audit` in CI** — it could not be run during the audit (no PHP
  outside the container).
- `npm audit fix` for the postcss advisory (build-time only).
- `filament/spatie-laravel-translatable-plugin` is abandoned.
