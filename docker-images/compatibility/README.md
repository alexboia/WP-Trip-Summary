# Isolated compatibility sites

These two Compose profiles test the current local plugin sources in real
WordPress sites. Each builds its own image with production Composer dependencies,
installs WordPress and activates WP Trip Summary automatically.

| Profile | WordPress | PHP branch | MySQL | Site |
| --- | --- | --- | --- | --- |
| `minimum.env` | 7.0 | 8.3 | 5.7.44 | http://localhost:8083 |
| `latest.env` | 7.1.4 | 8.5 | 26.10.0 | http://localhost:8085 |

The minimum follows the requirements in `abp01-plugin-main.php` and the README.
WordPress names the 7.0.0 release `7.0`. PHP and MySQL use maintained/final patch
releases of the selected branches: this is a PHP 8.3 branch check, not a test of
the original PHP 8.3.0 binary or every PHP/MySQL patch release.

The upper profile is a snapshot of stable versions verified on **2026-11-10**
against the [WordPress release API](https://api.wordpress.org/core/version-check/1.7/),
[PHP releases](https://www.php.net/releases/?json&version=8.5), and the official
[WordPress image list](https://github.com/docker-library/official-images/blob/master/library/wordpress)
and [MySQL image list](https://github.com/docker-library/official-images/blob/master/library/mysql).
It includes the current MySQL Innovation release. It excludes beta/RC releases.
Update the profile deliberately when testing newer versions. WordPress and MySQL
versions are explicit; the WordPress image tags allow PHP patch updates within
8.3/8.5. Record the actual versions and image IDs with each release test.

## Start either or both sites

Requirements: Docker with Linux containers and Docker Compose v2 or newer.
The MySQL 5.7 image requires `linux/amd64`; ARM hosts need emulation. The profiles
currently select `linux/amd64` for both database images.

From the plugin root:

```bash
cd docker-images/compatibility
docker compose --env-file minimum.env up --build --renew-anon-volumes --wait --wait-timeout 240
docker compose --env-file latest.env up --build --renew-anon-volumes --wait --wait-timeout 240
```

`--wait` starts the services in the background and waits for authenticated
database access, plugin activation and an HTTP health check. Each startup logs
the actual PHP, WordPress and MySQL versions and the plugin requirement-check
result. A failed plugin requirement is a warning so the site remains available
for diagnosis; a healthy container alone does not mean compatibility passed.
The default administrator is
`wpts` / `wpts-change-me`; override `WPTS_ADMIN_USER`, `WPTS_ADMIN_PASSWORD` and
`WPTS_ADMIN_EMAIL` through the shell environment when needed.

Only the web ports are published, bound to `127.0.0.1`. No host source, `vendor`,
WordPress configuration or database directory is mounted. The build context is
restricted by `Dockerfile.dockerignore`, and Composer installs from the committed
lockfile inside the selected PHP runtime with `--no-dev` and a platform check.
The bundled WordPress theme is used. Automatic updates and WP-Cron are disabled
so the chosen versions remain under test.

The `minimum` and `latest` project names give each site separate database and
upload volumes; both can run together. To create another independent copy, add
`-p wpts-another-test` and select a free `WPTS_PORT` through the environment.
Do not reuse a database volume across the two different MySQL branches.

## Rebuild, inspect and test

Changes to local sources are incorporated by rebuilding, not by a live bind
mount. Run the same `up --build --renew-anon-volumes --wait` command again after
editing or switching revisions. **Keep `--renew-anon-volumes`:** the official
WordPress image uses an anonymous web-root volume, and reusing it can hide the
new image's code. Named database and upload volumes survive this operation.
Changes installed through the browser or WP-CLI in the web root are replaced.

Use `latest.env` in place of `minimum.env` to inspect the other site:

```bash
docker compose --env-file minimum.env ps
docker compose --env-file minimum.env logs --tail=100 wordpress
docker compose --env-file minimum.env exec wordpress php --version
docker compose --env-file minimum.env exec wordpress wp --allow-root core version
docker compose --env-file minimum.env exec wordpress wp --allow-root plugin get wp-trip-summary
docker compose --env-file minimum.env exec wordpress wp --allow-root eval 'global $wpdb; echo $wpdb->db_version();'
docker compose --env-file minimum.env exec wordpress wp --allow-root eval 'exit((new Abp01_Installer())->checkRequirements());'
docker compose --env-file minimum.env images
```

The explicit requirement-check command exits with `0` on success and the plugin
status code on failure. Verification on 2026-11-10 passes the internal requirement
check in both profiles, including WordPress `7.0` against the header's `7.0.0`:
the plugin's `RequiredVersion` comparator now normalizes missing numeric
components before comparison. Browser and release-package checks still need to
be performed separately. WP-CLI 2.12.0 also emits a deprecation
from its bundled `cli/Colors.php` under PHP 8.5 during activation; this is tool
output rather than a plugin assertion failure.

Check imports, route replacement/cache invalidation, the viewer and altitude
profile, trip/rider-log editors, protected posts and translations in the browser.
WordPress writes debug output to `/var/www/html/wp-content/debug.log` when needed.
Inspect both that file and the container logs after the checks.

To test a built release ZIP in an already running site:

```bash
docker compose --env-file minimum.env cp ../../build/output/wp-trip-summary.0.3.3.zip wordpress:/tmp/wp-trip-summary.zip
docker compose --env-file minimum.env exec wordpress wp --allow-root plugin install /tmp/wp-trip-summary.zip --force --activate
```

This replaces the running plugin; the next rebuild/recreation restores the image
sources. Testing the source image does not by itself validate the ZIP contents.
For a historical upgrade test, use the [release images](../README.md) to seed the
older plugin and representative data, then install the new ZIP there. Installing
an older package over an already initialized 0.3.3 database is not a clean
historical baseline.

These sites are for runtime, browser and package compatibility checks. They do
not run PHPUnit: the repository's development dependencies require PHP 8.4.1+
and pin a separate WordPress PHPUnit/core version. Continue to use the dedicated
test database and instructions in [CONTRIBUTING](../../CONTRIBUTING.md#running-tests)
for the full suite. Never point PHPUnit at either site's data if it should be
retained.

## Stop or reset

Stop the sites while keeping their databases and uploaded tracks/media:

```bash
docker compose --env-file minimum.env down
docker compose --env-file latest.env down
```

To intentionally discard one complete test environment and start fresh:

```bash
docker compose --env-file minimum.env down --volumes
docker compose --env-file minimum.env up --build --renew-anon-volumes --wait --wait-timeout 240
```

`down --volumes` deletes that project's test database and uploaded files. It does
not affect the other profile or the local WordPress installation.
