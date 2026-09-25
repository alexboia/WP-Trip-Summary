#!/usr/bin/env bash

if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

./vendor/bin/phpunit

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi