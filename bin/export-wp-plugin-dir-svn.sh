#!/usr/bin/env bash

# Move to plug-in root
if [[ `pwd` == */bin ]]
then
	pushd ../ > /dev/null
	WPTS_EXPORT_RESTORE_DIR=true
else
	WPTS_EXPORT_RESTORE_DIR=false
fi

# Store some stuff for later use
WPTS_CDIR=$(pwd)
WPTS_VERSION=$(awk '{IGNORECASE=1}/Version:/{print $NF}' ./abp01-plugin-main.php | awk '{gsub(/\s+/,""); print $0}')

WPTS_EXPORT_ROOT="$WPTS_CDIR/build/wp-plugin-dir-svn"
WPTS_EXPORT_TRUNK_DIR="$WPTS_EXPORT_ROOT/trunk"
WPTS_EXPORT_ASSETS_DIR="$WPTS_EXPORT_ROOT/assets"
WPTS_EXPORT_TAGS_DIR="$WPTS_EXPORT_ROOT/tags"
WPTS_EXPORT_CURRENT_TAG_DIR="$WPTS_EXPORT_TAGS_DIR/$WPTS_VERSION"

ensure_root_dir() {
	echo "Ensuring root directory structure and checking out if needed..."
	if [ ! -d $WPTS_EXPORT_ROOT ]
	then
		mkdir $WPTS_EXPORT_ROOT
		svn co https://plugins.svn.wordpress.org/wp-trip-summary/ $WPTS_EXPORT_ROOT
	fi
}

ensure_tag_dir() {
    echo "Ensuring tag directory structure..."
	if [ ! -d $WPTS_EXPORT_CURRENT_TAG_DIR ] 
	then
		mkdir $WPTS_EXPORT_CURRENT_TAG_DIR
	fi
}

clean_trunk_dir() {
	echo "Ensuring trunk directory is clean..."
	rm -rf $WPTS_EXPORT_TRUNK_DIR/* > /dev/null
    rm -rf $WPTS_EXPORT_TRUNK_DIR/.htaccess > /dev/null
}

regenerate_help() {
	echo "Re-generating help contents..."
	php ./help/tools/make-help.php
}

copy_source_files() {
    echo "Copying all source files to $1..."
	cp ./LICENSE.md "$1/license.txt"
	cp ./README.txt "$1/readme.txt"
	cp ./index.php "$1"
	cp ./abp01-plugin-*.php "$1"
	cp ./.htaccess "$1"

	mkdir "$1/media" && cp -r ./media/* "$1/media"
	mkdir "$1/views" && cp -r ./views/* "$1/views"
	mkdir "$1/lib" && cp -r ./lib/* "$1/lib"
	mkdir "$1/lang" && cp -r ./lang/* "$1/lang"

	mkdir "$1/data"
	mkdir "$1/data/cache" && mkdir "$1/data/storage"
	mkdir "$1/data/help" && mkdir "$1/data/setup"

	cp -r ./data/help/* "$1/data/help" > /dev/null
	cp -r ./data/dev/setup/* "$1/data/setup" > /dev/null
}

copy_asset_files() {
    echo "Copying all asset files to $WPTS_EXPORT_ASSETS_DIR..."

    cp ./assets/en_US/viewer-summary.png    "$WPTS_EXPORT_ASSETS_DIR/screenshot-1.png" > /dev/null
    cp ./assets/en_US/viewer-map.png	"$WPTS_EXPORT_ASSETS_DIR/screenshot-2.png" > /dev/null
    cp ./assets/en_US/viewer-teaser-top.png	"$WPTS_EXPORT_ASSETS_DIR/screenshot-3.png" > /dev/null
    cp ./assets/en_US/admin-edit-map.png	"$WPTS_EXPORT_ASSETS_DIR/screenshot-4.png" > /dev/null
    cp ./assets/en_US/admin-edit-summary-bike.png	"$WPTS_EXPORT_ASSETS_DIR/screenshot-5.png" > /dev/null
    cp ./assets/en_US/admin-edit-summary-empty.png  "$WPTS_EXPORT_ASSETS_DIR/screenshot-6.png" > /dev/null
    cp ./assets/en_US/admin-settings.png    "$WPTS_EXPORT_ASSETS_DIR/screenshot-7.png" > /dev/null
	cp ./assets/en_US/admin-settings-predefined-tile-layer.png    "$WPTS_EXPORT_ASSETS_DIR/screenshot-8.png" > /dev/null
	cp ./assets/en_US/viewer-map-alt-profile.png    "$WPTS_EXPORT_ASSETS_DIR/screenshot-9.png" > /dev/null
	cp ./assets/en_US/maintenance.png    "$WPTS_EXPORT_ASSETS_DIR/screenshot-10.png" > /dev/null

    cp ./assets/banner-772x250.jpg    "$WPTS_EXPORT_ASSETS_DIR/banner-772x250.jpg" > /dev/null
    cp ./assets/banner-1544x500.jpg    "$WPTS_EXPORT_ASSETS_DIR/banner-1544x500.jpg" > /dev/null
    cp ./assets/icon-128x128.png    "$WPTS_EXPORT_ASSETS_DIR/icon-128x128.png" > /dev/null
    cp ./assets/icon-256x256.png    "$WPTS_EXPORT_ASSETS_DIR/icon-256x256.png" > /dev/null
}

echo "Using version: $WPTS_VERSION"

ensure_root_dir
clean_trunk_dir
regenerate_help
copy_source_files "$WPTS_EXPORT_TRUNK_DIR"

if [ $# -eq 1 ] && [ "$1" = "--export-tag=true" ]
then
    ensure_tag_dir
    copy_source_files "$WPTS_EXPORT_CURRENT_TAG_DIR"
fi

copy_asset_files

echo "DONE!"

if [ "$WPTS_EXPORT_RESTORE_DIR" = true ]
then
	popd > /dev/null
fi