#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

if [ ! -z "${WP_I18N_LIB+xxx}" ] || [ ! -d "$WP_I18N_LIB" ]; then
	WP_I18N_LIB="/usr/lib/wpi18n"
fi

if [ $# -lt 1 ]; then
	WPTS_PLUGIN_DIR=`pwd`
else
	WPTS_PLUGIN_DIR="$1"
fi

if [ -z "$2" ]; then
	WPTS_TEXT_DOMAIN=""
else
	WPTS_TEXT_DOMAIN=$2
fi

WPTS_PLUGIN_BASE=$(basename "$WPTS_PLUGIN_DIR")
if [[ ! $WPTS_TEXT_DOMAIN ]]; then
    $WPTS_TEXT_DOMAIN="$WPTS_PLUGIN_BASE"
fi

php "$WP_I18N_LIB/makepot.php" wp-plugin $WPTS_PLUGIN_DIR "WPTS_PLUGIN_DIR/lang/$WPTS_TEXT_DOMAIN.pot"

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi