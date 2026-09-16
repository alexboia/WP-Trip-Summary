#!/usr/bin/env bash

if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

php .agents/skills/wpts-document-hooks/scripts/extract-hooks.php . --pretty --output=./hook-docs/hooks-inventory.json

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi