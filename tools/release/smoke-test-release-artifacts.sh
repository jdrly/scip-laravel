#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
VERSION="${1:-}"

PHAR_PATH=''
ARCHIVE_PATH=''
CHECKSUM_PATH=''
ARTIFACT_INDEX=0

while IFS= read -r artifact_path; do
  case "$ARTIFACT_INDEX" in
    0) PHAR_PATH="$artifact_path" ;;
    1) ARCHIVE_PATH="$artifact_path" ;;
    2) CHECKSUM_PATH="$artifact_path" ;;
  esac

  ARTIFACT_INDEX=$((ARTIFACT_INDEX + 1))
done <<EOF
$({
  if [ -n "$VERSION" ]; then
    "$PROJECT_ROOT/tools/release/build-release-artifacts.sh" "$VERSION"
  else
    "$PROJECT_ROOT/tools/release/build-release-artifacts.sh"
  fi
})
EOF

if [ "$ARTIFACT_INDEX" -ne 3 ]; then
  echo "Expected three release artifacts, got $ARTIFACT_INDEX." >&2
  exit 1
fi

"$PROJECT_ROOT/tools/release/smoke-test-phar.sh" "$PHAR_PATH"
"$PROJECT_ROOT/tools/release/smoke-test-standalone.sh" "$ARCHIVE_PATH"
test -s "$CHECKSUM_PATH"
