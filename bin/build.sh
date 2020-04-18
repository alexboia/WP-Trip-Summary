#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_RESTORE_DIR=true
else
	WPTS_RESTORE_DIR=false
fi

# Store some stuff for later use
WPTS_CDIR=$(pwd)

WPTS_BUILD_ROOTDIR="$WPTS_CDIR/build"
WPTS_BUILD_OUTDIR="$WPTS_BUILD_ROOTDIR/output"
WPTS_BUILD_COMPATDIR="$WPTS_BUILD_ROOTDIR/compat-info"
WPTS_BUILD_TMPDIR="$WPTS_BUILD_ROOTDIR/tmp"

WPTS_VERSION=$(awk '{IGNORECASE=1}/Version:/{print $NF}' ./abp01-plugin-main.php | awk '{gsub(/\s+/,""); print $0}')
WPTS_BUILD_NAME="wp-trip-summary.$WPTS_VERSION.zip"

# Ensure all output directories exist
ensure_out_dirs() {
	echo "Ensuring output directory structure..."

	if [ ! -d $WPTS_BUILD_ROOTDIR ]
	then
		mkdir $WPTS_BUILD_ROOTDIR
	fi

	if [ ! -d $WPTS_BUILD_OUTDIR ] 
	then
		mkdir $WPTS_BUILD_OUTDIR
	fi

	if [ ! -d $WPTS_BUILD_COMPATDIR ] 
	then
		mkdir $WPTS_BUILD_COMPATDIR
	fi

	if [ ! -d $WPTS_BUILD_TMPDIR ] 
	then
		mkdir $WPTS_BUILD_TMPDIR
	fi
}

# Regenerate compatibility info
make_compat_info() {
	echo "Building compatibility information files..."
	./bin/detect-compat-info.sh
}

# Ensure help contents is up to date
regenerate_help() {
	echo "Re-generating help contents..."
	php ./help/tools/make-help.php
}

clean_tmp_dir() {
	echo "Cleaning up temporary directory..."
	rm -rf $WPTS_BUILD_TMPDIR/*
	rm -rf $WPTS_BUILD_TMPDIR/.htaccess
}

# Clean output directories
clean_out_dirs() {
	echo "Ensuring output directories are clean..."
	rm -rf $WPTS_BUILD_OUTDIR/* > /dev/null
	rm -rf $WPTS_BUILD_TMPDIR/* > /dev/null
	rm -rf $WPTS_BUILD_TMPDIR/.htaccess > /dev/null
}

# Copy over all files
copy_source_files() {
	echo "Copying all files..."
	cp ./LICENSE.md "$WPTS_BUILD_TMPDIR/license.txt"
	cp ./README.txt "$WPTS_BUILD_TMPDIR/readme.txt"
	cp ./index.php "$WPTS_BUILD_TMPDIR"
	cp ./abp01-plugin-*.php "$WPTS_BUILD_TMPDIR"
	cp ./.htaccess "$WPTS_BUILD_TMPDIR"

	mkdir "$WPTS_BUILD_TMPDIR/media" && cp -r ./media/* "$WPTS_BUILD_TMPDIR/media"
	mkdir "$WPTS_BUILD_TMPDIR/views" && cp -r ./views/* "$WPTS_BUILD_TMPDIR/views"
	mkdir "$WPTS_BUILD_TMPDIR/lib" && cp -r ./lib/* "$WPTS_BUILD_TMPDIR/lib"
	mkdir "$WPTS_BUILD_TMPDIR/lang" && cp -r ./lang/* "$WPTS_BUILD_TMPDIR/lang"

	mkdir "$WPTS_BUILD_TMPDIR/data"
	mkdir "$WPTS_BUILD_TMPDIR/data/cache" && mkdir "$WPTS_BUILD_TMPDIR/data/storage"
	mkdir "$WPTS_BUILD_TMPDIR/data/help" && mkdir "$WPTS_BUILD_TMPDIR/data/setup"

	cp -r ./data/help/* "$WPTS_BUILD_TMPDIR/data/help" > /dev/null
	cp -r ./data/dev/setup/* "$WPTS_BUILD_TMPDIR/data/setup" > /dev/null
}

generate_package() {
	echo "Generating archive..."
	pushd $WPTS_BUILD_TMPDIR > /dev/null
	zip -rT $WPTS_BUILD_OUTDIR/$WPTS_BUILD_NAME ./ > /dev/null
	popd > /dev/null
}

echo "Using version: ${WPTS_VERSION}"

ensure_out_dirs
clean_out_dirs
regenerate_help
make_compat_info
copy_source_files
generate_package
clean_tmp_dir

echo "DONE!"

if [ "$WPTS_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi