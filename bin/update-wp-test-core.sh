#!/usr/bin/env bash

set -euo pipefail

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

composer install

WPTS_WP_VERSION="$(php bin/tools/get-wp-test-version.php)"
WPTS_TARGET_CORE_DIR="/tmp/wordpress"

export WP_CORE_DIR="/tmp/wordpress-${WPTS_WP_VERSION}"

wp core download \
    --version="$WPTS_WP_VERSION" \
    --path="$WP_CORE_DIR"

# Replace the old core only after the new download has completed successfully.
if [[ ! -r "$WP_CORE_DIR/wp-settings.php" || ! -r "$WP_CORE_DIR/wp-includes/version.php" ]]
then
	echo "Downloaded WordPress core is incomplete: $WP_CORE_DIR" >&2
	exit 1
fi

rm -rf -- "$WPTS_TARGET_CORE_DIR"
mv -- "$WP_CORE_DIR" "$WPTS_TARGET_CORE_DIR"
export WP_CORE_DIR="$WPTS_TARGET_CORE_DIR"

echo "WordPress test core $WPTS_WP_VERSION installed in $WP_CORE_DIR."

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi
