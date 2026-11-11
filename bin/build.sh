#!/usr/bin/env bash

# Fail on unhandled command errors, unset variables and failed pipeline stages.
set -euo pipefail

# BASH_SOURCE locates this script even when the caller's working directory differs.
WPTS_SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
# source loads the shared functions and variables into this shell.
source "$WPTS_SCRIPT_DIR/tools/release/common.sh"

case "${1:-}" in
	-h|--help)
		printf 'Usage: bash bin/build.sh\nBuild a production ZIP in build/output/ from any working directory.\n'
		exit 0
		;;
esac
if [[ $# -ne 0 ]]; then
	wpts_release_fail 'Unexpected arguments. Use --help for usage.'
fi

wpts_release_require_commands zip unzip mv
wpts_release_prepare

archive="wp-trip-summary.$WPTS_VERSION.zip"
printf 'Creating %s...\n' "$archive"
# Parentheses run a subshell: cd affects only ZIP creation, and the relative
# input path produces a wp-trip-summary/ archive root instead of absolute paths.
(
	cd -- "$WPTS_RELEASE_WORK"
	zip -qr "$archive" wp-trip-summary
	unzip -tq "$archive"
)

# Publish only a complete, tested archive. Keep previous releases and preserve
# the last successful archive for this version when preparation or ZIP fails.
wpts_release_ensure_directory "$WPTS_ROOT/build/output"
mv -f -- "$WPTS_RELEASE_WORK/$archive" "$WPTS_ROOT/build/output/$archive"
printf 'DONE: %s\n' "$WPTS_ROOT/build/output/$archive"
