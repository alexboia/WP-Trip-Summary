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
