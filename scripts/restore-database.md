# Database Restore Operator Notes

Restoring a staging or production database is destructive to the target database. Do not run these commands until the outage owner confirms the selected backup, target database, and rollback plan.

## Preconditions

- The selected `.sql` backup exists outside the public web root.
- The `.sha256` file has been verified.
- The target application is in maintenance mode.
- A fresh backup of the current broken database has been taken.
- The incident owner has approved the restore.

## Verify Backup

```bash
sha256sum -c /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql.sha256
test -s /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql
```

## Restore

Use environment variables or a secured MySQL option file. Do not paste database passwords into shell history.

```bash
export MYSQL_PWD="$DB_PASSWORD"
mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  --default-character-set=utf8mb4 \
  "$DB_DATABASE" < /secure/backups/abu_qasaa_YYYYMMDDTHHMMSSZ.sql
unset MYSQL_PWD
```

## Post-Restore

```bash
cd backend
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Run the staging verification script and smoke checklist after restore.
