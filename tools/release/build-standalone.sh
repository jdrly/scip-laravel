#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
VERSION="${1:-$(php -r "require '$PROJECT_ROOT/vendor/autoload.php'; echo ScipLaravel\\Cli\\ApplicationFactory::VERSION;")}"
DIST_DIR="$PROJECT_ROOT/dist"
ARCHIVE_PATH="$DIST_DIR/scip-laravel-$VERSION-standalone.tar.gz"
TEMP_DIR="$(mktemp -d)"
STAGE_DIR="$TEMP_DIR/scip-laravel-$VERSION"

cleanup() {
  rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

mkdir -p "$DIST_DIR"
mkdir -p "$STAGE_DIR"

tar -C "$PROJECT_ROOT" -cf - \
  bin \
  src \
  vendor \
  composer.json \
  composer.lock \
  README.md \
  CHANGELOG.md \
| tar -C "$STAGE_DIR" -xf -

chmod +x "$STAGE_DIR/bin/scip-laravel"
rm -f "$ARCHIVE_PATH"
tar -czf "$ARCHIVE_PATH" -C "$TEMP_DIR" "scip-laravel-$VERSION"

echo "$ARCHIVE_PATH"
