#!/usr/bin/env bash
set -e

BACKUP_DIR="${BACKUP_DIR:-../backups}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"

required_var() {
  local name="$1"
  if [ -z "${!name:-}" ]; then
    echo "Missing required environment variable: $name" >&2
    exit 1
  fi
}

required_var DB_HOST
required_var DB_PORT
required_var DB_DATABASE
required_var DB_USERNAME

mkdir -p "$BACKUP_DIR"

BACKUP_FILE="$BACKUP_DIR/${DB_DATABASE}_${TIMESTAMP}.sql"

echo "Creating database backup: $BACKUP_FILE"

if [ -n "${DB_PASSWORD:-}" ]; then
  MYSQL_PWD="$DB_PASSWORD" mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --triggers \
    --default-character-set=utf8mb4 \
    "$DB_DATABASE" > "$BACKUP_FILE"
else
  mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --triggers \
    --default-character-set=utf8mb4 \
    "$DB_DATABASE" > "$BACKUP_FILE"
fi

if [ ! -s "$BACKUP_FILE" ]; then
  echo "Backup file is empty." >&2
  exit 1
fi

sha256sum "$BACKUP_FILE" > "$BACKUP_FILE.sha256"

echo "Backup complete."
echo "Backup file: $BACKUP_FILE"
echo "Checksum file: $BACKUP_FILE.sha256"
