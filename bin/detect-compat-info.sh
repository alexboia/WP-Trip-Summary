#!/usr/bin/env bash

set -euo pipefail

WPTS_ROOT=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
cd -- "$WPTS_ROOT"

if ! command -v composer > /dev/null 2>&1; then
	printf 'Composer is missing. Install Composer and make it available on PATH.\n' >&2
	exit 1
fi

if [[ ! -f ./vendor/bin/phpcompatinfo ]]; then
	printf 'PHP CompatInfo is missing. Run composer install with development dependencies first.\n' >&2
	exit 1
fi

WPTS_COMPAT_DIR="$WPTS_ROOT/build/compat-info"
mkdir -p -- "$WPTS_COMPAT_DIR"

WPTS_COMPAT_PLATFORM_REPORT="$WPTS_COMPAT_DIR/platform-deps.txt"
printf 'Checking production dependency platform requirements...\n'
if ! composer --no-interaction --no-ansi check-platform-reqs --no-dev > "$WPTS_COMPAT_PLATFORM_REPORT" 2>&1; then
	cat "$WPTS_COMPAT_PLATFORM_REPORT" >&2
	printf 'Platform dependency check failed. See %s\n' "$WPTS_COMPAT_PLATFORM_REPORT" >&2
	exit 1
fi

WPTS_COMPAT_NATIVE_DIR=$(php -r 'echo str_replace("\\", "/", realpath($argv[1]));' "$WPTS_COMPAT_DIR")
# Keep the custom service container separate from an earlier vanilla CLI cache.
export APP_CACHE_DIR="${APP_CACHE_DIR:-$WPTS_COMPAT_NATIVE_DIR/cache}/wpts-file-errors"

# CompatInfoDB prepends / to SQLite paths. On Windows, use a path rooted on
# the current drive instead of /C:/...; 
# the working directory is the plugin root.
export DATABASE_URL="${DATABASE_URL:-sqlite://${WPTS_COMPAT_NATIVE_DIR#[A-Za-z]:}/compatinfo-db.sqlite}"

WPTS_COMPATINFO=(php -d "memory_limit=${WPTS_COMPAT_MEMORY_LIMIT:-2G}" ./vendor/bin/phpcompatinfo --no-interaction --no-ansi --config "$WPTS_ROOT/bin/tools/compatinfo/config.php")
WPTS_COMPAT_DIAGNOSE="$WPTS_COMPAT_DIR/database-diagnose.txt"

printf 'Checking PHP CompatInfo database...\n'
if ! "${WPTS_COMPATINFO[@]}" diagnose > "$WPTS_COMPAT_DIAGNOSE" 2>&1; then
	printf 'Initializing PHP CompatInfo database...\n'
	"${WPTS_COMPATINFO[@]}" db:new
	if ! "${WPTS_COMPATINFO[@]}" diagnose > "$WPTS_COMPAT_DIAGNOSE" 2>&1; then
		cat "$WPTS_COMPAT_DIAGNOSE" >&2
		exit 1
	fi
fi

printf 'Analyzing plugin sources...\n'
"${WPTS_COMPATINFO[@]}" analyser:run . --exclude tests --exclude help --exclude build --exclude bin --exclude docs --exclude .agents --exclude vendor > "$WPTS_COMPAT_DIR/main-compat-info.txt"
printf 'Analyzing test sources...\n'
"${WPTS_COMPATINFO[@]}" analyser:run ./tests > "$WPTS_COMPAT_DIR/tests-compat-info.txt"
printf 'Compatibility reports saved to %s\n' "$WPTS_COMPAT_DIR"
