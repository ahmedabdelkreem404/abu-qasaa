Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Push-Location backend
composer run-script post-autoload-dump
php artisan test
Pop-Location

Push-Location frontend
npm run lint
npm run build
Pop-Location
