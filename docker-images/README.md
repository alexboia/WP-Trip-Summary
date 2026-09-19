# WP Trip Summary release images

This directory contains one Dockerfile for every downloadable WP Trip Summary
release found in the WordPress Plugin Directory or on the project's GitHub
Releases page on 2026-09-20.

The images are intended primarily for testing an upgrade from any historical
release to the latest release. Every image therefore runs WordPress 7.1.1,
rather than the WordPress version that was current when the plug-in was
published. PHP is selected as high as the unmodified historical package allows:

- releases `0.2b2` through `0.2.4` use PHP 7.4;
- releases `0.2.5` through `0.3.2` use PHP 8.4.

Every image also contains the exact WP Trip Summary release package, a pinned
WP AlexBoia.NET theme revision, WP-CLI 2.12.0, and a startup wrapper that
installs WordPress and activates the plug-in and theme.

See `versions.json` for the exact package URL, runtime image, WordPress/PHP
version, internal plug-in version, and pinned theme commit. `Dockerfile.0.2b2`
is extensively commented as a readable example of the shared build layout.

## Start a release

Copy `.env.example` to `.env`, select a Dockerfile, and choose a unique Compose
project name for the test environment:

```powershell
Copy-Item .env.example .env
# Edit WPTS_DOCKERFILE and WPTS_SITE_TITLE in .env.
docker compose -p wpts-upgrade-test up --build
```

Open <http://localhost:8080>. The development-only default administrator is:

- user: `wpts`
- password: `wpts-change-me`

Change these values in `.env` before exposing a container outside the local
machine.

## Test an upgrade chain

The database volume belongs to the Compose project, not to a particular
Dockerfile. Keep the same project name to retain the old release's data:

1. Set `WPTS_DOCKERFILE=Dockerfile.0.2.0` in `.env`.
2. Start it with `docker compose -p wpts-upgrade-test up --build` and create
   representative trips.
3. Stop the containers with `docker compose -p wpts-upgrade-test down`. Do not
   add `--volumes`.
4. Change `WPTS_DOCKERFILE` to the next release, or to the latest release.
5. Run `docker compose -p wpts-upgrade-test up --build --force-recreate`.
6. Open the site so the newly loaded plug-in can run its normal update path,
   then inspect the migrated data.

Repeat steps 3-6 for every intermediate release when testing the complete
chain. To discard the environment and its database only after the test:

```powershell
docker compose -p wpts-upgrade-test down --volumes
```

## Build without Compose

The build context must be this directory because every Dockerfile uses the two
shared installation scripts:

```powershell
docker build --file Dockerfile.0.3.2 --tag wpts:0.3.2 .
```

Running the image directly still requires a MySQL-compatible database and the
standard `WORDPRESS_DB_*` environment variables. `compose.yaml` provides a
MySQL 5.7 service so even the oldest database migration code can be exercised.

## Compatibility decisions

- WordPress Plugin Directory packages are preferred whenever the version is
  currently downloadable there. GitHub release assets fill the gaps.
- Every release uses WordPress 7.1.1. For PHP 8.4 releases the official
  `wordpress:7.1.1-php8.4-apache` image supplies both runtime and core.
- Official WordPress Docker images stopped publishing PHP 7.4 after WordPress
  6.1.1. The older plug-in images therefore use
  `wordpress:6.1.1-php7.4-apache` as the Apache/PHP runtime and copy the
  official WordPress 7.1.1 core archive over its bundled core. WordPress 7.1.1
  still supports PHP 7.4.
- PHP 8 cannot parse releases through `0.2.4` because their bundled
  `lib/3rdParty/MimeReader.php` uses removed curly-brace string offsets. Those
  packages remain unmodified and run on PHP 7.4. All PHP files in `0.2.5` and
  newer pass a PHP 8.4 syntax check.
- PHP 7.4 images use theme version 13.6, pinned immediately before PHP 8 union
  types were introduced. PHP 8.4 images use theme version 13.7.
- The WordPress Directory URL for `0.2.1` contains the unusual encoded suffix
  `%EF%80%8D`; it is the exact URL returned by the official plug-in API.
- GitHub release `0.2b2` contains plug-in version `0.2b`; both identifiers are
  retained in `versions.json`.
- `0.2.9` appears in the changelog, but has no downloadable WordPress package
  and no separate GitHub release, so it does not have a Dockerfile.
- `trunk` is intentionally excluded because it is mutable and is not a
  released version.

Syntax, manifest, and Compose validation can run without Docker. A full build,
WordPress installation, plug-in activation, and upgrade-chain test require a
running Docker daemon.
