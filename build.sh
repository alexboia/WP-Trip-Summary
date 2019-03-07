#!/usr/bin/env bash

WPTS_CDIR=$(pwd)

WPTS_BUILD_OUTDIR="$WPTS_CDIR/build/output"
WPTS_BUILD_TMPDIR="$WPTS_CDIR/build/tmp"

WPTS_VERSION=$(awk '{IGNORECASE=1}/Version:/{print $NF}' ./abp01-plugin-main.php)
WPTS_BUILD_NAME="wp-trip-summary.$WPTS_VERSION.zip"

echo "Using version: $WPTS_VERSION"

# Ensure all output directories exist
ensure_out_dirs() {
	echo "Ensuring output directory structure..."
	if [ ! -d $WPTS_BUILD_OUTDIR ] 
	then
		mkdir $WPTS_BUILD_OUTDIR
	fi

	if [ ! -d $WPTS_BUILD_TMPDIR ] 
	then
		mkdir $WPTS_BUILD_TMPDIR
	fi
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
	cp ./LICENSE.md $WPTS_BUILD_TMPDIR/LICENSE.txt
	cp ./README.txt $WPTS_BUILD_TMPDIR/README.txt
	cp ./index.php $WPTS_BUILD_TMPDIR
	cp ./abp01-plugin-main.php $WPTS_BUILD_TMPDIR
	cp ./.htaccess $WPTS_BUILD_TMPDIR

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
	zip -rT $WPTS_BUILD_OUTDIR/$WPTS_BUILD_NAME ./* > /dev/null
	popd > /dev/null
}

ensure_out_dirs
clean_out_dirs
regenerate_help
copy_source_files
generate_package
clean_tmp_dir

echo "DONE!"