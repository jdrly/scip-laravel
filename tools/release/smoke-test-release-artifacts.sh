#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

"$PROJECT_ROOT/tools/release/smoke-test-phar.sh"
"$PROJECT_ROOT/tools/release/smoke-test-runtime-image.sh" 8.5
"$PROJECT_ROOT/tools/release/smoke-test-standalone.sh"
