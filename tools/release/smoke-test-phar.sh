#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PHAR_PATH="${1:-$(php -d phar.readonly=0 "$PROJECT_ROOT/tools/release/build-phar.php")}"
TEMP_DIR="$(mktemp -d)"

cleanup() {
  rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

run_fixture() {
  local fixture_name="$1"
  local framework="$2"
  local php_version="$3"
  local output_path="$TEMP_DIR/${fixture_name}.scip"

  "$PHAR_PATH" index \
    --project-dir "$PROJECT_ROOT/fixtures/$fixture_name" \
    --output "$output_path" \
    --format scip \
    --framework "$framework" \
    --php-version "$php_version"

  test -s "$output_path"
}

run_fixture plain-php-modern php 8.5
run_fixture laravel12-app laravel 8.5
run_fixture laravel13-app laravel 8.5
