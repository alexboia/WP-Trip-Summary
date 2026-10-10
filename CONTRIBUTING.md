## Report a bug

[You can use New issue -> Bug report](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to file a new bug report.  
Please do so even if you are not sure whether or not the product should behave the way you expect it to, as this may hide, at the very least, some shortages in the way the plug-in communicates what it does.

## Request a feature you would like implemented

[You can use New Issue -> Feature request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) to propose a new feature for this plug-in.  
If it is an already existing feature, go ahead anyway and propose a way to enhance it.

## Help with translating the plug-in

There two main types of assets that need translation:

   - the user interface strings (labels, system messages etc.), which are provided and stored in classic `.mo` files, with a source `.pot` template file that can be used as base for translation (in this case, the `lib/abp01-trip-summary.pot` file);
   - the help content, which is provided as HTML files, one per each language, each with a source markdown (`.md`) file.

### Translating user interface strings

The way you translate user interface strings is by installing [POEdit](https://poedit.net/), loading the provide .pot file and, on that basis, provide the translation for the language you want.
[See this guide for an example](https://wplang.org/translate-theme-plugin/).  
The result is a .mo file and a .po file, which you then commit and create a pull request.

When done, [open a new issue and request](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) that it be processed and added as a plug-in translation.

### Translating help files

The help content is written, for each language, in a markdown (`.md`) file called index.md, located, for each language, in the `help/src/{language_code}` folder (ex. `help/src/ro_RO/index.md`). 
This content is then be built as HTML files when the plug-in installation kit is created, but it can also be built on demand.  

## Actively contribute to the code base

You can contribute to the code base itself either by:
   - writing code to fix a specific bug or implement a feature; 
   - or by proposing refactoring of existing code ([New issue -> Propose refactoring](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose)).

### Setting up the development environment

You need everything required to run the plug-in itself ([see the requirements](README.md#wpts-requirements)), plus:

1. the xdebug extension (recommended);
2. the Composer development dependencies, including PHPUnit, installed with `composer install`;
3. wp-cli 2.x, available in your `$PATH`, to initialize the test environment if needed;
4. phpcompatinfo 5.x, available in your `$PATH`, to generate the compatibility information files;
5. on Windows, Cygwin or the Windows Subsystem for Linux, to set up the development environment and run the test and build scripts, with:
   - the `wget`, `curl` and `zip` commands;
   - the gettext libraries;
   - the PHP CLI with the extensions listed in the plug-in requirements;
   - the MySQL command line client;
   - the Subversion command line client.

### Running tests

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

The sets are PHPUnit suites defined in `phpunit.xml`. When adding a test file, include it in the corresponding thematic suite. The `default` suite discovers every `tests/test-*.php` file automatically and remains the default for direct PHPUnit and `composer test` runs.

#### Running tests from PowerShell

The test bootstrap uses `WP_TESTS_DIR` when set, otherwise `/tmp/wordpress-tests-lib` when present, then the WordPress test library installed by Composer in `vendor/wp-phpunit/wp-phpunit`. This library is separate from the WordPress core installation needed to run the tests.

Set `WP_CORE_DIR` to the WordPress core installation to use for testing. Use a WordPress version matching the `wp-phpunit/wp-phpunit` version in `composer.lock`. Without this variable, the test configuration looks for `wordpress` under PHP's temporary directory.

Configure the test database connection in `tests/wp-tests-config.php` before running the suite. Use a dedicated test database: WordPress recreates its test tables during setup. The bootstrap loads this configuration directly.

From the plug-in directory:

```powershell
composer install
$env:WP_CORE_DIR = 'D:\path\to\wordpress'
.\vendor\bin\phpunit -c .\phpunit.xml
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
