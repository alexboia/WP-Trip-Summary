---
name: wpts-coding-conventions
description: Apply WP Trip Summary conventions when creating, modifying or reviewing this plugin's PHP, JavaScript, TypeScript, CSS, templates and tests.
---

# WP Trip Summary coding conventions

Apply these conventions to code owned by this plugin. The rules summarize recurring patterns in the current source; the license, tabs-only indentation and new global prefixes retain the policies already established by this skill.

Read the relevant sections of [references/examples.md](references/examples.md) when implementing or reviewing a construct. Examples include source pointers and language-specific details. Repository paths below are relative to the plugin root; reference links are relative to this skill.

## Scope and existing code

- Inspect nearby code before editing. Preserve public names, hook contracts and data keys unless the task includes changing them.
- The codebase contains both legacy `Abp01_*` classes and migrated `WpTripSummary` classes, typed and untyped PHP, and some older space-indented files. Apply the defaults below to new code without reformatting unrelated code or initiating a namespace/type migration.
- Exclude dependencies and generated files when inferring style: `vendor/`, `node_modules/`, `lib/3rdParty/`, `media/js/3rdParty/`, bundled Bootstrap and generated JavaScript beside TypeScript sources.

## Plugin structure

| Location | Responsibility |
| --- | --- |
| `abp01-plugin-main.php` | WordPress entry point and plugin bootstrap |
| `abp01-plugin-header.php` | Global plugin constants |
| `abp01-plugin-functions.php` | Global plugin helper functions |
| `lib/` | Classes and interfaces, grouped by subsystem |
| `lib/pluginModules/` | WordPress integration, hook registration and feature modules |
| `views/`, `views/helpers/` | PHP templates and presentation helpers |
| `media/js/`, `media/css/` | Browser code and styles; admin code in their `admin/` directories |
| `tests/test-*.php`, `tests/lib/` | Test cases, reusable helpers, test doubles and fixtures |
| `data/dev/setup/lookup-definitions.xml` | Lookup seed definitions and translations |

Follow the existing subsystem placement and filename pattern. PHP class filenames retain the final class-name segment, while subsystem directories use lower camel case, such as `lib/installer/step/Activate.php`.

## License header and PHP file layout

- Use [license-header-utility.php](scripts/license-header-utility.php) for license maintenance on plugin and test source files (PHP/PHTML, JavaScript, TypeScript and CSS). It generates missing headers and replaces recognized existing headers with the complete [license template](references/.license-header), using the target year.
- For a new source file, create its initial contents first (including `<?php` for PHP code), then run the utility with `--update`. For an existing source file changed by the task, run `--update` as part of the edit, even if its copyright year is already current. The default target is the current year; `--year` sets an exact year, including an earlier one. A run is a no-op only when the generated header already matches.
- Limit updates to source files in the task. Build/utility scripts, configuration, package/manifest files, dependencies and generated assets are exempt. The utility's intentionally missing/outdated license fixtures under `tests/license-header-files/` within this skill are test data; do not normalize their headers.
- For a review without edits, use `--read` or `--check`. Use `--update --dry-run` when a preview is useful. Inspect the diff after writing and use `--check` to verify the result. See [commands and options](references/examples.md#license-header-utility).
- Insert new PHP headers immediately after `<?php`. Existing headers keep their location, including after the WordPress plugin metadata comment or a strict-types directive. The recognized project license is replaced in full; code and other comments outside it remain unchanged.
- PHP library files omit the closing `?>`; templates close and reopen PHP around markup.
- Preserve the file's bootstrap guard. Runtime files commonly check `ABP01_LOADED`; the plugin header checks `ABSPATH`. Test files run through the test bootstrap and do not acquire runtime guards.
- Where strict typing is used, place `declare(strict_types=1);` after the license and before the namespace. It is not universal in legacy files; adding it can change behavior and is not a formatting edit.

See [class and file layout examples](references/examples.md#classes-and-file-layout).

## Whitespace, blocks and expressions

- Use tabs for code indentation, one per nesting level. Preserve existing line endings. Separate members and logical groups with a blank line.
- Put opening braces on the declaration or control-statement line, preceded by one space. Use braces for control-flow bodies; put `else`, `else if`, `catch` and `finally` on the preceding closing-brace line. Prefer the prevailing `else if` spelling.
- Use a space after control keywords (`if (`, `foreach (`, `catch (`), but none just inside parentheses.
- Use one space around assignment, comparison, arithmetic, logical, ternary and PHP `=>` operators. Keep unary `!` adjacent to its operand. Do not change comparison semantics merely for style.
- Keep array access and member access tight: `$items[$key]`, `$this->_env`, `self::$_instance`, `self::CONSTANT`, `object.property` in JavaScript.
- Use single-quoted PHP/JavaScript strings for ordinary literals; use double quotes or template literals when interpolation or escaping calls for them. Use lowercase PHP `true`, `false` and `null`.
- Use `array(...)` for PHP array literals, including callback pairs, and `[]` for indexing/appending. In multiline arrays, place entries on separate lines, indent nested values and align the closing parenthesis with the statement. Most arrays omit the final comma; preserve a local trailing-comma convention where already used.
- For long expressions, indent continuation lines one extra tab. Logical operators, ternary `?`/`:` and chained `->`/`.` accesses commonly start continuation lines. For PHP string concatenation, keep spaces around `.` and put the dot at the end of the preceding line; indent embedded HTML according to its nesting.

See [blocks and expressions](references/examples.md#blocks-and-expressions), [arrays and access](references/examples.md#arrays-and-access) and [string concatenation](references/examples.md#string-concatenation).

## Function and method declarations and calls

- Keep names adjacent to `(` in declarations and calls; anonymous functions use `function(...)`. Do not pad argument lists; use one space after separating commas.
- Format PHP types as `Type $argument`, defaults as `$argument = value` and return types as `): Type`. Keep nullable/union types tight: `?string`, `int|string`.
- For a wrapped declaration or call, usually keep the first argument on the opening line, indent subsequent arguments one tab and close on the last argument's line. Fully expanded calls also occur for nested or complex arguments; follow the local form.
- Public methods, parameters and local variables use lower camel case. Private/protected helpers and backing properties normally add a leading underscore: `_getLookupTableName()`, `$_lookupDataProvider`. Preserve PHP magic methods and inherited method names such as `__construct()`, `setUp()` and `tearDown()`.
- Preserve declared types and interface contracts. Use the surrounding subsystem's typing level; do not infer a mandatory type modernization from newer classes. PHPDoc records useful details such as collection element types, callback contracts and side effects.

See [declarations and calls](references/examples.md#declarations-and-calls).

## Core plugin constants and global functions

- Define global plugin constants in `abp01-plugin-header.php` and global plugin helper functions in `abp01-plugin-functions.php`, unless explicitly requested otherwise. Template helpers keep their existing placement.
- Preserve existing `ABP01_` constants and `abp01_` functions. New global constants use `WPTS_` with uppercase underscore-separated words; new global functions use `wpts_` with lowercase underscore-separated words. These new-name rules are established policy, even though legacy prefixes still dominate the source.
- Constants intended to be overridden from `wp-config.php` use `if (!defined('...'))`. Pluggable functions use `if (!function_exists('...'))`. Ordinary definitions are unconditional; do not make every symbol pluggable.

See [ordinary and pluggable definitions](references/examples.md#global-constants-and-functions).

## Class definitions

- Use PascalCase class/interface names and explicit member visibility. Put properties before the constructor, then related methods; private helpers often sit near the method using them rather than in one mandatory block at the end.
- Keep the naming scheme of the subsystem being edited. Legacy `Abp01_Installer_Step_Activate` maps to `lib/installer/step/Activate.php`; namespaced `WpTripSummary\Locale` maps to `lib/Locale.php` through the custom autoloader.
- Namespaced files use the bracketed `namespace WpTripSummary { ... }` form, with their contents indented. Put `use` imports inside that namespace, before the class. Resolve global classes with imports or a leading `\` where needed.
- Backing properties use `$_lowerCamelCase`; class constants use `UPPER_SNAKE_CASE`. Public data properties use lower camel case. Existing static factories, singleton accessors and constructor dependencies retain their own contracts; they are not requirements for every new class.

See [legacy and namespaced examples](references/examples.md#classes-and-file-layout).

## Filter hooks and action hooks

- Existing plugin hook names use lowercase `abp01_...` strings. Preserve their spelling, argument order, callback priority and accepted-argument count. The new global function/constant prefix policy does not implicitly rename hooks.
- Register callbacks using the local form, commonly `array($this, 'onEvent')` or a closure. Methods called by WordPress must be callable from outside the class.
- Filters return or assign the filtered value; actions notify listeners. Preserve any normalization of a filter result before use.
- Document exposed plugin hooks immediately above the call with their purpose, initial value or trigger, `@since`, `@category` and ordered `@param` entries. Describe result handling when applicable; use the actual version/category, not values copied from an unrelated hook.

See [hook examples](references/examples.md#filter-and-action-hooks).

## Views, JavaScript, TypeScript and CSS

- Templates use `<?php echo ...; ?>`, double-quoted HTML attributes and alternative PHP control syntax (`foreach (...): ... endforeach;`) around markup. Follow the existing `$data` view model and guard.
- Translate user-facing text with the existing `abp01-trip-summary` text domain. Follow contextual escaping (`esc_html`, `esc_attr`, `esc_url`, `esc_js` and translated variants) at output sites.
- Browser scripts commonly use the `(function($) { ... })(jQuery);` wrapper and `"use strict";`. Keep semicolons, camelCase functions/variables and `$` prefixes for jQuery collections. Preserve existing `var`/`const` usage and exposed `abp01...` names instead of rewriting the module style.
- Where a `.ts` source exists, edit it and use the existing compilation workflow for its `.js` counterpart. Preserve triple-slash references, companion `.d.ts` contracts and `WpTripSummary...` interface names; these declaration files can be handwritten source.
- CSS uses one selector per line in a grouped selector, a space before `{`, tab-indented `property: value;` declarations and a separate closing brace. Keep plugin selectors scoped with the existing `abp01-...` IDs/classes or `.abp01-bootstrap` container.

See [templates](references/examples.md#templates), [browser scripts and types](references/examples.md#javascript-and-typescript) and [CSS](references/examples.md#css).

## Tests

- Test files use `tests/test-Subject.php`, classes usually use `SubjectTests extends WP_UnitTestCase`, and methods use `test_behavior_scenario` with camelCase within each segment.
- Reuse helper traits from `tests/lib/`, fixtures from `tests/lib/data/` and existing test doubles where relevant. Helpers retain the `_camelCase` naming pattern.
- Keep setup, assertions and cleanup consistent with the neighboring suite. Restore hooks, global state and database changes introduced by a test. Data-driven tests use `@dataProvider` where already established.

See [test examples](references/examples.md#tests).

After changing the license utility, run its standalone tests from the plugin root with PHP 8.2 or newer and the tokenizer extension enabled (the utility uses a readonly class; WordPress is not required). Keep PHP's normal configuration loaded; do not use `-n`, which may disable tokenizer in this environment:

```text
php .agents/skills/wpts-coding-conventions/tests/license-header-test.php
php .agents/skills/wpts-coding-conventions/tests/license-header-utility-test.php
```

The runners exercise the supplied fixtures on temporary copies, check full-template synchronization, LF/CRLF preservation and repeat updates, lint updated PHP, and verify CLI output and exit codes. They leave the fixtures unchanged.
