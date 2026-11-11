#!/usr/bin/env bash
set -euo pipefail
[[ "${WPTS_RELEASE_TEST_FAILURE:-}" != zip ]]
exec "$WPTS_RELEASE_TEST_REAL_ZIP" "$@"
