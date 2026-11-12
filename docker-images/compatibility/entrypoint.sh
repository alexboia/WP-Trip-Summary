#!/usr/bin/env bash

set -Eeuo pipefail

readonly wordpress_path=/var/www/html
readonly ready_file=/tmp/wpts-compat-ready

if [[ "$#" -eq 0 ]]; then
	set -- apache2-foreground
fi
if [[ "$1" != apache2-foreground ]]; then
	exec /usr/local/bin/docker-entrypoint.sh "$@"
fi

rm -f "$ready_file"
/usr/local/bin/docker-entrypoint.sh "$@" &
wordpress_pid=$!

stop_wordpress() {
	kill -TERM "$wordpress_pid" 2>/dev/null || true
	wait "$wordpress_pid" 2>/dev/null || true
}
trap 'stop_wordpress; exit 143' TERM
trap 'stop_wordpress; exit 130' INT
trap 'status=$?; stop_wordpress; exit "$status"' ERR

wp_command() {
	wp --allow-root --path="$wordpress_path" --skip-plugins --skip-themes "$@"
}

# Compose already waits for authenticated database access. The official
# entrypoint still needs time to copy core and generate wp-config.php.
for (( attempt=0; attempt<120; attempt++ )); do
	if [[ -f "$wordpress_path/wp-load.php" && -f "$wordpress_path/wp-config.php" ]]; then
		break
	fi
	if ! kill -0 "$wordpress_pid" 2>/dev/null; then
		echo 'WordPress stopped before setup.' >&2
		exit 1
	fi
	sleep 1
done
if [[ ! -f "$wordpress_path/wp-load.php" || ! -f "$wordpress_path/wp-config.php" ]]; then
	echo 'Timed out waiting for WordPress files.' >&2
	stop_wordpress
	exit 1
fi

installed_core=$(wp_command core version)
if [[ "$installed_core" != "$WPTS_EXPECTED_WORDPRESS_VERSION" ]]; then
	printf 'Expected WordPress %s, found %s. Recreate with --renew-anon-volumes.\n' "$WPTS_EXPECTED_WORDPRESS_VERSION" "$installed_core" >&2
	stop_wordpress
	exit 1
fi

if ! wp_command core is-installed >/dev/null 2>&1; then
	wp_command core install \
		--url="${WPTS_SITE_URL:-http://localhost:8083}" \
		--title="${WPTS_SITE_TITLE:-WP Trip Summary compatibility}" \
		--admin_user="${WPTS_ADMIN_USER:-wpts}" \
		--admin_password="${WPTS_ADMIN_PASSWORD:-wpts-change-me}" \
		--admin_email="${WPTS_ADMIN_EMAIL:-wpts@example.test}" \
		--skip-email
fi

# WP-CLI has no current WordPress user unless --user is supplied. The plugin
# checks activate_plugins before installing its tables, even when WP-CLI reports success.
wp_command --user="${WPTS_ADMIN_USER:-wpts}" plugin activate wp-trip-summary
# Load the active plugin too: an active flag alone does not verify bootstrap.
wp --allow-root --path="$wordpress_path" --skip-themes eval '
	if (!function_exists("abp01_get_env")) {
		WP_CLI::error("WP Trip Summary did not bootstrap.");
	}
	global $wpdb;
	WP_CLI::log("PHP " . PHP_VERSION . "; WordPress " . get_bloginfo("version") . "; MySQL " . $wpdb->db_version());
	$installer = new Abp01_Installer();
	$requirements = $installer->checkRequirements();
	if ($requirements !== Abp01_Installer::ALL_REQUIREMENTS_MET) {
		WP_CLI::warning("Plugin requirement check failed with status " . $requirements . ". The site remains available for diagnosis; compatibility has not passed.");
	} else {
		WP_CLI::log("Plugin requirements passed.");
	}
'
chown -R www-data:www-data "$wordpress_path/wp-content"
touch "$ready_file"
printf 'WP Trip Summary %s is ready at %s.\n' "$(wp_command plugin get wp-trip-summary --field=version)" "$WPTS_SITE_URL"
wait "$wordpress_pid"
