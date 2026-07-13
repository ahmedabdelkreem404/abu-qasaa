# Backup And Recovery

## Rules

- Create a verified backup before every staging or production deployment.
- Store backups outside the public web root.
- Do not commit backups to Git.
- Do not embed passwords in committed scripts or shell history.
- Encrypt backups and copy them to off-server storage for production.
- Do not automatically run `php artisan migrate:rollback` in production.

## Backup

Use environment variables provided by the target server:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=abu_qasaa
export DB_USERNAME=abu_qasaa
read -r -s -p "Database password: " DB_PASSWORD
export DB_PASSWORD
export BACKUP_DIR=/secure/backups/abu-qasaa

scripts/backup-database.sh
```

The script creates:

- A timestamped `.sql` file.
- A `.sha256` checksum file.
- A non-empty file verification.

## Manual mysqldump Example

```bash
export MYSQL_PWD="$DB_PASSWORD"
mysqldump \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  --single-transaction \
  --routines \
  --triggers \
  --default-character-set=utf8mb4 \
  "$DB_DATABASE" > "/secure/backups/abu_qasaa_$(date -u +%Y%m%dT%H%M%SZ).sql"
unset MYSQL_PWD
```

Verify:

```bash
test -s /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql
sha256sum /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql > /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql.sha256
```

## Restore Warning

Restoring overwrites the target database state. Before restore:

1. Put the application in maintenance mode.
2. Back up the current broken database.
3. Verify the selected backup checksum.
4. Confirm the target database name.
5. Confirm the incident owner approves the restore.

Restore example:

```bash
cd backend
php artisan down --retry=60

sha256sum -c /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql.sha256

export MYSQL_PWD="$DB_PASSWORD"
mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  --default-character-set=utf8mb4 \
  "$DB_DATABASE" < /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql
unset MYSQL_PWD

php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## Code Rollback

1. Identify the previous known-good tag or commit.
2. Put the backend into maintenance mode.
3. Switch code to the known-good commit.
4. Reinstall dependencies.
5. Rebuild Laravel caches.
6. Rebuild/restart the frontend.
7. Run health checks and smoke tests.

## Database Rollback

Prefer forward fixes for additive migrations. If data corruption or incompatible schema change requires rollback, restore a verified backup only after impact review. Do not run `migrate:rollback` in production without a migration-by-migration compatibility review.
