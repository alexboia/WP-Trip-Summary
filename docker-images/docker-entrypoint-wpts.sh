#!/usr/bin/env bash

# -E keeps ERR traps in functions, -e stops on errors, -u rejects unset
# variables, and pipefail notices failures hidden inside a pipeline.
set -Eeuo pipefail

readonly wordpress_entrypoint="/usr/local/bin/docker-entrypoint.sh"
readonly wordpress_path="/var/www/html"

# Match the default command from the official WordPress image when no command
# was supplied explicitly.
if [[ "$#" -eq 0 ]]; then
	set -- apache2-foreground
fi

# Utility commands such as `docker run IMAGE php -v` should retain the normal
# behavior of the official WordPress image and skip the automatic site setup.
if [[ "$1" != "apache2-foreground" ]]; then
	exec "$wordpress_entrypoint" "$@"
fi

# Start the official entrypoint in the background. It copies WordPress from
# /usr/src/wordpress to /var/www/html and starts Apache. This wrapper can then
# wait for the database and finish the initial WordPress setup with WP-CLI.
"$wordpress_entrypoint" "$@" &
wordpress_pid=$!

# Forward Docker stop signals to Apache, then wait for a clean shutdown.
stop_wordpress() {
	trap - TERM INT
	kill -TERM "$wordpress_pid" 2>/dev/null || true
	wait "$wordpress_pid" 2>/dev/null || true
}

trap stop_wordpress TERM INT

# Run WP-CLI against this container's site. Plug-ins and themes are skipped
# while administering the installation so a broken optional component cannot
# prevent WP-CLI from reporting a useful setup error.
wp_command() {
	wp \
		--allow-root \
		--path="$wordpress_path" \
		--skip-plugins \
		--skip-themes \
		"$@"
}

# Official WordPress images accept either WORDPRESS_DB_PASSWORD=value or a
# Docker secret in WORDPRESS_DB_PASSWORD_FILE. Read both forms consistently.
read_wordpress_setting() {
	local setting_name="$1"
	local default_value="$2"
	local file_setting_name="${setting_name}_FILE"

	if [[ -n "${!setting_name:-}" ]]; then
		printf '%s' "${!setting_name}"
	elif [[ -n "${!file_setting_name:-}" ]]; then
		<"${!file_setting_name}" tr -d '\r\n'
	else
		printf '%s' "$default_value"
	fi
}

export WPTS_STARTUP_DB_HOST
export WPTS_STARTUP_DB_USER
export WPTS_STARTUP_DB_PASSWORD
export WPTS_STARTUP_DB_NAME

WPTS_STARTUP_DB_HOST="$(read_wordpress_setting WORDPRESS_DB_HOST mysql)"
WPTS_STARTUP_DB_USER="$(read_wordpress_setting WORDPRESS_DB_USER example_username)"
WPTS_STARTUP_DB_PASSWORD="$(read_wordpress_setting WORDPRESS_DB_PASSWORD example_password)"
WPTS_STARTUP_DB_NAME="$(read_wordpress_setting WORDPRESS_DB_NAME example_database)"

# Check the database with PHP's mysqli extension, which is already present in
# the WordPress image. No additional mysql command-line client is required.
database_is_available() {
	php -r '
		$host = getenv("WPTS_STARTUP_DB_HOST");
		$port = 3306;
		$socket = null;

		if (preg_match("/^(.+):([0-9]+)$/", $host, $matches)) {
			$host = $matches[1];
			$port = (int) $matches[2];
		} elseif (preg_match("/^(.+):(.+)$/", $host, $matches)) {
			$host = $matches[1];
			$socket = $matches[2];
		}

		$connection = @mysqli_connect(
			$host,
			getenv("WPTS_STARTUP_DB_USER"),
			getenv("WPTS_STARTUP_DB_PASSWORD"),
			getenv("WPTS_STARTUP_DB_NAME"),
			$port,
			$socket
		);

		if ($connection === false) {
			exit(1);
		}

		mysqli_close($connection);
	' >/dev/null 2>&1
}

# Both the copied WordPress files and wp-config.php must exist before WP-CLI
# can run. The database must also accept connections.
wordpress_is_ready_for_setup() {
	[[ -f "$wordpress_path/wp-load.php" \
		&& -f "$wordpress_path/wp-config.php" ]] \
		&& database_is_available
}

wait_seconds="${WPTS_DB_WAIT_SECONDS:-120}"
attempt=1

# The database container may need several seconds on its very first start.
# Abort with a clear error if Apache exits or the configurable timeout expires.
until wordpress_is_ready_for_setup; do
	if ! kill -0 "$wordpress_pid" 2>/dev/null; then
		status=1
		wait "$wordpress_pid" || status=$?
		echo "The WordPress process stopped before first-run setup could begin." >&2
		exit "$status"
	fi

	if (( attempt >= wait_seconds )); then
		echo "WordPress files or the database were not ready within ${wait_seconds} seconds." >&2
		exit 70
	fi

	sleep 1
	attempt=$((attempt + 1))
done

# Create the WordPress tables and administrator only for a fresh database.
# During an upgrade-chain test the existing database is deliberately retained.
if ! wp_command core is-installed >/dev/null 2>&1; then
	wp_command core install \
		--url="${WPTS_SITE_URL:-http://localhost:8080}" \
		--title="${WPTS_SITE_TITLE:-WP Trip Summary release}" \
		--admin_user="${WPTS_ADMIN_USER:-wpts}" \
		--admin_password="${WPTS_ADMIN_PASSWORD:-wpts-change-me}" \
		--admin_email="${WPTS_ADMIN_EMAIL:-wpts@example.test}" \
		--skip-email
fi

# Activation is idempotent, so the same code also works when a container is
# recreated over the persistent database used by a previous release.
wp_command plugin activate wp-trip-summary
wp_command theme activate wp-alexboia-net

installed_wordpress_version="$(wp_command core version)"
installed_plugin_version="$(wp_command plugin get wp-trip-summary --field=version)"
installed_theme_version="$(wp_command theme get wp-alexboia-net --field=version)"

# Detect a malformed or incorrectly selected historical archive immediately.
if [[ -n "${WPTS_EXPECTED_PLUGIN_VERSION:-}" \
		&& "$installed_plugin_version" != "$WPTS_EXPECTED_PLUGIN_VERSION" ]]; then
	echo "Expected WP Trip Summary ${WPTS_EXPECTED_PLUGIN_VERSION}, but installed ${installed_plugin_version}." >&2
	stop_wordpress
	exit 65
fi

# This also verifies that the newer core copied over the legacy PHP 7.4
# runtime image reached the live /var/www/html installation.
if [[ -n "${WPTS_EXPECTED_WORDPRESS_VERSION:-}" \
		&& "$installed_wordpress_version" != "$WPTS_EXPECTED_WORDPRESS_VERSION" ]]; then
	echo "Expected WordPress ${WPTS_EXPECTED_WORDPRESS_VERSION}, but installed ${installed_wordpress_version}." >&2
	stop_wordpress
	exit 66
fi

# WordPress may create files as root during WP-CLI setup. Hand them back to the
# web-server user before accepting browser requests.
chown -R www-data:www-data "$wordpress_path/wp-content"

echo "WP Trip Summary ${installed_plugin_version} is active with WordPress ${installed_wordpress_version} and WP AbNet ${installed_theme_version}."

# Keep this wrapper alive for as long as the Apache process is alive and return
# Apache's exit status to Docker.
wait "$wordpress_pid"
