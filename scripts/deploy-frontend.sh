#!/usr/bin/env bash
set -e

APP_DIR="${APP_DIR:-$(pwd)}"
cd "$APP_DIR"

if [ ! -f package.json ]; then
  echo "Run this script from the Next.js frontend directory or set APP_DIR." >&2
  exit 1
fi

echo "Installing frontend dependencies..."
npm ci

echo "Running frontend lint..."
npm run lint

echo "Building frontend..."
npm run build

if [ "${START_FRONTEND:-0}" = "1" ]; then
  echo "Starting frontend with npm run start..."
  npm run start
else
  echo "Frontend build complete. Restart the configured process manager for this deployment target."
fi
