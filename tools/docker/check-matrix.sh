#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

"$PROJECT_ROOT/tools/docker/check.sh" 8.4
"$PROJECT_ROOT/tools/docker/check.sh" 8.5
