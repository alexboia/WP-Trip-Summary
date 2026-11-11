#!/usr/bin/env bash

set -euo pipefail

WPTS_SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
source "$WPTS_SCRIPT_DIR/tools/release/common.sh"

WPTS_EXPORT_TAG=false
for argument in "$@"; do
	case "$argument" in
		--export-tag=true) WPTS_EXPORT_TAG=true ;;
		--export-tag=false) WPTS_EXPORT_TAG=false ;;
		-h|--help)
			printf 'Usage: bash bin/export-wp-plugin-dir-svn.sh [--export-tag=true|false]\n'
			printf 'Prepare the local SVN checkout under build/wp-plugin-dir-svn/. No commit is made.\n'
			printf 'WPTS_RELEASE_SVN_URL overrides the repository URL for isolated local testing.\n'
			exit 0
			;;
		*) wpts_release_fail "Unknown argument: $argument" ;;
	esac
done

wpts_release_prepare_assets() {
	mkdir -- "$WPTS_RELEASE_WORK/assets"
	local source destination

	while IFS=$'\t' read -r source destination; do
		cp -- "$WPTS_ROOT/assets/$source" "$WPTS_RELEASE_WORK/assets/$destination"
	done < "$WPTS_RELEASE_TOOLS/assets.tsv"
}

wpts_release_checkout() {
	wpts_release_assert_build_path "$WPTS_EXPORT_ROOT"
	
	if [[ ! -e "$WPTS_EXPORT_ROOT" ]]; then
		svn checkout "$WPTS_RELEASE_SVN_URL" "$WPTS_EXPORT_ROOT"
	fi
	
	local actual_url

	actual_url=$(svn info --show-item url "$WPTS_EXPORT_ROOT")
	if [[ "${actual_url%/}" != "${WPTS_RELEASE_SVN_URL%/}" ]]; then
		wpts_release_fail "The SVN checkout does not belong to $WPTS_RELEASE_SVN_URL."
	fi
}

wpts_release_sync_directory() {
	local source=$1 destination=$2

	wpts_release_ensure_directory "$destination"
	
	# rsync exclusions protect SVN metadata from deletion as well as copying.
	rsync -a --delete --exclude=.svn/ -- "$source/" "$destination/"
}

wpts_release_require_commands svn
WPTS_RELEASE_SVN_URL=${WPTS_RELEASE_SVN_URL:-https://plugins.svn.wordpress.org/wp-trip-summary}
WPTS_EXPORT_ROOT="$WPTS_ROOT/build/wp-plugin-dir-svn"

# Finish package and asset preparation before changing an existing checkout.
wpts_release_prepare
wpts_release_prepare_assets
wpts_release_checkout
wpts_release_assert_build_path "$WPTS_EXPORT_ROOT/trunk"
wpts_release_assert_build_path "$WPTS_EXPORT_ROOT/assets"

if [[ "$WPTS_EXPORT_TAG" == true ]]; then
	wpts_release_assert_build_path "$WPTS_EXPORT_ROOT/tags/$WPTS_VERSION"
fi

wpts_release_sync_directory "$WPTS_RELEASE_PACKAGE" "$WPTS_EXPORT_ROOT/trunk"
wpts_release_sync_directory "$WPTS_RELEASE_WORK/assets" "$WPTS_EXPORT_ROOT/assets"

if [[ "$WPTS_EXPORT_TAG" == true ]]; then
	wpts_release_sync_directory "$WPTS_RELEASE_PACKAGE" "$WPTS_EXPORT_ROOT/tags/$WPTS_VERSION"
fi

svn status "$WPTS_EXPORT_ROOT"
printf 'DONE: prepared %s. Review SVN additions/deletions and changes before committing.\n' "$WPTS_EXPORT_ROOT"
