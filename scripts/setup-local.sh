#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
FRONTEND_DIR="$ROOT_DIR/frontend"
FRESH_DATABASE=false

for arg in "$@"; do
  case "$arg" in
    --fresh-database)
      FRESH_DATABASE=true
      ;;
    *)
      echo "Unknown option: $arg" >&2
      echo "Usage: scripts/setup-local.sh [--fresh-database]" >&2
      exit 2
      ;;
  esac
done

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

copy_env_if_missing() {
  local example_file="$1"
  local target_file="$2"

  if [ -f "$target_file" ]; then
    echo "Keeping existing $target_file"
    return
  fi

  cp "$example_file" "$target_file"
  echo "Created $target_file from $example_file"
}

require_command php
require_command composer
require_command node
require_command npm

copy_env_if_missing "$BACKEND_DIR/.env.example" "$BACKEND_DIR/.env"
copy_env_if_missing "$FRONTEND_DIR/.env.example" "$FRONTEND_DIR/.env.local"

cd "$BACKEND_DIR"
composer install

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
  php artisan key:generate
fi

php artisan storage:link || true
php artisan optimize:clear

if [ "$FRESH_DATABASE" = true ]; then
  echo "WARNING: --fresh-database deletes all data in the configured local database."
  echo "Use it only for local development or automated verification, never for staging or production."
  read -r -p "Type FRESH LOCAL DATABASE to continue: " confirmation
  if [ "$confirmation" != "FRESH LOCAL DATABASE" ]; then
    echo "Database reset skipped."
  else
    php artisan migrate:fresh --seed
  fi
else
  echo "Database not migrated automatically. Run 'cd backend && php artisan migrate --seed' when ready."
fi

cd "$FRONTEND_DIR"
npm install

cat <<'MSG'

Local setup helper finished.

Start backend:
  cd backend
  php artisan serve

Start frontend:
  cd frontend
  npm run dev

Local URLs:
  Backend API: http://localhost:8000
  Health: http://localhost:8000/api/v1/health
  Frontend: http://localhost:3000
MSG
