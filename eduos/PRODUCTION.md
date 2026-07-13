# EduOS — Production Deployment Runbook

This application is a Laravel 12 modular monolith. Everything below assumes a
single application server; scale out behind a load balancer only after moving
sessions and cache to Redis.

## 1. Server requirements

- PHP 8.3+ with `pdo_mysql` (or `pdo_pgsql`), `sqlite3`, `gd`, `mbstring`, `bcmath`
- MySQL 8 / MariaDB 10.6+ / PostgreSQL 14+ (SQLite is for development only)
- Nginx + PHP-FPM (or Apache), HTTPS terminated with a valid certificate
- Cron access for the scheduler

## 2. Environment (`.env`)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eduos.minedub.cm
APP_KEY=            # php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=eduos
DB_USERNAME=eduos
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

MAIL_MAILER=smtp    # anything other than log/array activates real reset emails
MAIL_HOST=...
MAIL_FROM_ADDRESS=noreply@eduos.minedub.cm
```

Notes:
- `APP_ENV=production` activates HTTPS URL generation and suppresses the
  on-screen demo delivery of password-reset links.
- With `MAIL_MAILER` configured, password resets are emailed; with `log`/`array`
  they fall back to demo behaviour (link shown on screen outside production,
  generic message in production).

## 3. Deploy steps

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate            # first deploy only
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 4. Scheduler (required)

The tamper-evidence sweep must run nightly:

```
* * * * * cd /var/www/eduos && php artisan schedule:run >> /dev/null 2>&1
```

This drives `eduos:verify-chains` (daily 02:00), which walks every passport and
custody hash chain and raises a CRITICAL alert on tampering.

## 5. Health & monitoring

- Liveness endpoint: `GET /up` (framework health check, no auth)
- Chain integrity: Settings → "Verify all chains now", or `php artisan eduos:verify-chains`
- Authentication audit: `/audit-trail` (AUTH stream) records every login,
  failure, lockout, MFA event and password reset

## 6. Security posture (already enforced by the application)

- All state-changing requests run inside a database transaction (atomic ledger)
- Login lockout after 5 failures per email+IP (5-minute decay) + route throttle
- TOTP MFA with one-time recovery codes; session listing and revocation
- New/reset accounts receive a random temporary password and are forced to
  rotate it at first login
- Security headers (CSP, X-Frame-Options DENY, nosniff, referrer policy) on
  every response; HTTPS URLs forced in production
- SHA-256 hash-chained custody and passport events; reconstructible stock journal

## 7. Backups

Back up the database and `storage/app/public/evidence` (inspection and
discrepancy photos) at least daily. The custody ledger is the legal record —
treat it accordingly (point-in-time recovery recommended).

## 8. First production login

Seeded demo accounts must be disabled or given fresh passwords before go-live:
create your real ministry administrator via `/users`, sign in with the issued
temporary password, rotate it, enable MFA on `/profile/mfa`, then deactivate
the demo accounts from `/users`.
