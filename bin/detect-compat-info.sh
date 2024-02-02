#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	COMPAT_RESTORE_DIR=true
else
	COMPAT_RESTORE_DIR=false
fi

 phpcompatinfo analyser:run . --exclude tests --exclude help --output ./build/compat-info/main-compat-info.txt --profile
 phpcompatinfo analyser:run ./tests --output ./build/compat-info/tests-compat-info.txt --profile
 phpcompatinfo analyser:run ./help --output ./build/compat-info/help-compat-info.txt --profile

if [ "$COMPAT_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi