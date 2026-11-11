#!/usr/bin/env bash

set -euo pipefail

WPTS_RELEASE_TEST_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
WPTS_RELEASE_TEST_ROOT=$(cd -- "$WPTS_RELEASE_TEST_DIR/../../../.." && pwd -P)

for executable in php rsync zip unzip svn svnadmin svnlook realpath mktemp sha256sum diff; do
	command -v "$executable" > /dev/null || { printf 'Missing test prerequisite: %s\n' "$executable" >&2; exit 1; }
done

test_build=$(realpath -m -- "$WPTS_RELEASE_TEST_ROOT/build")
[[ "$test_build" == "$WPTS_RELEASE_TEST_ROOT/build" ]] || exit 1

mkdir -p -- "$test_build"
test_root=$(mktemp -d "$test_build/release-tests.XXXXXXXX")

wpts_release_test_cleanup() {
	local status=$?
	trap - EXIT
	case "$(realpath -m -- "$test_root")" in
		"$test_build/release-tests."*) rm -rf -- "$test_root" ;;
		*) printf 'Refusing unexpected test cleanup path.\n' >&2; return 1 ;;
	esac
	return "$status"
}

trap wpts_release_test_cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

wpts_release_test_fail() {
	printf '[FAIL] %s\n' "$1" >&2
	cat "$test_root/command.log" >&2
	exit 1
}

wpts_release_test_run() {
	if ! "$@" > "$test_root/command.log" 2>&1; then
		wpts_release_test_fail "Command failed: $*"
	fi
}

wpts_release_test_move() {
	local path
	for path in "$@"; do
		case "$(realpath -m -- "$path")" in
			"$test_root/"*) ;;
			*) wpts_release_test_fail "Move outside test workspace: $path" ;;
		esac
	done
	mv -- "$1" "$2"
}

wpts_release_test_expect_failure() {
	if "$@" > "$test_root/command.log" 2>&1; then
		wpts_release_test_fail "Command unexpectedly succeeded: $*"
	fi

	if grep -q 'DONE:' "$test_root/command.log"; then
		wpts_release_test_fail 'A failed release printed success.'
	fi

	[[ "$(sha256sum "$archive")" == "$archive_hash" ]] || \
		wpts_release_test_fail 'A failed build changed the previous archive.'

	[[ -z "$(find "$project/build/release" -mindepth 1 -print -quit)" ]] || \
		wpts_release_test_fail 'Temporary release files were not cleaned up.'
}

project="$test_root/plugin with spaces"
mkdir -p "$project/bin/tools/release" "$test_root/commands"
cp -R -- "$WPTS_RELEASE_TEST_DIR/fixtures/project/." "$project/"
cp -- "$WPTS_RELEASE_TEST_ROOT/bin/build.sh" "$WPTS_RELEASE_TEST_ROOT/bin/export-wp-plugin-dir-svn.sh" "$project/bin/"
cp -- "$WPTS_RELEASE_TEST_DIR/../common.sh" "$WPTS_RELEASE_TEST_DIR/../version.php" "$WPTS_RELEASE_TEST_DIR/../assets.tsv" "$project/bin/tools/release/"

for directory in lib views media/js lang data/dev/setup data/cache data/storage vendor/development tests .agents build/output; do
	mkdir -p -- "$project/$directory"
	printf 'fixture: %s\n' "$directory" > "$project/$directory/marker.txt"
done

for file in LICENSE.md index.php .htaccess README.txt; do
	printf 'original: %s\n' "$file" > "$project/$file"
done

mkdir -p "$project/media/js/node_modules/unused" "$project/media/.git"

printf 'development\n' > "$project/media/js/node_modules/unused/package.js"
printf 'metadata\n' > "$project/media/.git/config"

for file in package.json package-lock.json tsconfig.json .env .env.local; do
	printf 'development\n' > "$project/media/js/$file"
done

while IFS=$'\t' read -r source destination; do
	mkdir -p -- "$project/assets/$(dirname -- "$source")"
	printf 'asset: %s\n' "$source" > "$project/assets/$source"
done < "$project/bin/tools/release/assets.tsv"

export WPTS_RELEASE_TEST_REAL_ZIP
WPTS_RELEASE_TEST_REAL_ZIP=$(command -v zip)

cp -- "$WPTS_RELEASE_TEST_DIR/fixtures/composer.sh" "$test_root/commands/composer"
cp -- "$WPTS_RELEASE_TEST_DIR/fixtures/zip.sh" "$test_root/commands/zip"

chmod +x "$test_root/commands/composer" "$test_root/commands/zip"

export PATH="$test_root/commands:$PATH"
# Inherited overrides must not redirect the production install into the host.
export COMPOSER="$project/composer.json" COMPOSER_VENDOR_DIR="$project/vendor"
export COMPOSER_IGNORE_PLATFORM_REQS=1 COMPOSER_IGNORE_PLATFORM_REQ='php'

source_hash=$(sha256sum "$project/composer.json" "$project/composer.lock" "$project/vendor/development/marker.txt" "$project/README.txt")
archive="$project/build/output/wp-trip-summary.9.8.7.zip"

printf 'previous release\n' > "$project/build/output/older.zip"
cd -- "$test_root"

wpts_release_test_run bash "$project/bin/build.sh"

archive_hash=$(sha256sum "$archive")
unzip -q "$archive" -d "$test_root/unpacked"

package="$test_root/unpacked/wp-trip-summary"

[[ -f "$package/vendor/production/package.txt" && -f "$package/.htaccess" ]] || \
	wpts_release_test_fail 'Required runtime files are missing.'

[[ -d "$package/data/cache" && -d "$package/data/storage" ]] || 
	\wpts_release_test_fail 'Writable runtime directories are missing.'

[[ -z "$(find "$package/data/cache" "$package/data/storage" -type f -print -quit)" ]] || \
	wpts_release_test_fail 'Working data was packaged.'

for excluded in vendor/development media/js/node_modules media/.git media/js/package.json media/js/package-lock.json media/js/tsconfig.json media/js/.env media/js/.env.local tests .agents composer.json composer.lock; do
	[[ ! -e "$package/$excluded" ]] || \
		wpts_release_test_fail "Development input was packaged: $excluded"
done

[[ "$(unzip -Z1 "$archive" | cut -d/ -f1 | sort -u)" == wp-trip-summary ]] || \
	wpts_release_test_fail 'Incorrect ZIP root directory.'

cmp "$project/README.source.txt" "$package/readme.txt"

[[ "$source_hash" == "$(sha256sum "$project/composer.json" "$project/composer.lock" "$project/vendor/development/marker.txt" "$project/README.txt")" ]] || \
	wpts_release_test_fail 'Source checkout was changed.'

[[ -f "$project/build/output/older.zip" ]] || \
	wpts_release_test_fail 'A previous release was removed.'

printf '[PASS] ZIP contents, production dependencies, paths with spaces and source isolation.\n'

for failure in library readme composer platform zip; do
	wpts_release_test_expect_failure env WPTS_RELEASE_TEST_FAILURE="$failure" bash "$project/bin/build.sh"
	printf '[PASS] %s failure preserves the archive and cleans temporary files.\n' "$failure"
done

sed -i 's/9.8.7/9.8.6/' "$project/abp01-plugin-header.php"
wpts_release_test_expect_failure bash "$project/bin/build.sh"

cp -- "$WPTS_RELEASE_TEST_DIR/fixtures/project/abp01-plugin-header.php" "$project/abp01-plugin-header.php"
sed -i 's/9.8.7/9.8.6/' "$project/README.source.txt"

wpts_release_test_expect_failure bash "$project/bin/build.sh"

cp -- "$WPTS_RELEASE_TEST_DIR/fixtures/project/README.source.txt" "$project/README.source.txt"
printf '[PASS] Plugin constant and generated readme version mismatches are rejected.\n'

wpts_release_test_move "$project/views" "$project/views-backup"
wpts_release_test_expect_failure bash "$project/bin/build.sh"
wpts_release_test_move "$project/views-backup" "$project/views"
printf '[PASS] Source-copy failure preserves the archive.\n'

# A real local SVN repository exercises checkout metadata and repeated export.
svnadmin create "$test_root/repository"
export WPTS_RELEASE_SVN_URL="file://$test_root/repository"

svn mkdir "$WPTS_RELEASE_SVN_URL/trunk" "$WPTS_RELEASE_SVN_URL/tags" "$WPTS_RELEASE_SVN_URL/assets" -m 'Initialize isolated release test' > /dev/null
repository_revision=$(svnlook youngest "$test_root/repository")

cd -- "$project/bin"
wpts_release_test_run bash ./export-wp-plugin-dir-svn.sh --export-tag=true

checkout="$project/build/wp-plugin-dir-svn"

diff -r "$package" "$checkout/trunk"
diff -r "$package" "$checkout/tags/9.8.7"

printf 'stale\n' > "$checkout/trunk/.obsolete"
printf 'stale\n' > "$checkout/tags/9.8.7/obsolete.txt"
printf 'stale\n' > "$checkout/assets/obsolete.png"

wpts_release_test_run bash ./export-wp-plugin-dir-svn.sh --export-tag=true

diff -r "$package" "$checkout/trunk"
diff -r "$package" "$checkout/tags/9.8.7"

[[ ! -e "$checkout/assets/obsolete.png" && -d "$checkout/.svn" ]] || \
	wpts_release_test_fail 'SVN export left stale data or removed metadata.'
[[ "$(svnlook youngest "$test_root/repository")" == "$repository_revision" ]] || 
	\wpts_release_test_fail 'Export committed changes.'

printf '[PASS] ZIP, SVN trunk and tag match; repeated export removes stale files without committing.\n'

printf 'local tag note\n' > "$checkout/tags/9.8.7/.review-note"
wpts_release_test_run bash ./export-wp-plugin-dir-svn.sh

[[ -f "$checkout/tags/9.8.7/.review-note" ]] || \
	wpts_release_test_fail 'Default export modified the tag.'

rm -- "$checkout/tags/9.8.7/.review-note"
wpts_release_test_expect_failure env WPTS_RELEASE_SVN_URL="$WPTS_RELEASE_SVN_URL/another-project" bash ./export-wp-plugin-dir-svn.sh

printf '[PASS] Default export leaves tags alone; a mismatched SVN repository is rejected.\n'

mkdir "$test_root/outside"
printf 'protected\n' > "$test_root/outside/marker.txt"

wpts_release_test_move "$checkout/trunk" "$checkout/trunk-backup"
ln -s "$test_root/outside" "$checkout/trunk"

wpts_release_test_expect_failure bash ./export-wp-plugin-dir-svn.sh

[[ "$(cat "$test_root/outside/marker.txt")" == protected ]] || \
	wpts_release_test_fail 'Export followed a redirected destination.'

[[ ! -e "$test_root/outside/abp01-plugin-main.php" ]] || \
	wpts_release_test_fail 'Export wrote outside the build directory.'

rm -- "$checkout/trunk"
wpts_release_test_move "$checkout/trunk-backup" "$checkout/trunk"
printf '[PASS] Redirected export destinations are rejected without touching their contents.\n'

rm -- "$project/assets/icon-128x128.png"
wpts_release_test_expect_failure bash ./export-wp-plugin-dir-svn.sh --export-tag=true

diff -r "$package" "$checkout/trunk"
diff -r "$package" "$checkout/tags/9.8.7"
printf '[PASS] Missing assets leave the previous SVN export intact.\n'

wpts_release_test_expect_failure bash ./build.sh --unexpected
wpts_release_test_expect_failure bash ./export-wp-plugin-dir-svn.sh --unexpected

printf '[PASS] Unknown arguments fail.\n'
printf 'All release script tests passed.\n'
