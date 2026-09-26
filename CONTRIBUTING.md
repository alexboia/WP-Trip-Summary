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
