#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	COMPAT_RESTORE_DIR=true
else
	COMPAT_RESTORE_DIR=false
fi

 phpcompatinfo analyser:run . --exclude tests --exclude help > ./build/compat-info/main-compat-info.txt
 phpcompatinfo analyser:run ./tests > ./build/compat-info/tests-compat-info.txt
 phpcompatinfo analyser:run ./help > ./build/compat-info/help-compat-info.txt

if [ "$COMPAT_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi