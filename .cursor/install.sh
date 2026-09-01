#!/usr/bin/env bash
# Idempotent repository bootstrap for the petb2b Laravel app.
# Runs after the repository is checked out. Safe to run repeatedly.
set -euo pipefail

# Move to the repository root (this script lives in .cursor/).
cd "$(dirname "$0")/.."

# PHP dependencies.
composer install --no-interaction --prefer-dist --no-progress

# Environment file. The repo ships defaults in .env_ini (there is no
# .env.example); copy it once and keep the existing file on later runs.
if [ ! -f .env ]; then
    cp .env_ini .env
fi

# Ensure an application key exists.
if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# SQLite database file (path configured in .env / config/database.php).
mkdir -p database
touch database/database.sqlite

# Apply schema. Idempotent: only pending migrations run.
php artisan migrate --force

# Seed demo data (admin / supplier / breeder users + categories) only when
# the users table is empty, because the seeder uses create() and would fail
# on unique constraints if re-run against an already-seeded database.
USER_COUNT="$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tr -dc '0-9' || true)"
if [ -z "${USER_COUNT}" ] || [ "${USER_COUNT}" = "0" ]; then
    php artisan db:seed --force
fi

# JavaScript dependencies + production asset build.
if [ -f package-lock.json ]; then
    npm ci
else
    npm install
fi
npm run build

# Symlink public/storage -> storage/app/public (idempotent).
if [ ! -e public/storage ]; then
    php artisan storage:link
fi

echo "petb2b install complete."
