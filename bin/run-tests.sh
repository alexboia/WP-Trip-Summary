#!/usr/bin/env bash

wpts_usage() {
	cat <<'USAGE'
Usage: bash bin/run-tests.sh [--set=SET] [--filter=PATTERN] [PHPUnit options]

Options:
  --set=SET         Run a named test set (also accepts --set SET).
  --filter=PATTERN  Forward a PHPUnit filter (also accepts --filter PATTERN).
  -h, --help       Show this help.

Test sets:
  all, default  All tests (the default).
  core          Environment, settings, lookup data and common helpers.
  auth          Authorization and nonce providers.
  validation    Input filtering and validation rules.
  routes        Route data, tracks, geometry and processing.
  documents     GPX/GeoJSON parsers, validators and parser factory.
  installer     Installation, removal and requirements.
  modules       Module activation, hosting and dependency selection.
  ui            Admin actions, columns, menus, views and frontend themes.
  logging       Audit, system and route logs.
  io            Files, downloads, maintenance and server directives.

Examples:
  bash bin/run-tests.sh --set=routes
  bash bin/run-tests.sh --filter='RouteTrackPointTests::test_'
  bash bin/run-tests.sh --set=documents --filter='GpxDocumentParserTests'
USAGE
}

wpts_argument_error() {
	printf 'Error: %s\nUse --help to see available options and test sets.\n' "$1" >&2
	exit 2
}

WPTS_TEST_SET=all
WPTS_PHPUNIT_ARGS=()

while [ "$#" -gt 0 ]; do
	case "$1" in
		--set=*)
			WPTS_TEST_SET=${1#--set=}
			shift
			;;
		--set|--filter)
			if [ "$#" -lt 2 ] || [[ -z "$2" || "$2" == --* ]]; then
				wpts_argument_error "Missing value for $1."
			fi
			if [ "$1" = --set ]; then
				WPTS_TEST_SET=$2
			else
				WPTS_PHPUNIT_ARGS+=("$1" "$2")
			fi
			shift 2
			;;
		--filter=)
			wpts_argument_error 'Missing value for --filter.'
			;;
		-h|--help)
			wpts_usage
			exit 0
			;;
		*)
			WPTS_PHPUNIT_ARGS+=("$1")
			shift
			;;
	esac
done

case "$WPTS_TEST_SET" in
	all|default)
		WPTS_TEST_SET=default
		;;
	core|auth|validation|routes|documents|installer|modules|ui|logging|io)
		;;
	'')
		wpts_argument_error 'Missing value for --set.'
		;;
	*)
		wpts_argument_error "Unknown test set: $WPTS_TEST_SET."
		;;
esac

WPTS_ROOT=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd) || exit 1
cd -- "$WPTS_ROOT" || exit 1

exec ./vendor/bin/phpunit --testsuite "$WPTS_TEST_SET" "${WPTS_PHPUNIT_ARGS[@]}"
