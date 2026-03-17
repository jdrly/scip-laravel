#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 2 ]; then
  echo "Usage: generate-checksums.sh <output-path> <artifact> [artifact ...]" >&2
  exit 1
fi

OUTPUT_PATH="$1"
shift

: > "$OUTPUT_PATH"
for artifact_path in "$@"; do
  if [ ! -f "$artifact_path" ]; then
    echo "Missing artifact for checksum generation: $artifact_path" >&2
    exit 1
  fi

  artifact_name="$(basename "$artifact_path")"
  artifact_directory="$(cd "$(dirname "$artifact_path")" && pwd)"
  (
    cd "$artifact_directory"
    shasum -a 256 "$artifact_name"
  ) >> "$OUTPUT_PATH"
done

test -s "$OUTPUT_PATH"
printf '%s\n' "$OUTPUT_PATH"
