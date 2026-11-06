## Report a bug

[You can use New issue -> Bug report](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to file a new bug report.  
Please do so even if you are not sure whether or not the product should behave the way you expect it to, as this may hide, at the very least, some shortages in the way the plug-in communicates what it does.

## Request a feature you would like implemented

[You can use New Issue -> Feature request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to propose a new feature for this plug-in.  
If it is an already existing feature, go ahead anyway and propose a way to enhance it.

## Help with translating the plug-in

User interface translations live in `lang/`: editable `.po` catalogs, compiled `.mo` files and the shared [abp01-trip-summary.pot](lang/abp01-trip-summary.pot) template. Translations of predefined lookup values are maintained separately in [data/dev/setup/lookup-definitions.xml](data/dev/setup/lookup-definitions.xml).

### Translating user interface strings

To translate user interface strings, open the existing `.po` catalog in [POEdit](https://poedit.net/), or create a new catalog from the `.pot` template for the desired locale. Update existing catalogs from the template when source strings change.
[See this guide for an example](https://wplang.org/translate-theme-plugin/).  
Save both the `.po` catalog and its compiled `.mo` file under `lang/`, following the existing `abp01-trip-summary-<locale>` naming pattern, and include both in the pull request.

After changing translatable PHP strings, regenerate the template from the plug-in root with WP-CLI's `i18n make-pot` command available:

```bash
bash bin/makepot.sh
```

The script writes `lang/abp01-trip-summary.pot`; it does not update `.po` catalogs or compile `.mo` files. Its current exclusions include `media/`, so check JavaScript translation changes separately. Review the catalog diff and verify the affected UI in the target language.

When done, [open a new issue and request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) that it be processed and added as a plug-in translation.

## Actively contribute to the code base

You can contribute to the code base itself either by:
   - writing code to fix a specific bug or implement a feature; 
   - or by proposing refactoring of existing code ([New issue -> Propose refactoring](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose)).

### Repository skills

The repository includes four skills under `.agents/skills/`: reusable instructions, references and supporting utilities for contributors and coding agents. Start with [AGENTS.md](AGENTS.md) for repository structure and shared boundaries, then read the relevant `SKILL.md` before following its workflow.

| Skill | When to use it | What it covers |
| --- | --- | --- |
| [wpts-coding-conventions](.agents/skills/wpts-coding-conventions/SKILL.md) | Creating, editing or reviewing plugin code, templates and tests | PHP, JavaScript, TypeScript and CSS conventions, examples from the codebase, and the source license-header utility. |
| [wpts-document-hooks](.agents/skills/wpts-document-hooks/SKILL.md) | Inventorying, auditing or documenting actions and filters | Hook extraction and documentation parsing, inline contract documentation, migration-sensitive contracts, and generated documentation under `hook-docs/`. |
| [wpts-refactor-code-namespaces](.agents/skills/wpts-refactor-code-namespaces/SKILL.md) | Migrating legacy `Abp01_*` symbols to `WpTripSummary`, or adding conservative PHP types on request | Release-scoped migration lots, the class inventory, a conversion helper, legacy aliases and shims, and compatibility validation. |
| [wpts-marketing](.agents/skills/wpts-marketing/SKILL.md) | Planning or producing product marketing materials | Release-grounded messaging, README copy, screenshots and visual assets, travel photograph selection, and video workflow references. |

Each `SKILL.md` links to its detailed procedures, references and applicable utility checks. Keep those instructions in the skill files when updating a workflow. Combine skills when the task requires it; ordinary code changes do not automatically start a namespace migration or a hook audit. Namespace migration and type-hint-only work follow the current release lot defined by the refactoring skill.

For marketing work that updates the readmes or captures the UI, also follow [Updating the readme files](#updating-the-readme-files) and [Capturing screenshots](#capturing-screenshots) below.

### Setting up the development environment

You need everything required to run the plug-in itself ([see the requirements](README.md#wpts-requirements)), plus:

1. the xdebug extension (recommended);
2. PHP 8.4.1 or newer for the complete Composer development toolchain, including PHPUnit, installed with `composer install` (this is separate from the plug-in's PHP 8.3 runtime minimum);
3. wp-cli 2.x, available in your `$PATH`, to initialize the test environment if needed;
4. the Composer development dependency `bartlett/php-compatinfo`, invoked through `vendor/bin/phpcompatinfo`; see [Generating compatibility reports](#generating-compatibility-reports);
5. on Windows, Cygwin or the Windows Subsystem for Linux, to set up the development environment and run the test and build scripts, with:
   - the `wget`, `curl` and `zip` commands;
   - the gettext libraries;
   - the PHP CLI with the extensions listed in the plug-in requirements;
   - the MySQL command line client;
   - the Subversion command line client.

#### Development setup helper

[bin/install-dev.sh](bin/install-dev.sh) installs Composer dependencies and prepares optional WordPress test infrastructure. It expects this plug-in to be inside a WordPress installation. Before running it, make `php`, `curl`, `svn` and `wp` available, and configure that installation's `wp-config.php` to read `WP_DB_HOST_ENV` and `WP_DEBUG_ENV` through `getenv()`. The script validates these settings before installing tools.

Run it in a Unix-style environment with access to `/usr/local/bin` and `/usr/lib/wpi18n`, which it uses for tools and the WordPress translation library. It can download Composer and WP-CLI when needed. On Windows, use a suitably configured WSL or Cygwin environment for this setup helper.

```bash
bash bin/install-dev.sh --help
bash bin/install-dev.sh --db-name=wpts_test --db-user=wpts_test \
    --db-pass='replace-with-test-password' --db-host=127.0.0.1 \
    --wp-version=locked --skip-db-create=true --skip-wp-tests-scaffold=true
```

Use a dedicated test database. `--wp-version=locked` is the default: the helper reads the `wp-phpunit/wp-phpunit` version from `composer.lock`, using [bin/tools/get-wp-test-version.php](bin/tools/get-wp-test-version.php). Explicit versions remain supported. Both skip flags default to `true`; set `--skip-db-create=false` to allow database creation (requires `mysqladmin`), or `--skip-wp-tests-scaffold=false` to generate the test scaffold. If `bin/install-wp-tests.sh` already exists, the helper still invokes it with the supplied database/version parameters and database skip flag. Check the resulting test configuration before running PHPUnit.

The scaffold installer reuses an existing core directory. When upgrading WordPress tests, select a fresh directory through `WP_CORE_DIR` (for example `/tmp/wpts-wordpress-7.1.3`) so it downloads the matching core instead of retaining an older installation.

To replace the test core at the default `/tmp/wordpress` path, run:

```bash
bash bin/update-wp-test-core.sh
```

This helper runs `composer install`, reads the locked WordPress test version and downloads it with WP-CLI into `/tmp/wordpress-<version>`. After a successful download and a check for the required core files, it deletes `/tmp/wordpress` and moves the new core into that location. Failed dependency installation or download leaves the old core in place. It can be run from the plug-in root or from `bin/`.

#### Compiling TypeScript

Browser sources and [tsconfig.json](media/js/tsconfig.json) live under `media/js/`, including its `admin/` directory. Edit `.ts` files where they exist and regenerate their companion `.js` files; `.d.ts` files can be handwritten contracts. Scripts without a TypeScript source are edited directly.

Install the npm dependencies from both manifests and use an available TypeScript compiler:

```bash
npm install
npm --prefix media/js install
tsc --version
tsc --project media/js/tsconfig.json --noEmit
tsc --project media/js/tsconfig.json --noEmitOnError
```

The manifests do not pin TypeScript or define npm build/test scripts. Record the compiler version used for a change; the local compiler inspected for this workflow is 5.9.3. The configuration targets ES2020/CommonJS and emits JavaScript beside its sources without source maps. Check the generated diff, include the intended source/output pairs, and verify the affected UI in WordPress. Dependency or compiler upgrades should be separate, intentional changes.

### Running tests

The locked test dependencies are WordPress PHPUnit 7.1.3, PHPUnit 9.6.38, PHPUnit Polyfills 1.1.5, Mockery 1.6.15 and Faker 1.24.1. PHPUnit is an explicit development dependency. These are the latest compatible stable versions at this update: [WordPress 7.1.3 requires PHPUnit Polyfills 1.x](https://github.com/WordPress/wordpress-develop/blob/7.1.3/composer.json), whose supported PHPUnit versions stop at 9.x. Updating PHPUnit to a newer major requires corresponding WordPress test-suite support.

Use `composer install` to reproduce the lockfile. For an intentional test dependency update, run:

```bash
composer update phpunit/phpunit yoast/phpunit-polyfills wp-phpunit/wp-phpunit fakerphp/faker mockery/mockery --with-dependencies --minimal-changes
php bin/tools/get-wp-test-version.php
```

Provision the reported WordPress core version before running the updated suite. Review `composer.lock` and run the tests after an update.

With the PHPUnit dependencies and WordPress test environment configured, run:

```bash
bash bin/run-tests.sh
bash bin/run-tests.sh --set=routes
bash bin/run-tests.sh --filter='RouteTrackPointTests::test_'
bash bin/run-tests.sh --set=documents --filter='GpxDocumentParserTests'
```

`--set SET` and `--filter PATTERN` are also accepted. A filter applies within the selected set. Other PHPUnit options are forwarded unchanged. Run `bash bin/run-tests.sh --help` for usage information. The script can also be called from `bin/` or by its path from another directory, and returns PHPUnit's exit status.

| Set | Tests |
| --- | --- |
| `all`, `default` | All tests; used when `--set` is omitted |
| `core` | Environment, settings, lookup data, changelog and common helpers |
| `auth` | Authorization and nonce providers |
| `validation` | Input filtering, validation rules and validator providers |
| `routes` | Route data, tracks, geometry and processing |
| `documents` | GPX/GeoJSON parsers, validators and parser factory |
| `installer` | Installation, removal and requirements |
| `modules` | Module activation, hosting and dependency selection |
| `ui` | Admin actions, columns, menus, views and frontend themes |
| `logging` | Audit, system and route logs |
| `io` | Files, downloads, maintenance and server directives |
| `sec` | Leaflet wrapper security and Nginx access directives |
| `leaflet` | Live HTTP integration checks for the Leaflet wrapper; not a PHPUnit suite |

Except for `leaflet`, the sets are PHPUnit suites defined in `phpunit.xml`. When adding a test file, include it in the corresponding thematic suite. The `default` suite discovers every `tests/test-*.php` file automatically and remains the default for direct PHPUnit and `composer test` runs.

The wrapper also provides this shortcut for the [Leaflet HTTP integration checks](#testing-the-leaflet-script-wrapper):

```bash
bash bin/run-tests.sh --set=leaflet --host http://localhost:8080
```

Use the space-separated `--host HOST` form: the current parser does not handle a nonempty `--host=HOST` value correctly. The shortcut runs the default HTTP test mode; use the PHP runner directly for options such as `--mode=load` or `--plugin-path`.

#### Running tests from PowerShell

The test bootstrap uses an explicit `WP_TESTS_DIR` first, otherwise the Composer-provided `WP_PHPUNIT__DIR` (`vendor/wp-phpunit/wp-phpunit`), with `/tmp/wordpress-tests-lib` as a fallback. This avoids silently selecting an old scaffold library after a Composer update. The test library is separate from the WordPress core installation needed to run the tests.

Set `WP_CORE_DIR` to the WordPress core installation to use for testing. Use a WordPress version matching the `wp-phpunit/wp-phpunit` version in `composer.lock`; `php bin/tools/get-wp-test-version.php` prints it. Without this variable, the test configuration looks for `wordpress` under PHP's temporary directory. When using the Composer test library, the bootstrap rejects a missing or mismatched core before WordPress resets test tables.

Configure the test database connection in `tests/wp-tests-config.php` before running the suite, or set `WP_TESTS_CONFIG_FILE_PATH` to an absolute path to a separate test configuration. An already-defined constant of that name takes precedence. Use a dedicated test database: WordPress recreates its test tables during setup. In a custom configuration, derive `ABSPATH` from `WP_CORE_DIR` and set `WP_PHP_BINARY` to `PHP_BINARY` so the WordPress installer uses the same PHP executable as PHPUnit.

Each run resets the plug-in's test tables and activates it against the freshly installed WordPress fixture. The old `.wpts-installed` checkout marker is no longer used. Fixture setup failures abort the run with a nonzero status; a failed bootstrap must never count as a passing suite. File fixtures remain the responsibility of their tests, and the bootstrap does not remove storage directories while the plug-in's logger is open.

From the plug-in directory:

```powershell
composer install
$env:WP_CORE_DIR = 'D:\path\to\wordpress'
php .\vendor\bin\phpunit -c .\phpunit.xml
```

### Testing the Leaflet script wrapper

[bin/tools/test-leaflet-wrapper.php](bin/tools/test-leaflet-wrapper.php) runs HTTP integration tests against [abp01-plugin-leaflet-plugins-wrapper.php](abp01-plugin-leaflet-plugins-wrapper.php). It exercises the deployed wrapper through the web server, including rewrite rules and `REQUEST_URI`. Run it directly with PHP, independently of the PHPUnit suites above.

#### Requirements and usage

- PHP CLI with the cURL extension enabled.
- A reachable WordPress installation at the host root, serving the same plug-in version and JavaScript files as the local checkout. The runner reads local scripts and plug-in constants to build its expectations.
- Public access to the plug-in's `media/js/abp01-common.js` and WordPress's `/wp-includes/js/jquery/jquery.js`. The runner checks these before testing traversal, so an absent target cannot produce a misleading pass.
- For rewrite tests, Apache must have `mod_rewrite` enabled and honor the plug-in's [.htaccess](.htaccess) rules.

From the plug-in root:

```bash
php bin/tools/test-leaflet-wrapper.php alexboia.net.local:8080
php bin/tools/test-leaflet-wrapper.php https://example.com --mode=load
php bin/tools/test-leaflet-wrapper.php alexboia.net.local:8080 --mode=rewrite
php bin/tools/test-leaflet-wrapper.php https://example.com --plugin-path=/wp-content/plugins/wp-trip-summary
php bin/tools/test-leaflet-wrapper.php --help
```

Replace the example hosts with the installation being tested. The positional argument accepts a hostname with an optional port, or an HTTP(S) origin. It defaults to `http://` when the scheme is omitted. Supply the final origin: redirects are not followed. Credentials, URL paths, query strings and fragments are rejected.

| Option | Behavior |
| --- | --- |
| `--mode=all` | Default; run both `load` and rewrite tests |
| `--mode=load` | Call the PHP wrapper with `load` relative to the plug-in directory; exercises the fallback used when rewrite is unavailable |
| `--mode=rewrite` | Request script URLs directly, allowing the server to route them to the wrapper with the original `REQUEST_URI` |
| `--plugin-path=/wp-content/plugins/<directory>` | Override the remote plug-in URL path; defaults to `/wp-content/plugins/` followed by the local plug-in directory name |
| `--help` | Print usage and exit |

Options use the `--name=value` form. The runner does not change server configuration or create remote fixtures. Select `--mode=load` on a host without rewrite support; `all` expects both routes to work. A static JavaScript response fails the rewrite tests because they check the complete wrapped content.

#### Coverage and results

The suite checks:

- Fullscreen, magnifying glass and magnifying glass button scripts, with and without `ver`: HTTP status, complete source wrapped with `window.abp01Leaflet`, JavaScript content type and byte length.
- Cache headers (`ETag`, `Cache-Control`, `Expires`), repeated requests, matching and stale `If-None-Match`, version changes, and bodyless `304` responses. Missing or forbidden files must still be rejected when a cache validator is supplied.
- Missing files, directories, CSS files and existing JavaScript outside the permitted Leaflet directory or plug-in. Traversal cases include relative and root-relative paths, double encoding and backslashes; malformed inputs include external URLs, null bytes and array-valued `load`.
- Missing or empty `load`, fallback to `REQUEST_URI`, unrelated query parameters, and precedence of an explicit `load` over the rewritten URL.

Access checks in rewrite mode send invalid `load` values through a valid rewritten script URL. This ensures the request reaches the wrapper, instead of depending on how the server serves unrelated static paths.

Each assertion case prints `[PASS]` or `[FAIL]`, followed by totals at the end. Assertion failures allow the remaining cases to run; setup or transport errors abort the run with `[ERROR]`.

| Exit code | Meaning |
| --- | --- |
| `0` | All selected cases passed, or help was requested |
| `1` | One or more assertion cases failed |
| `2` | Invalid arguments, missing dependencies or reference files, or an HTTP transport error |

#### Extending the runner

Keep each operation in a function with a clear responsibility. `wpts_leaflet_test_main()` handles CLI execution, while `wpts_leaflet_test_suite()` prepares the inputs and coordinates the test groups.

| Responsibility | Functions |
| --- | --- |
| Parse and validate CLI input | `wpts_leaflet_test_options()`, `wpts_leaflet_test_parse_arguments()`, host and plug-in path validation helpers |
| Prepare reference data | `wpts_leaflet_test_script_paths()`, `wpts_leaflet_test_read_sources()`, `wpts_leaflet_test_verify_traversal_targets()` |
| Send HTTP requests and read headers | `wpts_leaflet_test_request()`, `wpts_leaflet_test_create_http_request()`, `wpts_leaflet_test_collect_response_header()` |
| Check a script response | `wpts_leaflet_test_script()` delegates content, header, ETag and expiration checks to separate helpers |
| Run scenario groups | `wpts_leaflet_test_run_script_cases()`, `wpts_leaflet_test_run_path_cases()`, `wpts_leaflet_test_run_cache_cases()`, `wpts_leaflet_test_run_access_cases()`, `wpts_leaflet_test_run_load_cases()`, `wpts_leaflet_test_run_rewrite_cases()` |
| Define cache and access scenarios | `wpts_leaflet_test_cache_case_request()`, `wpts_leaflet_test_cache_case_response()`, `wpts_leaflet_test_invalid_loads()` |
| Report outcomes | `wpts_leaflet_test_run()`, `wpts_leaflet_test_report_totals()`; terminal formatting is shared in [bin/tools/common.php](bin/tools/common.php) |

Add new scenarios to the corresponding group and reuse the request and assertion helpers. Preserve the test names, messages and exit codes during refactoring; compare runs against the same host before and after the change.

## Generating compatibility reports

[bin/detect-compat-info.sh](bin/detect-compat-info.sh) checks the platform requirements of production dependencies with Composer and uses the Composer-installed PHP CompatInfo to inspect the plugin and test sources. Make `composer` and `php` available on `PATH`, and install development dependencies with `composer install` first. The current locked toolchain requires PHP 8.4.1 or newer, and the default SQLite connection needs both `sqlite3` and `pdo_sqlite` in the CLI configuration. These are analysis-tool requirements; the reports do not change the plugin's declared runtime requirements.

```bash
bash bin/detect-compat-info.sh
```

The script resolves the plugin root from its own location and creates `build/compat-info/`. It first runs `composer --no-interaction --no-ansi check-platform-reqs --no-dev`, saving both standard output and errors to `platform-deps.txt`. A failed platform check prints the report to standard error and stops the script with a nonzero exit status before database preparation or source analysis.

The script then checks the reference database with `diagnose`; when the database is missing or uninitialized, it runs `db:new` (schema creation plus reference-data import), then checks it again. A valid database is reused. Initialization runs without `--force`. A nonzero exit status from initialization or analysis also stops the script.

| Output | Purpose |
| --- | --- |
| `build/compat-info/compatinfo-db.sqlite` | Default reference database for the analyzer, separate from WordPress and its test database |
| `build/compat-info/cache/wpts-file-errors/` | Default analyzer cache for the local extension configuration, including its environment subdirectory |
| `build/compat-info/platform-deps.txt` | Composer platform check for installed production dependencies against the current CLI environment; development dependencies are excluded |
| `build/compat-info/database-diagnose.txt` | Latest database and runtime prerequisite diagnostics |
| `build/compat-info/main-compat-info.txt` | Plugin source analysis, excluding `tests`, `help`, `build`, `bin`, `docs`, `.agents` and `vendor` |
| `build/compat-info/tests-compat-info.txt` | Separate analysis of `tests/` |

Each report is overwritten when its check starts; a failed or interrupted check may leave its report incomplete. Reports for later steps remain from any previous run if the script stops early. There is no Help report because that source directory has been removed. The main scan excludes `vendor/` so that development tools do not affect the plugin source report.

The [Composer platform check](https://getcomposer.org/doc/03-cli.md#check-platform-reqs) validates the PHP and extension requirements declared by installed production dependencies, including transitive dependencies. It checks the real CLI environment and ignores `config.platform`; passing on the development machine does not establish compatibility with the plugin's minimum supported PHP version. Run the same Composer check and runtime tests on each supported target environment. The source report and platform check complement each other; neither replaces runtime tests or a source audit of the production dependencies shipped in the release. Interpret them alongside `composer.lock` and plugin metadata.

Existing `DATABASE_URL` and `APP_CACHE_DIR` values override the default database and cache base directory. The script appends `wpts-file-errors` to the cache base so an older CLI container built without the local extension cannot bypass its registration. Use a database dedicated to this analyzer. With the default Windows SQLite path, the script accounts for CompatInfoDB's leading-slash normalization by using a path rooted on the current drive.

The CLI memory limit defaults to `2G` for reference-data import and analysis; this affects only the analyzer processes. To adjust it:

```bash
WPTS_COMPAT_MEMORY_LIMIT=4G bash bin/detect-compat-info.sh
```

If preparation fails, inspect the command output and the relevant `platform-deps.txt` or `database-diagnose.txt` report, verify the CLI extensions and directory permissions, then rerun. A schema-only database left by an interrupted initial import can be populated on the next run. Save changes to the shell script before starting a run.

### Local PHP CompatInfo extension

The script loads [bin/tools/compatinfo/config.php](bin/tools/compatinfo/config.php) through `--config`. It imports the upstream default services and registers [ResetFileErrorsExtension](bin/tools/compatinfo/ResetFileErrorsExtension.php) through PHP CompatInfo's `app.extension` mechanism. No dependency source files are patched.

PHP CompatInfo 7.2.7 retains its collecting error handler across files. Once an empty or invalid file adds an error, later valid files can be reported with that same error. The extension clears the handler on `BeforeFileAnalysis`, after the preceding file's errors have been collected and before the next file is parsed. Real errors remain in the report, and `--stop-on-failure` still stops at the first error.

The upstream query handler constructs its error handler directly. The local configuration replaces that service with [CompatibilityQueryHandler](bin/tools/compatinfo/CompatibilityQueryHandler.php), which passes a shared `Collecting` service through the parser's public `parse()` method. The reset extension receives that same service through constructor injection and calls `clearErrors()` directly. For `--stop-on-failure`, the query handler keeps the upstream `Throwing` behavior. This uses public interfaces without reflection or access to private parser state.

Review the upstream query handler for changes to source discovery or parser arguments, and rerun the standalone regression check when upgrading PHP CompatInfo:

```bash
php bin/tools/compatinfo/test-reset-file-errors.php
```

The check uses an isolated reference database and retains its fixtures and reports under `build/compat-info/`. It covers empty and malformed files followed by valid files, cached-container loading, `--stop-on-failure`, source exclusions and missing sources. When changing the service configuration or upgrading dependencies, clear the dedicated `wpts-file-errors` cache before running the main script again; the reference SQLite database is stored separately.

## Building a distribution package

[bin/build.sh](bin/build.sh) creates `build/output/wp-trip-summary.<version>.zip`, using the version in `abp01-plugin-main.php`. Run it from the plugin root with Bash, PHP, `awk`, `zip` and the standard Unix file utilities available. The library path checker requires PHP 8.2 or newer.

Prepare the package in a separate release checkout or staging copy so that its `vendor/` contains production dependencies only:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php bin/tools/build-readme.php
php bin/tools/check-lib-paths.php
bash bin/build.sh
```

Run the relevant tests and compatibility analysis with development dependencies before preparing this staging copy. Compile changed TypeScript and translation catalogs beforehand as well. The build copies the prepared files; it does not run PHPUnit, compile TypeScript/catalogs or regenerate the readmes. Its compatibility-analysis call is currently commented out, so run that check separately when needed.

The script clears `build/output/` and `build/tmp/`, stages the plugin files, copies all of `vendor/`, creates and checks the ZIP, then clears the temporary files. Keep anything you need to retain outside those directories. Inspect the archive contents and test installation/upgrade in a development WordPress instance before distribution; its final `DONE!` message alone does not establish that every external command succeeded.

### WordPress.org export status

[bin/export-wp-plugin-dir-svn.sh](bin/export-wp-plugin-dir-svn.sh) prepares a local WordPress.org SVN checkout under `build/wp-plugin-dir-svn/`; it does not commit it. It currently still invokes the removed `help/tools/make-help.php` and copies `data/help/`. Update and verify those obsolete steps before using it as a release workflow. The export also clears the local `trunk/` contents and copies the complete `vendor/`, so its staging inputs need the same review as the ZIP build.

## Capturing screenshots

[bin/tools/screen-capture/run.py](bin/tools/screen-capture/run.py) captures the plug-in's admin screens and frontend viewer using Playwright and headless Firefox. It also generates filtered image variants and a JSON catalog describing each capture.

### Preparing the environment

Use Python 3.12 or newer; the commands below use Python 3.13, which has been used to verify the tool. The Python dependencies are pinned in [requirements.txt](bin/tools/screen-capture/requirements.txt).

Prepare a development WordPress installation with WP Trip Summary active and an administrator account that can access Settings, the plug-in's admin pages and the sample post editor. Login must work through the standard username/password form; the tool does not handle interactive CAPTCHA or two-factor authentication challenges.

The sample editor post must expose the Trip Summary launcher and route log metaboxes. For the frontend captures, prepare a post with trip information, a GPS track with altitude data, and visible teaser, map, altitude profile and route log controls. The editor and frontend samples can refer to the same post. The configured map tile service must be reachable from Firefox.

From the plug-in root, in PowerShell:

```powershell
cd bin\tools\screen-capture
py -3.13 -m venv .venv
.\init.ps1
.\.venv\Scripts\python.exe -m playwright install firefox
.\.venv\Scripts\python.exe run.py --help
```

[init.ps1](bin/tools/screen-capture/init.ps1) creates `.venv` if needed and installs the Python dependencies. The separate Playwright command installs the Firefox binary used by the tool. Run it again after updating Playwright. Calling `.venv\Scripts\python.exe` explicitly keeps all commands on the same interpreter; activating the environment is optional.

### Configuring the capture run

Keep the working directory at `bin/tools/screen-capture` for the following commands. Both `config.yaml` and relative output paths are resolved from the current working directory.

```powershell
.\.venv\Scripts\python.exe run.py
```

On the first run, or when the configuration is invalid, the tool prompts for five required values, writes `config.yaml`, then starts capturing. A valid existing configuration is reused. The file has this structure; replace every sample value with your development site's details:

```yaml
baseUrl: "http://localhost:8080"
userName: "screenshot-user"
password: "replace-with-local-password"
knownSamplePostEdit: "42"
knownSamplePostView: "/sample-trip/"
```

`baseUrl` is the WordPress installation URL, including `http://` or `https://`, an optional port and an optional installation subdirectory. It must not include credentials, a query or a fragment. `knownSamplePostEdit` is a positive post ID used for the listing audit window and editor captures. `knownSamplePostView` is a permalink relative to `baseUrl`, such as `/sample-trip/` or `?p=42`.

The password prompt hides typed characters, but the saved YAML contains the password in plain text. The tool's `config.yaml` is ignored by Git; keep it local. To change the saved values interactively, run:

```powershell
.\.venv\Scripts\python.exe run.py --reconfigure
```

Press Enter to retain an existing value, including the password. Reconfiguration continues into the capture workflow after saving; it is not a setup-only command.

### Command-line options

```powershell
.\.venv\Scripts\python.exe run.py --out-dir ./screenshots/review --viewport 1920x1080
.\.venv\Scripts\python.exe run.py --full-pages --verbose
```

| Option | Current behavior |
| --- | --- |
| `--out-dir PATH` | Output directory; defaults to `./screenshots`. A new directory path is accepted, but it cannot point to an existing file or have a file as a parent. |
| `--viewport WIDTHxHEIGHT` | Browser viewport; defaults to `1920x1080`. Both dimensions must be positive integers. Uppercase `X` and surrounding spaces are also accepted; quote values containing spaces. |
| `--reconfigure` | Prompt for configuration again, using valid saved values as defaults, then capture. |
| `--full-pages` | Adds full-page images throughout the capture sequence, alongside the regular page and element captures. Full-page filenames end in `-full.png`. |
| `--report` | Prints a table of captured screenshots after saving the catalog; it does not change which screens are captured. |
| `--verbose` | Log navigation requests, responses and failures during login. |
| `--host URL`, `--user-name USER`, `--password PASSWORD` | Parsed and validated, but currently not applied to the configuration used by `main()`. Use `config.yaml` or `--reconfigure` to change login details. |
| `--save-config` | Accepted by the parser, but currently has no effect in `main()`. Interactive setup and reconfiguration save the YAML file. |
| `-h`, `--help` | Print the CLI help and exit. |

The limitations above describe the current implementation; the help text for the credential override options describes their intended behavior.

### Captured screens and output

Each run executes the complete capture sequence:

- About, Settings tabs and the predefined map tile layers dialog.
- Maintenance page, missing track files report and Nginx access directives helper.
- System logs, lookup data management and its add-item form.
- Post listing and the sample post's audit window, including a separate crop of the window. The tool follows listing pagination to find the configured post.
- Five post editor element captures: the launcher metabox, the trip editor on Info and Map, the route log metabox and its add-entry form. `--full-pages` adds one image of the entire editor page with the dialogs closed.
- Frontend teaser and viewer on Info, Map, Map with altitude profile, and Route Log.

The lookup and post editor forms are opened for capture without saving their contents. The complete run also executes the two maintenance tools listed above and accepts their confirmation dialogs. It temporarily changes the **site language** to US English and restores the previous value after the capture sequence succeeds. If capture stops before that restoration step, restore the language through WordPress Settings > General.

Output is organized as follows:

```text
screenshots/
  wpts-about.png
  wpts-admin-post-edit-launcher.png
  wpts-admin-post-edit-info.png
  wpts-admin-post-edit-map.png
  wpts-admin-post-edit-route-log.png
  wpts-admin-post-edit-route-log-form.png
  ...
  filtered/
    wpts-about.png
    ...
  catalog.json
```

The original PNGs are kept in the output directory. `filtered/` contains copies processed with the softening and vignette filters. After a successful run, `catalog.json` records `page`, `url`, `outFile`, `isFullPage` and `element` for each original capture; URLs are stored relative to the configured WordPress base URL. Reusing an output directory overwrites matching filenames, so use a different `--out-dir` when keeping multiple capture sets.

Review the images before using them in documentation. The tool does not copy or rename them into `assets/en_US/`; select the required images and update the readme screenshot assets and captions separately, following [Updating the readme files](#updating-the-readme-files).

### Troubleshooting

- **Missing Python imports:** run the script and dependency installation through the same `.venv\Scripts\python.exe` interpreter shown above.
- **Firefox executable missing:** run `.\.venv\Scripts\python.exe -m playwright install firefox` from the tool directory.
- **Login timeout:** rerun with `--verbose` and check the saved credentials, WordPress URL, redirects and any login challenge. Each run uses a fresh browser context.
- **Missing controls or map timeout:** check that the configured sample posts contain the expected Trip Summary content, the metaboxes are enabled, and the track and map tiles load normally in Firefox. Most explicit waits use the `WPTS_TIMEOUT` constant, currently 10 seconds.

## Updating the readme files

`README.md` (GitHub) and `README.txt` (WordPress.org) are generated by [bin/tools/build-readme.php](bin/tools/build-readme.php). Do not edit them directly: edit their sources in `readme/` and rebuild. `README.txt` is also read at runtime: the plug-in's About page extracts its `== Changelog ==` section, and the build and SVN export scripts copy it as `readme.txt`.

### Sources

| Source | Purpose |
| --- | --- |
| [readme/manifest.json](readme/manifest.json) | Central configuration: Makefile location, source paths and variables |
| [readme/Makefile](readme/Makefile) | One recipe per target (`[Github]`, `[WpOrg]`); every difference between the two readme files lives here |
| `readme/sections/*.md` | Section fragments, written in GitHub-flavored Markdown; a fragment is referenced by its file name without `.md` |
| [CHANGELOG.md](CHANGELOG.md) | Changelog entries, used by `@changelog` |
| `assets/en_US/` | WordPress.org screenshots, captioned in the `[WpOrg]` recipe |
| `readme/docs/` | Longer documentation linked from the fragments; not assembled |
| `readme/archive/` | The previous readme fragments, kept for reference; not assembled |

To change the content, edit or add a fragment. To change where it appears, edit the Makefile. Fragments may contain HTML comments for maintainers: they are removed from both outputs, and comments containing `TODO` are reported as warnings.

### Usage

From the plug-in root, with PHP 8.2 or newer:

```bash
php bin/tools/build-readme.php                         # build every target
php bin/tools/build-readme.php --target=WpOrg          # build one target; repeatable, case-insensitive
php bin/tools/build-readme.php --check                 # write nothing; report outdated outputs
php bin/tools/build-readme.php --output-dir=/tmp/out   # preview: write the outputs to another directory
php bin/tools/build-readme.php --manifest=path/to.json # use another manifest
php bin/tools/build-readme.php --help
```

`--check` cannot be combined with `--output-dir`. Line endings are ignored when comparing; outputs are written with LF line endings.

| Exit code | Meaning |
| --- | --- |
| `0` | Outputs built, or up to date with `--check` |
| `1` | `--check` found at least one outdated output |
| `2` | Invalid arguments, configuration or sources; the message names the file and, for the Makefile, the line |

Warnings do not change the exit code. They report fragments with TODO notes, a `Stable tag` without a matching changelog entry and relative links that cannot be made absolute.

### The manifest

```json
{
	"makefile": "readme/Makefile",
	"pluginHeader": {
		"file": "abp01-plugin-main.php",
		"variables": {
			"version": "Version"
		}
	},
	"paths": {
		"sections": "readme/sections",
		"screenshots": "assets/en_US",
		"changelog": "CHANGELOG.md"
	},
	"variables": {
		"repositoryUrl": "https://github.com/alexboia/WP-Trip-Summary"
	}
}
```

| Key | Content |
| --- | --- |
| `makefile` | Path of the Makefile |
| `pluginHeader.file` | PHP file holding the WordPress plug-in header |
| `pluginHeader.variables` | Variable names mapped to plug-in header fields; `"version": "Version"` exposes `{{version}}` |
| `paths.sections` | Directory of the section fragments |
| `paths.screenshots` | Directory of the screenshots listed with `screenshot[file]` |
| `paths.changelog` | Markdown changelog file |
| `variables` | Additional variables, as name/value pairs |

All keys are required. Paths are relative to the plug-in root and cannot contain `..`. Variable names start with a letter and contain letters, digits and `_`; a name cannot be defined both in `pluginHeader.variables` and in `variables`. Unknown keys are rejected, to catch typos.

#### How variables are populated

The two variable blocks are filled differently:

- **`variables`** holds literal values: `"repositoryUrl": "https://..."` defines `{{repositoryUrl}}` with that exact value.
- **`pluginHeader.variables`** holds header field names, not values. On every build, the generator reads the first 8 KB of `pluginHeader.file` and looks up each field, as WordPress does with `get_file_data()`. `"version": "Version"` gives `{{version}}` the value of the ` * Version: 0.3.3` line. A field that is missing or empty stops the build.

Do not write version numbers in the manifest: change them in the plug-in header, and both readme files follow it on the next build. To pin a value that should not come from the header, define it in `variables` instead, and remove it from `pluginHeader.variables`.

### The Makefile

```text
# A comment
[Github]
format = markdown
output = README.md
sections = hero, why, screenshots, \
	features, faq

[WpOrg]
format = wporg
header[Stable tag] = {{version}}
section[Changelog] = @changelog
```

| Syntax | Meaning |
| --- | --- |
| `[Target]` | Starts the recipe of a target; names are unique, ignoring case |
| `key = value` | Sets a value; a key can be set only once per target |
| `key += value` | Appends comma-separated items to a list value, or sets it if undefined |
| `key[Name] = value` | Adds an entry to an ordered map; entries keep the order in which they are written |
| `\` at the end of a line | Continues the value on the next line |
| `#` at the start of a line | Comment |

Values can use `{{variable}}` placeholders from the manifest. An unknown variable stops the build. List values are separated by commas; their items are:

| Item | Output |
| --- | --- |
| `name` | The fragment `readme/sections/name.md` |
| `@changelog` | Every version in the changelog |
| `@changelog:N` | The latest `N` versions |
| `@screenshots` | The target's `screenshot[file] = caption` entries |

The `format` key selects how a target is rendered, and each format accepts its own keys. A key that the format does not accept stops the build.

#### Format `markdown`

| Key | Required | Content |
| --- | --- | --- |
| `output` | Yes | Output file, relative to the plug-in root |
| `sections` | Yes | Items, in order |
| `banner` | No | Text placed before the first section, such as a "generated file" comment |
| `screenshot[file]` | No | Captions for `@screenshots`, rendered as Markdown images |

The fragments are copied as written, separated by one blank line.

#### Format `wporg`

| Key | Required | Content |
| --- | --- | --- |
| `output` | Yes | Output file, relative to the plug-in root |
| `title` | Yes | Plug-in name, rendered as `=== Title ===` |
| `short-description` | Yes | Summary below the header; at most 150 characters |
| `header[Field]` | No | Header fields (`Contributors`, `Tags`, `Stable tag` etc.), in order |
| `section[Name]` | Yes | Items of the `== Name ==` section; sections keep their order |
| `screenshot[file]` | No | Captions for `@screenshots`, numbered in order: the first entry is `screenshot-1` on WordPress.org |
| `link-base` | No | URL prefixed to relative links, such as `{{repositoryUrl}}/blob/master/` |
| `changelog-links` | No | `keep` (default) or `text`: replace changelog links with their text, so the About page shows plain text |

Fragments are converted to the WordPress.org readme format. Fenced code blocks are left unchanged.

- Level 1 to 3 headings become `= Heading =`. When a fragment is the only item of its section, its first heading is dropped and the section name serves as its title.
- A line containing only a bold question, `**Question?**`, becomes `= Question? =`. FAQ fragments therefore write each question on its own line, followed by a blank line and the answer.
- Image lines are removed. A linked image, such as a badge, becomes a text link to the image's target. Other inline images are removed.
- HTML tags are removed and their text is kept. `<a name="...">` anchors are removed entirely.
- Links to `#anchors` become plain text. Relative links are prefixed with `link-base`; without it, they are kept and reported.
- Changelog versions are rendered as `= X =`, followed by their entries.

### Writing fragments

- Start each fragment with a `## Title` heading. Add an `<a name="wpts-...">` anchor below it if other sections link to it.
- Write for GitHub first; check the WordPress.org output with `--output-dir` and the [readme validator](https://wordpress.org/plugins/developers/readme-validator/).
- Link to repository files with paths relative to the plug-in root, such as `readme/docs/file-formats.md`. They work on GitHub and become absolute in `README.txt`.
- When the two targets need different content, create a separate fragment, such as `upgrade-notice.md`, and reference it only from the target that needs it.

The changelog is read from `### Version X` headings. A version ends at the next heading of level 1 to 3, so sections such as `## Upgrade notices` are not included.

### Extending the generator

Each class lives in `bin/tools/readme/` and is loaded by `build-readme.php`.

| Responsibility | Classes |
| --- | --- |
| Read and validate the manifest | `ReadmeManifest`, `ReadmeManifestPluginHeader`, `ReadmeManifestPaths`, using `ReadmeJsonObject` |
| Parse the Makefile | `ReadmeMakefile`, `ReadmeMakefileTarget`, `ReadmeMakefileValue` |
| Read sources | `ReadmePluginHeader`, `ReadmeChangelog`, `ReadmeChangelogVersion` |
| Share build state: variables, fragments, warnings | `ReadmeBuildContext`, `ReadmeSectionItem` |
| Render a target | `ReadmeTargetRenderer` and its subclasses `ReadmeMarkdownRenderer` and `ReadmeWpOrgRenderer` |
| Text helpers | `ReadmeText` |

- **New manifest key:** add a promoted property to the matching `readonly` class and read it in `fromJson()` or `fromFile()` with the typed `ReadmeJsonObject` accessors. A key that is not read is rejected as unknown.
- **New Makefile key:** add it to the renderer's `assertKnownKeys()` call and read it with `requireValue()`, `optionalValue()`, `requireChoice()` or `map()`. Use `errorAtLine()` for errors.
- **New format:** subclass `ReadmeTargetRenderer`, implement `render()`, register the class in `ReadmeTargetRenderer::FORMATS`, and add a `require_once` to `build-readme.php`.
- **New special item:** extend `ReadmeSectionItem::parse()` and handle the new kind in each renderer.

After changing the generator, compare the `--output-dir` output with the previous build, and check that the About page still reads the changelog from `README.txt`.
