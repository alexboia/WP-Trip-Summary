#!/usr/bin/env bash
set -euo pipefail

[[ "${COMPOSER_VENDOR_DIR:-}" == "$PWD/vendor" ]]
[[ -z "${COMPOSER:-}${COMPOSER_IGNORE_PLATFORM_REQ:-}${COMPOSER_IGNORE_PLATFORM_REQS:-}" ]]
[[ -f composer.json && -f composer.lock ]]
arguments=" $* "
[[ "$arguments" == *' --no-dev '* ]]

if [[ "$arguments" == *' install '* ]]; then
	[[ "$arguments" == *' --no-scripts '* && "$arguments" == *' --no-plugins '* ]]
	[[ "$arguments" == *' --optimize-autoloader '* ]]
	[[ "${WPTS_RELEASE_TEST_FAILURE:-}" != composer ]]
	mkdir -p vendor/production
	cp composer.json vendor/autoload.php
	printf 'production dependency\n' > vendor/production/package.txt
elif [[ "$arguments" == *' check-platform-reqs '* ]]; then
	[[ "${WPTS_RELEASE_TEST_FAILURE:-}" != platform ]]
else
	exit 2
fi
