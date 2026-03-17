#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
ARCHIVE_PATH="${1:-$($PROJECT_ROOT/tools/release/build-standalone.sh)}"
TEMP_DIR="$(mktemp -d)"
OUTPUT_PATH="$TEMP_DIR/index.scip"
FIXTURE_PATH="$PROJECT_ROOT/fixtures/plain-php-modern"

cleanup() {
  rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

tar -xzf "$ARCHIVE_PATH" -C "$TEMP_DIR"
EXTRACTED_DIR="$(find "$TEMP_DIR" -maxdepth 1 -type d -name 'scip-laravel-*' | head -n 1)"

"$EXTRACTED_DIR/bin/scip-laravel" index \
  --project-dir "$FIXTURE_PATH" \
  --output "$OUTPUT_PATH" \
  --format scip \
  --framework php \
  --php-version 8.5

test -s "$OUTPUT_PATH"
