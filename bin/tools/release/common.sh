#!/usr/bin/env bash

# Shared release preparation for ZIP and WordPress.org export. Source this
# from a script using set -euo pipefail; call prepare as a standalone command.
WPTS_RELEASE_TOOLS=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
WPTS_ROOT=$(cd -- "$WPTS_RELEASE_TOOLS/../../.." && pwd -P)
WPTS_RELEASE_WORK=''

wpts_release_fail() {
	printf 'Release failed: %s\n' "$1" >&2
	exit 1
}

wpts_release_require_commands() {
	local executable
	for executable in "$@"; do
		if ! command -v "$executable" > /dev/null 2>&1; then
			wpts_release_fail "Required command is missing: $executable"
		fi
	done
}

wpts_release_assert_build_path() {
	local path=$1 resolved
	# Resolve .. and existing symlinks before checking containment; -m also accepts
	# destination directories that have not been created yet.
	resolved=$(realpath -m -- "$path")

	# The slash before * limits this match to children of build/, not build-other/.
	case "$resolved" in
		"$WPTS_ROOT/build/"*) ;;
		*) wpts_release_fail "Refusing to write outside the plugin build directory: $path" ;;
	esac

	if [[ "$resolved" != "$path" ]]; then
		wpts_release_fail "Refusing a redirected build path: $path"
	fi
}

wpts_release_ensure_directory() {
	wpts_release_assert_build_path "$1"
	mkdir -p -- "$1"
}

wpts_release_cleanup() {
	# Capture the exit status before any cleanup command can replace $?. Removing
	# the EXIT trap prevents re-entry; returning status preserves the original result.
	local status=$?
	trap - EXIT
	if [[ -n "$WPTS_RELEASE_WORK" ]]; then
		wpts_release_assert_build_path "$WPTS_RELEASE_WORK"
		rm -rf -- "$WPTS_RELEASE_WORK"
	fi
	return "$status"
}

wpts_release_install_dependencies() {
	local dependencies="$WPTS_RELEASE_WORK/dependencies"
	mkdir -- "$dependencies"
	cp -- "$WPTS_ROOT/composer.json" "$WPTS_ROOT/composer.lock" "$dependencies/"
	# Changes to the working directory and Composer environment stay in this subshell.
	(
		# An inherited Composer project/vendor override must not redirect writes
		# into the development installation or bypass platform requirements.
		unset COMPOSER COMPOSER_VENDOR_DIR COMPOSER_IGNORE_PLATFORM_REQ COMPOSER_IGNORE_PLATFORM_REQS
		export COMPOSER_VENDOR_DIR="$dependencies/vendor"

		cd -- "$dependencies"
		
		composer --no-interaction --no-ansi install --no-dev --prefer-dist \
			--no-progress --no-plugins --no-scripts --optimize-autoloader
		
		composer --no-interaction --no-ansi --no-plugins check-platform-reqs --no-dev
	)

	[[ -f "$dependencies/vendor/autoload.php" ]] || \
		wpts_release_fail 'Composer did not generate vendor/autoload.php.'
	
	rsync -a --exclude=.git --exclude=.svn --exclude=node_modules \
		--exclude=.env --exclude='.env.*' -- \
		"$dependencies/vendor/" "$WPTS_RELEASE_PACKAGE/vendor/"
}

wpts_release_copy_directory() {
	local source=$1 destination=$2
	mkdir -p -- "$destination"
	rsync -a --safe-links \
		--exclude=node_modules --exclude=.git --exclude=.svn \
		--exclude=.env --exclude='.env.*' \
		--exclude=package.json --exclude=package-lock.json --exclude=npm-shrinkwrap.json \
		--exclude=yarn.lock --exclude=pnpm-lock.yaml --exclude=tsconfig.json \
		-- "$source/" "$destination/"
}

wpts_release_copy_sources() {
	local directory
	cp -- "$WPTS_ROOT/LICENSE.md" "$WPTS_RELEASE_PACKAGE/license.txt"
	cp -- "$WPTS_RELEASE_WORK/readmes/README.txt" "$WPTS_RELEASE_PACKAGE/readme.txt"
	# Keep the root quoted for spaces, but leave the wildcard outside the quotes
	# so Bash expands the entry-point filenames. -- ends cp option parsing.
	cp -- "$WPTS_ROOT/index.php" "$WPTS_ROOT/.htaccess" "$WPTS_ROOT"/abp01-plugin-*.php "$WPTS_RELEASE_PACKAGE/"

	for directory in lib views media lang; do
		wpts_release_copy_directory "$WPTS_ROOT/$directory" "$WPTS_RELEASE_PACKAGE/$directory"
	done

	wpts_release_copy_directory "$WPTS_ROOT/data/dev/setup" "$WPTS_RELEASE_PACKAGE/data/setup"
	mkdir -p -- "$WPTS_RELEASE_PACKAGE/data/cache" "$WPTS_RELEASE_PACKAGE/data/storage"
}

wpts_release_prepare() {
	wpts_release_require_commands php composer rsync realpath mktemp mkdir cp rm
	wpts_release_ensure_directory "$WPTS_ROOT/build/release"

	# mktemp replaces the Xs with a unique suffix, isolating concurrent builds.
	WPTS_RELEASE_WORK=$(mktemp -d "$WPTS_ROOT/build/release/package.XXXXXXXX")

	# EXIT covers success and errors; converting INT/TERM to exits also runs cleanup.
	# 130 and 143 are the conventional exit statuses for those two signals.
	trap wpts_release_cleanup EXIT
	trap 'exit 130' INT
	trap 'exit 143' TERM
	
	WPTS_RELEASE_PACKAGE="$WPTS_RELEASE_WORK/wp-trip-summary"
	
	mkdir -- "$WPTS_RELEASE_PACKAGE"
	
	printf 'Checking library paths and generating readmes...\n'
	php "$WPTS_ROOT/bin/tools/check-lib-paths.php"
	php "$WPTS_ROOT/bin/tools/build-readme.php" --output-dir="$WPTS_RELEASE_WORK/readmes"
	
	WPTS_VERSION=$(php "$WPTS_RELEASE_TOOLS/version.php" "$WPTS_ROOT" "$WPTS_RELEASE_WORK/readmes/README.txt")
	
	printf 'Preparing WP Trip Summary %s...\n' "$WPTS_VERSION"
	wpts_release_copy_sources
	wpts_release_install_dependencies
}
