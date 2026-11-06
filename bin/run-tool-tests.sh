#!/usr/bin/env bash

set -uo pipefail

wpts_tool_tests_usage() {
	cat <<'USAGE'
Usage: bash bin/run-tool-tests.sh [--set=SET]

Run standalone utility regression tests without WordPress or a test database.

Options:
  --set=SET    Select a group (also accepts --set SET).
  -h, --help   Show this help.

Groups:
  all             All utility tests (default).
  php             All PHP utility tests.
  license         License-header parser and CLI utility.
  hooks           Hook extraction and documentation parsing.
  namespaces      Class-identifier conversion utility.
  compatinfo      Per-file parser error reset extension.
  screen-capture  Python tests for the screenshot utility.

Environment:
  WPTS_TEST_PHP     PHP executable, default: php from PATH.
  WPTS_TEST_PYTHON  Python executable; otherwise prefer the screenshot tool's
                   native .venv, then python3 or python from PATH.
                   Supply executable paths without additional arguments.

All selected runners execute even if another fails. Missing dependencies fail
the affected runner. Exit codes: 0 = success, 1 = test failure, 2 = usage error.
USAGE
}

wpts_tool_tests_argument_error() {
	printf 'Error: %s\nUse --help to see available test groups.\n' "$1" >&2
	exit 2
}

WPTS_TEST_SET=all
while [[ $# -gt 0 ]]; do
	case "$1" in
		--set=*)
			WPTS_TEST_SET=${1#--set=}
			shift
			;;
		--set)
			if [[ $# -lt 2 || -z "$2" || "$2" == -* ]]; then
				wpts_tool_tests_argument_error 'Missing value for --set.'
			fi
			WPTS_TEST_SET=$2
			shift 2
			;;
		-h|--help)
			wpts_tool_tests_usage
			exit 0
			;;
		*)
			wpts_tool_tests_argument_error "Unknown argument: $1."
			;;
	esac
done

case "$WPTS_TEST_SET" in
	all|php|license|hooks|namespaces|compatinfo|screen-capture) ;;
	'') wpts_tool_tests_argument_error 'Missing value for --set.' ;;
	*) wpts_tool_tests_argument_error "Unknown test group: $WPTS_TEST_SET." ;;
esac

WPTS_ROOT=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd) || exit 1
cd -- "$WPTS_ROOT" || exit 1
WPTS_TEST_PHP=${WPTS_TEST_PHP:-php}
WPTS_PASSED_RUNNERS=0
WPTS_FAILED_RUNNERS=()

wpts_run_tool_test() {
	local name=$1
	shift
	printf '\n=== %s ===\n' "$name"
	if "$@"; then
		WPTS_PASSED_RUNNERS=$((WPTS_PASSED_RUNNERS + 1))
		printf '[PASS] %s\n' "$name"
	else
		local status=$?
		if [[ $status == 130 || $status == 143 ]]; then
			exit "$status"
		fi
		WPTS_FAILED_RUNNERS+=("$name (exit $status)")
		printf '[FAIL] %s (exit %s)\n' "$name" "$status" >&2
	fi
}

case "$WPTS_TEST_SET" in
	all|php|license)
		wpts_run_tool_test 'License header parser' "$WPTS_TEST_PHP" .agents/skills/wpts-coding-conventions/tests/license-header-test.php
		wpts_run_tool_test 'License header CLI' "$WPTS_TEST_PHP" .agents/skills/wpts-coding-conventions/tests/license-header-utility-test.php
		;;
esac
case "$WPTS_TEST_SET" in
	all|php|hooks)
		wpts_run_tool_test 'Hook extraction' "$WPTS_TEST_PHP" .agents/skills/wpts-document-hooks/tests/extract-hooks-test.php
		wpts_run_tool_test 'Hook documentation parser' "$WPTS_TEST_PHP" .agents/skills/wpts-document-hooks/tests/parse-hook-doc-test.php
		;;
esac
case "$WPTS_TEST_SET" in
	all|php|namespaces)
		wpts_run_tool_test 'Class identifier conversion' "$WPTS_TEST_PHP" .agents/skills/wpts-refactor-code-namespaces/tests/change-target-class-identifier-test.php
		;;
esac
case "$WPTS_TEST_SET" in
	all|php|compatinfo)
		wpts_run_tool_test 'CompatInfo error reset' "$WPTS_TEST_PHP" bin/tools/compatinfo/test-reset-file-errors.php
		;;
esac
case "$WPTS_TEST_SET" in
	all|screen-capture)
		if [[ -z "${WPTS_TEST_PYTHON:-}" ]]; then
			if [[ -x "$WPTS_ROOT/bin/tools/screen-capture/.venv/bin/python" ]]; then
				WPTS_TEST_PYTHON="$WPTS_ROOT/bin/tools/screen-capture/.venv/bin/python"
			elif [[ "${OSTYPE:-}" == msys* || "${OSTYPE:-}" == cygwin* ]] && [[ -x "$WPTS_ROOT/bin/tools/screen-capture/.venv/Scripts/python.exe" ]]; then
				WPTS_TEST_PYTHON="$WPTS_ROOT/bin/tools/screen-capture/.venv/Scripts/python.exe"
			elif command -v python3 > /dev/null 2>&1; then
				WPTS_TEST_PYTHON=python3
			else
				WPTS_TEST_PYTHON=python
			fi
		fi
		wpts_run_tool_test 'Screenshot utility' "$WPTS_TEST_PYTHON" -m unittest discover -s bin/tools/screen-capture -p test_run.py -v
		;;
esac

printf '\nTool test runners: %s passed, %s failed.\n' "$WPTS_PASSED_RUNNERS" "${#WPTS_FAILED_RUNNERS[@]}"
if [[ ${#WPTS_FAILED_RUNNERS[@]} -gt 0 ]]; then
	printf '  - %s\n' "${WPTS_FAILED_RUNNERS[@]}" >&2
	exit 1
fi
