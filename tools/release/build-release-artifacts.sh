#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
VERSION="${1:-$(php -r "require '$PROJECT_ROOT/vendor/autoload.php'; echo ScipLaravel\\Cli\\ApplicationFactory::VERSION;")}"
DIST_DIR="$PROJECT_ROOT/dist"
PHAR_PATH="$(php -d phar.readonly=0 "$PROJECT_ROOT/tools/release/build-phar.php" "$VERSION")"
ARCHIVE_PATH="$($PROJECT_ROOT/tools/release/build-standalone.sh "$VERSION")"
CHECKSUM_PATH="$DIST_DIR/scip-laravel-$VERSION-sha256.txt"

"$PROJECT_ROOT/tools/release/generate-checksums.sh" "$CHECKSUM_PATH" "$PHAR_PATH" "$ARCHIVE_PATH" >/dev/null

printf '%s\n%s\n%s\n' "$PHAR_PATH" "$ARCHIVE_PATH" "$CHECKSUM_PATH"
