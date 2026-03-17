#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
TAG="${1:-${GITHUB_REF_NAME:-}}"

if [ -z "$TAG" ]; then
  echo "Missing release tag. Pass it explicitly or set GITHUB_REF_NAME." >&2
  exit 1
fi

if ! command -v gh >/dev/null 2>&1; then
  echo "GitHub CLI is required to publish releases." >&2
  exit 1
fi

VERSION="$(php "$PROJECT_ROOT/tools/release/resolve-release-version.php" "$TAG")"

if gh release view "$TAG" >/dev/null 2>&1; then
  echo "Release $TAG already exists." >&2
  exit 1
fi

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
$("$PROJECT_ROOT/tools/release/build-release-artifacts.sh" "$VERSION")
EOF

if [ "$ARTIFACT_INDEX" -ne 3 ]; then
  echo "Expected three release artifacts, got $ARTIFACT_INDEX." >&2
  exit 1
fi

"$PROJECT_ROOT/tools/release/smoke-test-phar.sh" "$PHAR_PATH"
"$PROJECT_ROOT/tools/release/smoke-test-standalone.sh" "$ARCHIVE_PATH"

NOTES_PATH="$(mktemp)"
cleanup() {
  rm -f "$NOTES_PATH"
}
trap cleanup EXIT
php "$PROJECT_ROOT/tools/release/extract-release-notes.php" "$VERSION" > "$NOTES_PATH"

release_command=(
  gh release create "$TAG"
  "$PHAR_PATH"
  "$ARCHIVE_PATH"
  "$CHECKSUM_PATH"
  --title "$TAG"
  --notes-file "$NOTES_PATH"
)

if [[ "$VERSION" == *-* ]]; then
  release_command+=(--prerelease)
fi

"${release_command[@]}"
