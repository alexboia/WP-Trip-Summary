---
name: wpts-refactor-code-namespaces
description: Migrate PHP classes in the WP Trip Summary plugin from legacy Abp01_* names to the WpTripSummary namespace while preserving runtime compatibility, autoloading, dependency injection, and static analysis. Also use on demand when the user asks to add conservative PHP type hints to one or more WPTS classes, even without a namespace migration.
---

# Refactor WPTS PHP Code

Work only inside the WP Trip Summary plugin containing `abp01-plugin-main.php` and `lib/`.
Preserve behavior and backward compatibility. Treat namespace migration and type-hint-only work as separate workflows; perform only the workflow requested by the user.

## Release lot gate

Do not modify production PHP code through this skill until a migration lot exists for the plugin's current version.

1. Read the current version from both the `Version` header in `abp01-plugin-main.php` and `ABP01_VERSION` in `abp01-plugin-header.php`. Stop and report the mismatch if they differ.
2. Load `lots/lot-<PluginVersion>.json` and validate its structure against `lots/lot.schema.json`. Stop and ask for a lot to be defined if the file is missing or invalid.
3. Load `class-list.json` as the single source of truth for each class's target name and migration status. A lot intentionally does not duplicate those fields.
4. Work only on classes listed in the current lot and present in `class-list.json`. If the requested class is absent from either file, stop and ask whether the planning data should be amended; do not silently move work between releases.
5. Before modifying a listed class, set its status in `class-list.json` to `in_progress` and set the lot status to `working`. Set the class status to `complete` only after all required validation passes.
6. Set the lot status to `complete` only when every class referenced by that lot has status `complete` in `class-list.json`. Do not start a future-version lot while its version is not current.

This gate applies both to namespace migrations and to type-hint-only work performed through this skill.

### Expanding an existing lot

Treat adding classes to an existing release lot as a scope change, not as a routine JSON edit.

1. Always require an explicit motivation from the developer before changing the lot. If the request does not already contain one, stop and ask for it.
2. Test and challenge the motivation against the release objective, actual class dependencies, implementation and testing effort, regression risk, and whether the work can remain in the next planned lot.
3. Inspect the relevant code and planning metadata where useful; do not accept urgency, convenience, or fear of losing context as sufficient justification without examining the tradeoffs.
4. Explicitly present the disadvantages of expansion, including the larger review surface, increased regression risk, additional testing, possible release delay, harder rollback or bisection, and disruption of the planned migration order.
5. Recommend accepting, rejecting, or narrowing the expansion. Do not edit the lot until the developer has seen this assessment and explicitly confirms the scope change.

If the developer includes a motivation in the initial request, treat it as the required motivation and proceed directly to testing and challenging it rather than asking the same question again.

## Naming convention

Convert the legacy PEAR-style name into namespace segments while preserving the final symbol name:

- `Abp01_Env` -> `WpTripSummary\Env`
- `Abp01_Installer_Service_CreateDbTable` -> `WpTripSummary\Installer\Service\CreateDbTable`
- `Abp01_Route_Manager` -> `WpTripSummary\Route\Manager`

Apply the same mapping to classes, interfaces, abstract classes, and traits. Keep the existing directory structure and casing unchanged. The custom autoloader already maps namespace segments to the existing paths; do not alter the autoloading strategy unless explicitly requested.

## Namespace migration workflow

Migrate one target symbol at a time unless the user explicitly requests a batch.

1. Check the worktree and preserve all existing user changes.
2. Locate the definition and inspect its inheritance, implemented interfaces, traits, constructor, collaborators, and tests.
3. Search the entire plugin for the legacy identifier in code, PHPDoc, strings, callbacks, dependency-injection maps, tests, mocks, `class_exists()`, `is_a()`, reflection, and serialized or persisted data.
4. Confirm that the fully qualified name derived from the legacy identifier follows the naming convention above.
5. Run the structural conversion helper in dry-run mode and inspect its output:

   ```text
   php .agents/skills/wpts-refactor-code-namespaces/scripts/change-target-class-identifier.php <file> <legacy-name> --dry-run
   ```

6. If the preview is correct, run the same command without `--dry-run`. It derives the new fully qualified name from the `Abp01_` identifier, renames the declared symbol and its lexical self-references, adds `declare(strict_types=1);`, and wraps the file in the bracketed target namespace. When the source has no top-level imports, it also adds preliminary imports for class-like dependencies detected in `new`, `extends`, `implements`, `instanceof`, `catch`, and static-access expressions. Treat these imports as a starting point and inspect them; the helper intentionally does not perform full semantic analysis or update aliases, shims, and external references.
7. Update references required for the migrated definition to work. Do not blindly replace string occurrences; classify strings used by hooks, configuration, persistence, reflection, or external extension points.
8. Immediately add the runtime compatibility mapping to `abp01_init_legacy_class_aliases()` in `abp01-plugin-functions.php`:

   ```php
   abp01_legacy_class_alias(\WpTripSummary\NewName::class, 'Abp01_OldName');
   ```

9. Add the corresponding deprecated shim to `abp01-legacy-shim.php`, following the existing examples. Use the correct symbol kind; do not model an interface or trait as a class.
10. If the symbol appears in `Abp01_PluginModules_PluginModuleHost::_getDefaultInjectableServiceFactories()`, retain a factory under the legacy identifier and add an equivalent factory under the new identifier.
11. Apply conservative type hints to the target symbol as described below. Do not broaden this into unrelated cleanup.
12. Validate the migration and update `class-list.json` only after the checks complete.

## Type-hint-only workflow

Use this workflow when the user asks only for type hints. Do not add a namespace, strict-types directive, aliases, shims, or dependency-injection entries unless separately requested.

1. Inspect every caller, override, implementation, parent declaration, and returned value relevant to the methods being typed.
2. Add native parameter, property, and return types when the runtime contract is clear.
3. Treat an unambiguous `@var` annotation on a `private` property as sufficient evidence for immediate conversion to a native property type. For example, convert an `@var Abp01_AdminAjaxAction` property to `private Abp01_AdminAjaxAction $property;`. Reflect `null` or other declared alternatives in the native type, and verify that initialization is compatible. Remove a PHPDoc block that becomes entirely redundant; preserve any useful description or type detail that native syntax cannot express.
4. Type class constants from their declared values when the value has an unambiguous type and the project's minimum supported PHP version is 8.3 or newer. Before adding the type, inspect declarations of the same constant throughout parent classes, child classes, and implemented or extended interfaces. Emit a clear warning when any declaration in that inheritance chain has a value of a different type, and do not introduce an incompatible native type until the conflict is resolved. When the minimum PHP version is below 8.3, report that typed class constants are unavailable and leave them untyped unless the user explicitly authorizes raising the compatibility baseline.
5. Use nullable or union types when the implementation genuinely supports multiple values. Use `mixed` only when narrowing would misrepresent the contract.
6. Use PHPDoc for array shapes, generic collections, callable signatures, or details that native PHP types cannot express.
7. Account for WordPress filters, options, request values, and extension points, which may supply loosely typed values.
8. Do not change method behavior while adding types. Flag unrelated bugs instead of silently fixing them.
9. Avoid widening the diff into dependent classes. Type a dependent declaration only when required for compatibility or explicitly requested.

## Conservative typing rules

- Do not infer a type from a parameter name alone.
- Preserve compatible signatures across inheritance and interfaces.
- Initialize typed properties, or make them nullable when they may be read before assignment.
- Before enabling strict types, inspect scalar calls made from the affected file; WordPress commonly supplies numeric and boolean values as strings.
- Preserve `false`, `null`, `WP_Error`, and other sentinel values present in the real contract.
- Infer a constant type only from a value whose resulting type is statically clear. Inspect expressions, inherited constants, and filtered or environment-derived values instead of guessing.
- Prefer a precise PHPDoc contract over an unsafe native declaration.

## Migration inventory

Candidate symbols begin with `Abp01_`, live under `lib/`, and exclude `lib/3rdParty/`.

Maintain `class-list.json` in this skill directory. Generate it before the first namespace migration if it does not exist. Order entries by a proposed dependency-safe migration sequence: contracts and base types before implementations, then consumers. Do not reorder completed work unnecessarily.

Generate or refresh it from the plugin root with:

```text
php .agents/skills/wpts-refactor-code-namespaces/scripts/build-class-list.php .
```

The generator preserves completed entries and existing issue notes. Review its proposed order and issue flags before choosing the next migration; they are an aid, not a substitute for dependency inspection.

Use this shape:

```json
{
  "Abp01_ClassName": {
    "newClass": "WpTripSummary\\ClassName",
    "filePath": "lib/ClassName.php",
    "status": "pending",
    "potentialIssues": []
  }
}
```

Allowed statuses are `pending`, `in_progress`, `blocked`, and `complete`. Keep completed entries for migration history. Record issues such as public extension points, persisted names, inheritance ordering, external-library collisions, or dependency-injection participation.

## Required validation

For every modified symbol:

1. Run `php -l` on every touched PHP file.
2. Run `git diff --check` scoped to touched files and inspect the diff for unrelated changes.
3. Run the narrowest relevant tests, followed by the broader suite when practical.
4. For a namespace migration, verify both the new symbol and legacy alias with `class_exists()`, `interface_exists()`, or `trait_exists()` as appropriate.
5. Search again for the old identifier and explain which remaining references are intentional.
6. Verify both dependency-injection identifiers when applicable.

Never report a test as passed when it did not start. Report infrastructure failures separately from code failures.

After changing `change-target-class-identifier.php`, run its standalone regression test:

```text
php .agents/skills/wpts-refactor-code-namespaces/tests/change-target-class-identifier-test.php
```
