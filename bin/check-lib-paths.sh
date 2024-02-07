#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

php ./bin/tools/check-lib-paths.php
if [ $? -ne 0 ];
then
	echo "There are issues with the library directory. Build will fail if run.";
	exit 1000
fi

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi