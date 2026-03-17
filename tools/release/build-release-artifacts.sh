#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

PHAR_PATH="$(php -d phar.readonly=0 "$PROJECT_ROOT/tools/release/build-phar.php")"
IMAGE_NAME="$($PROJECT_ROOT/tools/release/build-runtime-image.sh 8.5)"
ARCHIVE_PATH="$($PROJECT_ROOT/tools/release/build-standalone.sh)"

printf '%s\n%s\n%s\n' "$PHAR_PATH" "$IMAGE_NAME" "$ARCHIVE_PATH"
