#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PHP_VERSION="${1:-8.5}"
IMAGE_TAG="scip-laravel:${PHP_VERSION}"

cd "$PROJECT_ROOT"

docker build \
  --build-arg "PHP_VERSION=${PHP_VERSION}" \
  --file docker/runtime/Dockerfile \
  --tag "$IMAGE_TAG" \
  .

echo "$IMAGE_TAG"
