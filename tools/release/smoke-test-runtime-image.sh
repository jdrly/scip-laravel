#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PHP_VERSION="${1:-8.5}"
IMAGE_TAG="$PROJECT_ROOT/tools/release/build-runtime-image.sh"
IMAGE_NAME="$($IMAGE_TAG "$PHP_VERSION")"
TEMP_DIR="$(mktemp -d)"
OUTPUT_DIR="$TEMP_DIR/out"
mkdir -p "$OUTPUT_DIR"

cleanup() {
  rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

docker run --rm \
  -v "$PROJECT_ROOT/fixtures/plain-php-modern:/workspace/project:ro" \
  -v "$OUTPUT_DIR:/workspace/out" \
  "$IMAGE_NAME" \
  index \
  --project-dir /workspace/project \
  --output /workspace/out/index.scip \
  --format scip \
  --framework php \
  --php-version 8.5

test -s "$OUTPUT_DIR/index.scip"
