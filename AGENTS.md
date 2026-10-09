# WP Trip Summary: agent instructions

## Scope and starting point

This file applies to the `abp01-travel-tech-box` plugin and its descendants. The product is **WP Trip Summary**: a WordPress plugin for structured trip information, GPS tracks, maps and route logs.

- Treat this directory as the working and Git root. It can be nested inside a larger WordPress installation; run commands here or use `git -C <plugin-root>`.
- Inspect `git status --short`, the relevant implementation and neighboring tests before editing. Preserve existing user changes.
- Keep changes within the requested feature or subsystem. Do not modify WordPress core, sibling plugins or site configuration as incidental cleanup.
- Paths and commands below are relative to this directory. Read [README.md](README.md) for product context and environment setup, and [CONTRIBUTING.md](CONTRIBUTING.md) for test and contribution workflows.

## Use the local skills

Read the applicable `SKILL.md` before performing its workflow. These skills own the detailed procedures; this file provides repository orientation and shared boundaries. Follow more specific instructions in a descendant `AGENTS.md` when present, and honor explicit user instructions.

| Task | Skill | Key boundary |
| --- | --- | --- |
| Create, edit or review plugin PHP, JavaScript, TypeScript, CSS, templates or tests | [wpts-coding-conventions](.agents/skills/wpts-coding-conventions/SKILL.md) | Use its conventions and relevant examples; maintain source license headers with its utility. |
| Inventory, audit or maintain action/filter documentation | [wpts-document-hooks](.agents/skills/wpts-document-hooks/SKILL.md) | Refresh and read the canonical inventory; preserve public hook contracts and distinguish an audit from an inline documentation edit. |
| Migrate legacy class names, or add conservative PHP types on request | [wpts-refactor-code-namespaces](.agents/skills/wpts-refactor-code-namespaces/SKILL.md) | Follow the current release lot gate, planning metadata and compatibility checks, including for type-hint-only work. |
| Plan or produce product marketing materials | [wpts-marketing](.agents/skills/wpts-marketing/SKILL.md) | Ground user/developer messaging in the target release; use the existing README generator and asset paths, and route video production through the media skills. |

Combine skills when a task spans their responsibilities. Ordinary code edits use the coding skill without automatically starting a namespace migration, a typing pass or a repository-wide hook audit. Keep detailed skill rules in their existing files instead of maintaining a second copy here.

## Repository map

| Location | Responsibility |
| --- | --- |
| `abp01-plugin-main.php` | WordPress metadata, entry point and bootstrap |
| `abp01-plugin-header.php`, `abp01-plugin-functions.php` | Plugin constants, global helpers, autoloader initialization and runtime legacy aliases |
| `lib/Autoloader.php`, `abp01-legacy-shim.php` | Custom class loading and deprecated symbol shims |
| `lib/pluginModules/` | WordPress integration, hook registration, module activation and dependency injection |
| `lib/route/` | Route information, logs, track geometry, parsing, validation and processing |
| `lib/installer/`, `lib/settings/`, `lib/maintenanceTool/` | Installation lifecycle, settings and maintenance |
| `lib/adminAjaxAction/`, `lib/nonceProvider/`, `lib/validation/`, `lib/validate/` | Request handling, nonces and validation infrastructure |
| `lib/viewer/`, `lib/frontendTheme/`, `lib/viewModel/`, `views/` | Frontend data, themes, view models and PHP templates |
| `lib/includes/`, `media/js/`, `media/css/` | Asset registration, browser code and styles; admin assets live in `admin/` subdirectories |
| `abp01-plugin-leaflet-plugins-wrapper.php` | HTTP wrapper for bundled Leaflet plugin scripts |
| `lang/`, `data/dev/setup/lookup-definitions.xml` | Translation catalogs and source lookup definitions |
| `tests/`, `tests/lib/` | WordPress PHPUnit tests, helpers, doubles and fixtures |
| `bin/`, `hook-docs/`, `.agents/skills/` | Tooling, generated hook inventory/documentation and agent workflows |

## Implementation boundaries

- Preserve public hooks, option/meta keys, AJAX action names, serialized identifiers, asset handles and template contracts unless their change is part of the task. Search definitions and consumers before changing a shared contract.
- Legacy `Abp01_*` and namespaced `WpTripSummary` symbols coexist. `lib/Autoloader.php` supports both with the existing directory casing. Do not replace it with Composer PSR-4 loading or rename directories during an unrelated edit.
- Runtime aliases in `abp01-plugin-functions.php` and deprecated declarations in `abp01-legacy-shim.php` serve different purposes. Follow the namespace skill when changing either as part of a migration, including dependency-injection identifiers and release-lot status updates.
- Existing hook names retain the `abp01_` prefix. The coding skill's `wpts_`/`WPTS_` policy for new global functions/constants does not rename hooks or existing symbols.
- Reuse the surrounding module, service and view-model patterns. Preserve bootstrap guards, local typing choices and line endings; avoid broad formatting changes.
- Preserve authorization, nonce validation, input validation and contextual output escaping when modifying request handlers or views. Translate user-facing strings with `abp01-trip-summary`.
- Edit authored TypeScript when a matching `.ts` file exists, then regenerate its JavaScript using the established compiler setup. Companion `.d.ts` files may be handwritten contracts. For scripts without TypeScript sources, edit the JavaScript directly.
- Keep dependencies and bundled code outside ordinary feature edits: `vendor/`, all `node_modules/`, `lib/3rdParty/`, `media/js/3rdParty/` and bundled Bootstrap. Do not hand-edit generated JavaScript, lockfiles or build output to work around a source problem.
- Use `composer install` with the committed lockfile when dependencies are missing. Dependency updates and compatibility-baseline changes must be intentional parts of the task.

## Environment and compatibility

Read the current plugin metadata, runtime requirement checks and `composer.lock` together before choosing PHP syntax or changing compatibility claims. The declared minimum and the effective requirements are not necessarily aligned: the plugin header currently advertises PHP 8.0.0, while locked Monolog requires PHP 8.1 or newer. The license utility and library-path checker use PHP 8.2 features. Do not infer the production baseline solely from the PHP executable installed locally or from a tooling requirement.

The legacy `.circleci/config.yml` matrix and `.phpcs.xml.dist`, if present locally, contain outdated versions or template settings. Do not treat them as an authoritative compatibility target or use them to reformat the plugin. Verify their relevance before relying on them.

The root and `media/js/` npm manifests contain dependency declarations, but no build, lint or test scripts and no pinned TypeScript compiler. Do not invent `npm test` or `npm run build` commands. Use [media/js/tsconfig.json](media/js/tsconfig.json) with the available project compiler, inspect emitted changes and report if the compiler setup is unavailable.

## Validation

Choose checks that exercise the changed behavior. All commands below run from the plugin root; replace illustrative filenames with actual touched files.

### PHP changes

```text
php -l lib/ChangedFile.php
git diff --check
```

Run syntax checks on every touched PHP file. Apply and verify the license utility on authored source files as required by the coding skill; Markdown, configuration, utility scripts and generated assets do not require source-header updates.

For changes to library names, paths or declarations, also run:

```text
php bin/tools/check-lib-paths.php
```

This is a structural check, not a substitute for runtime loading or migration validation.

### WordPress PHPUnit tests

Tests use [phpunit.xml](phpunit.xml) and [tests/bootstrap.php](tests/bootstrap.php). They require Composer development dependencies, a matching WordPress core installation and a dedicated test database configured through `tests/wp-tests-config.php`. The bootstrap installs/uninstalls plugin data and WordPress recreates test tables; never point it at the site's working database.

`WP_TESTS_DIR` selects the WordPress test library. Otherwise the bootstrap tries `/tmp/wordpress-tests-lib`, then the Composer-provided library through `WP_PHPUNIT__DIR`. `WP_CORE_DIR` selects WordPress core; its default is `wordpress` under PHP's temporary directory. Match the core version to `wp-phpunit/wp-phpunit` in `composer.lock`. Do not assume the surrounding WordPress installation is the test fixture.

Once the test environment is configured, direct PHP invocation works from PowerShell as well as Bash:

```text
php vendor/bin/phpunit -c phpunit.xml --testsuite routes
php vendor/bin/phpunit -c phpunit.xml --filter RouteTrackPointTests
php vendor/bin/phpunit -c phpunit.xml
```

The Bash wrapper offers named sets and forwards PHPUnit options:

```bash
bash bin/run-tests.sh --set=routes
bash bin/run-tests.sh --set=documents --filter='GpxDocumentParserTests'
bash bin/run-tests.sh
```

Available PHPUnit sets are `core`, `auth`, `validation`, `routes`, `documents`, `installer`, `modules`, `ui`, `logging`, `io` and `sec`; `all`/`default` select the default suite. Add a new `tests/test-*.php` file to its thematic suite in `phpunit.xml`; the default suite discovers it automatically. Reuse existing test helpers and restore altered hooks, globals and data.

Start with the narrowest relevant tests and broaden when shared behavior is affected or a skill requires it. Pure Markdown changes need content, path and diff checks rather than a database-backed test run.

### Specialized checks

- **Leaflet wrapper:** follow the HTTP integration prerequisites in `CONTRIBUTING.md`; invoke `php bin/tools/test-leaflet-wrapper.php <test-origin>` against the intended test installation. `--mode=load` covers a host without rewrite support; rewrite tests require the server rules. This runner is separate from PHPUnit.
- **Hooks:** use the hook skill to refresh `hook-docs/hooks-inventory.json` with `bash bin/update-hooks-inventory.sh` or its documented PHP equivalent. Do not hand-edit the generated inventory. Generated hook Markdown belongs under `hook-docs/`.
- **Skill utilities:** when changing a utility, run the standalone regression runners named in its `SKILL.md`. They have different prerequisites from the WordPress suite.
- **Browser behavior:** for frontend/admin changes, check the affected editor, viewer or settings flow in the available development installation, including the browser console. Report when browser verification was unavailable.

## Build and completion

`bin/build.sh` packages a release into `build/output/`, checks library paths and clears its build output/temp directories. Run it from the plugin root only when packaging is relevant and after inspecting its inputs and output paths. It is not a TypeScript build or a substitute for tests. `bin/install-dev.sh` installs tools and can scaffold/configure test infrastructure; it is not a routine validation command.

Before finishing, inspect the diff and Git status, verify that only intended files changed, and check any affected generated artifacts against their sources. Do not bump versions, edit migration lots, regenerate unrelated inventories or publish a release as incidental cleanup.

Report what changed, which checks actually ran, their results and any remaining limitations. Distinguish failed assertions from missing tooling or test infrastructure; a test that did not start did not pass.
