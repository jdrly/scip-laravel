#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 1 ]; then
  echo "usage: $0 <php-version>" >&2
  exit 1
fi

PHP_VERSION="$1"
PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
IMAGE_TAG="scip-laravel-check:${PHP_VERSION}"

cd "$PROJECT_ROOT"

docker build \
  --build-arg "PHP_VERSION=${PHP_VERSION}" \
  --file docker/qa/Dockerfile \
  --tag "$IMAGE_TAG" \
  .
