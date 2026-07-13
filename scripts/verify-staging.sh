#!/usr/bin/env bash
set -e

API_ORIGIN="${1:-}"
FRONTEND_ORIGIN="${2:-}"
BUSINESS_SLUG="${3:-dates}"

if [ -z "$API_ORIGIN" ] || [ -z "$FRONTEND_ORIGIN" ]; then
  echo "Usage: scripts/verify-staging.sh https://api.example.com https://www.example.com [business-slug]" >&2
  exit 1
fi

API_ORIGIN="${API_ORIGIN%/}"
FRONTEND_ORIGIN="${FRONTEND_ORIGIN%/}"

check_http() {
  local label="$1"
  local url="$2"
  local expected="${3:-200}"

  echo "Checking $label: $url"
  local status
  status="$(curl -fsS -o /tmp/abu-qasaa-staging-check.out -w "%{http_code}" "$url")"

  if [ "$status" != "$expected" ]; then
    echo "$label returned HTTP $status, expected $expected" >&2
    exit 1
  fi
}

check_no_debug_markers() {
  local url="$1"
  echo "Checking debug markers are absent: $url"
  local body
  body="$(curl -fsS "$url")"

  if printf "%s" "$body" | grep -Eiq "APP_KEY|DB_PASSWORD|stack trace|Illuminate\\\\|SQLSTATE|Whoops"; then
    echo "Potential debug or secret marker detected in response." >&2
    exit 1
  fi
}

check_http "backend health" "$API_ORIGIN/api/v1/health" 200
check_http "frontend home" "$FRONTEND_ORIGIN" 200
check_http "public business unit" "$FRONTEND_ORIGIN/$BUSINESS_SLUG" 200
check_no_debug_markers "$API_ORIGIN/api/v1/does-not-exist"

echo "Staging verification checks passed."
