#!/bin/sh

# Stop at the first failed command (-e) and reject variables that have not
# been assigned (-u). This keeps a partially downloaded archive out of the
# final image.
set -eu

# The Dockerfiles call this script while building the temporary "assets"
# stage. Keeping downloads in one script makes every release image behave in
# exactly the same way.
if [ "$#" -ne 4 ]; then
	echo "Usage: install-release.sh <plugin-package-url> <theme-ref> <wp-cli-version> <wordpress-version>" >&2
	exit 64
fi

plugin_package_url="$1"
theme_ref="$2"
wp_cli_version="$3"
wordpress_version="$4"

# Files below assets_root are copied into the final WordPress image. Files
# below work_root exist only while archives are being unpacked.
assets_root="/opt/wpts-assets"
work_root="/tmp/wpts-release"

mkdir -p \
	"$assets_root/plugins/wp-trip-summary" \
	"$assets_root/themes/wp-alexboia-net" \
	"$assets_root/bin" \
	"$assets_root/wordpress" \
	"$work_root/plugin" \
	"$work_root/theme" \
	"$work_root/wordpress"

# Download the exact historical plug-in package selected by the Dockerfile.
curl -fsSL "$plugin_package_url" -o "$work_root/plugin.zip"
unzip -q "$work_root/plugin.zip" -d "$work_root/plugin"

# Release archives are not uniform: some contain a top-level directory and
# some contain the plug-in files directly. Locate the main file first so both
# layouts are normalized to the same destination.
plugin_main="$(find "$work_root/plugin" -type f -name 'abp01-plugin-main.php' | head -n 1)"
if [ -z "$plugin_main" ]; then
	echo "The plug-in archive does not contain abp01-plugin-main.php." >&2
	exit 65
fi

plugin_root="$(dirname "$plugin_main")"
cp -a "$plugin_root/." "$assets_root/plugins/wp-trip-summary/"

# Pin the theme to a commit, rather than a moving branch, so rebuilding an old
# release later produces the same files.
theme_package_url="https://github.com/alexboia/WP-AlexBoia-NET-Theme/archive/${theme_ref}.zip"
curl -fsSL "$theme_package_url" -o "$work_root/theme.zip"
unzip -q "$work_root/theme.zip" -d "$work_root/theme"

theme_stylesheet="$(find "$work_root/theme" -type f -name 'style.css' | head -n 1)"
if [ -z "$theme_stylesheet" ]; then
	echo "The theme archive does not contain style.css." >&2
	exit 66
fi

theme_root="$(dirname "$theme_stylesheet")"
cp -a "$theme_root/." "$assets_root/themes/wp-alexboia-net/"

# The old official PHP 7.4 WordPress runtime image contains WordPress 6.1.1.
# Downloading core separately lets that runtime execute the requested modern
# WordPress version without changing the historical plug-in package.
wordpress_package_url="https://wordpress.org/wordpress-${wordpress_version}.tar.gz"
curl -fsSL "$wordpress_package_url" -o "$work_root/wordpress.tar.gz"
tar -xzf "$work_root/wordpress.tar.gz" \
	-C "$work_root/wordpress" \
	--strip-components=1

if [ ! -f "$work_root/wordpress/wp-includes/version.php" ]; then
	echo "The WordPress archive does not contain wp-includes/version.php." >&2
	exit 67
fi

cp -a "$work_root/wordpress/." "$assets_root/wordpress/"

# WP-CLI performs the unattended first-run installation in the final image.
wp_cli_url="https://github.com/wp-cli/wp-cli/releases/download/v${wp_cli_version}/wp-cli-${wp_cli_version}.phar"
curl -fsSL "$wp_cli_url" -o "$assets_root/bin/wp"
chmod 0755 "$assets_root/bin/wp"

# Nothing in the temporary work directory is needed by the final image.
rm -rf "$work_root"
