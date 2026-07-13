#!/usr/bin/env bash
set -e

APP_DIR="${APP_DIR:-$(pwd)}"
cd "$APP_DIR"

if [ ! -f artisan ]; then
  echo "Run this script from the Laravel backend directory or set APP_DIR." >&2
  exit 1
fi

restore_online() {
  local exit_code=$?
  if [ "$exit_code" -ne 0 ]; then
    echo "Deployment failed. Attempting to bring the application online..."
    php artisan up || true
  fi
  exit "$exit_code"
}

trap restore_online EXIT

echo "Installing production Composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader

echo "Putting application into maintenance mode..."
php artisan down --retry=60

echo "Running additive migrations..."
php artisan migrate --force

echo "Ensuring public storage link exists..."
php artisan storage:link || true

echo "Rebuilding Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "Bringing application online..."
php artisan up

trap - EXIT
echo "Backend deployment complete."
