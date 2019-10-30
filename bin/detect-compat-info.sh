#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	COMPAT_RESTORE_DIR=true
else
	COMPAT_RESTORE_DIR=false
fi

 phpcompatinfo analyser:run --alias cmain --output=./build/compat-info/main-compat-info.txt > /dev/null

 pushd ./tests > /dev/null
 phpcompatinfo analyser:run --alias ctests --output=../build/compat-info/tests-compat-info.txt > /dev/null
 popd > /dev/null

 pushd ./help > /dev/null
 phpcompatinfo analyser:run --alias chelp --output=../build/compat-info/help-compat-info.txt > /dev/null
 popd > /dev/null

if [ "$COMPAT_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi