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
  WPTS_TEST_PYTHON  Python executable; otherwise use the screenshot tool's .venv.
                   Prefer bin/python; Scripts/python.exe is also supported
                   in Git Bash, Cygwin and WSL. No implicit PATH fallback.
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

wpts_run_screenshot_tests() {
	local python_executable=${WPTS_TEST_PYTHON:-}
	local venv_path="$WPTS_ROOT/bin/tools/screen-capture/.venv"
	if [[ -z "$python_executable" ]]; then
		if [[ -x "$venv_path/bin/python" ]]; then
			python_executable="$venv_path/bin/python"
		elif [[ "${OSTYPE:-}" == msys* || "${OSTYPE:-}" == cygwin* || -n "${WSL_INTEROP:-}" || -n "${WSL_DISTRO_NAME:-}" ]] && [[ -x "$venv_path/Scripts/python.exe" ]]; then
			python_executable="$venv_path/Scripts/python.exe"
		else
			printf 'Error: no usable Python interpreter found in %s.\n' "$venv_path" >&2
			printf 'Create the screenshot tool virtual environment and install requirements.txt, or set WPTS_TEST_PYTHON explicitly.\n' >&2
			return 1
		fi
	fi
	printf 'Python interpreter: %s\n' "$python_executable"
	"$python_executable" -m unittest discover -s bin/tools/screen-capture -p test_run.py -v
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
		wpts_run_tool_test 'Screenshot utility' wpts_run_screenshot_tests
		;;
esac

printf '\nTool test runners: %s passed, %s failed.\n' "$WPTS_PASSED_RUNNERS" "${#WPTS_FAILED_RUNNERS[@]}"
if [[ ${#WPTS_FAILED_RUNNERS[@]} -gt 0 ]]; then
	printf '  - %s\n' "${WPTS_FAILED_RUNNERS[@]}" >&2
	exit 1
fi
