---
name: wpts-document-hooks
description: Inventory, assess, and document WordPress action and filter dispatches owned by the WP Trip Summary plugin. Use when auditing `abp01_*` hooks, finding missing inline hook doc-comments, drafting their contracts from code context, or maintaining hook documentation without renaming the hooks.
---

# Document WPTS Hooks

Work inside the WP Trip Summary plugin containing `abp01-plugin-main.php` and `lib/`. Hook identifiers remain permanently prefixed with `abp01_`; never rename a hook merely because PHP classes are moving to namespaces.

## Inventory first

From the plug-in root, refresh the deterministic inventory before auditing, editing, or generating hook documentation:

```text
bash bin/update-hooks-inventory.sh
```

The canonical inventory is always `./hook-docs/hooks-inventory.json`. Read that file after the command completes; do not use standard output or an alternate report path as the input to later documentation work. If invoking the extractor directly instead of using the wrapper script, preserve the same output contract:

```text
php .agents/skills/wpts-document-hooks/scripts/extract-hooks.php . --pretty --output=./hook-docs/hooks-inventory.json
```

The extractor scans production PHP entry points, `lib/` excluding `lib/3rdParty/`, and `views/`. It reports resolved documented hooks, resolved undocumented hooks, and unresolved dynamic dispatchers separately. Treat the canonical inventory as a syntactic inventory, not as proof that an existing comment is correct. Do not edit the generated JSON manually.

Inspect every unresolved dispatcher manually. Trace constructor arguments, property assignments, constants, and callers until each concrete `abp01_*` hook name is found or explicitly record why it cannot be resolved. Do not silently omit dynamic hooks.

## Treat dispatchers uniformly

After detection, treat a direct WordPress dispatcher and a registered class-wrapped dispatcher as the same kind of public hook occurrence. Apply the same inventory, contract assessment, documentation, categorization, duplicate-consistency, and validation rules regardless of whether the dispatch statement is `do_action(...)`, `apply_filters(...)`, or `new <registered-wrapper>(...)`.

- Obtain the hook kind for a class-wrapped dispatcher from `WPTS_HOOK_CLASS_WRAPPED_DISPATCHERS`; it must resolve to `action` or `filter`, never remain unknown merely because the dispatch is wrapped.
- Interpret `dispatcher` as the mechanism that exposes the logical hook. A wrapper's fully qualified class name identifies that mechanism but does not change the hook's public documentation semantics.
- Attach the inline hook doc-comment immediately before the logical dispatch statement. For a class-wrapped occurrence, this is the `new` expression that receives the hook name and arguments.
- Do not inventory or document the wrapper's internal `do_action()` or `apply_filters()` call as a second occurrence when it only implements registered class-wrapped dispatches.
- Resolve and report static, dynamic, documented, undocumented, and duplicate hook occurrences identically for both mechanisms.

## Assess existing documentation

For each documented occurrence, compare the doc-comment with the invocation:

- Verify action versus filter terminology, argument count and order, pass-by-reference behavior, and the value returned by a filter.
- Require one `@category` tag and verify that its value describes the hook's functional domain rather than its dispatcher mechanism or whether it is an action or filter.
- Trace each argument to its origin and inspect callbacks registered inside the plugin when they provide additional contract evidence.
- Report duplicate dispatch sites for the same name and require their public contracts and `@category` values to agree.
- Classify a doc-comment without `@category` as incomplete documentation, separately from a missing doc-comment. Do not overwrite a useful comment wholesale when adding the tag or making another focused correction is sufficient.

## Draft missing documentation

Create an initial draft for every occurrence reported as undocumented. Read [references/hook-doc-format.md](references/hook-doc-format.md) before drafting or inserting inline documentation.

Infer the draft from the enclosing method or template, the expressions passed to the dispatcher, value construction, downstream use, and known callbacks. For every inferred parameter, record the evidence and confidence. Distinguish facts proven by code from assumptions that require developer confirmation.

Use `Fires` for actions and `Filters` for filters. Describe when the hook runs and what extension decision it exposes, not merely a humanized version of its identifier. Document arguments in invocation order. For filters, document the filtered value first.

Add exactly one `@category` tag to every new or updated hook doc-comment and include the proposed category in every draft record. Reuse the exact spelling and casing of an established project category when the hook belongs to it. Base a class-wrapped hook's category on the logical subsystem it exposes, not on the wrapper class. When no existing category clearly applies, infer a concise functional category from code context, mark it as proposed, and request developer confirmation before inserting it; do not invent a project-wide taxonomy during an unrelated documentation task.

Do not insert drafts into production files unless the user asks to add or update inline hook documentation. An inventory or audit request is read-only apart from an explicitly requested report file.

## Generate Markdown documentation

Use `./hook-docs/hooks-inventory.json` as the source inventory for generated hook documentation. Write all generated Markdown files under `./hook-docs/`; do not place generated documentation in the skill directory or alongside production PHP files. When no output filename was requested, choose a descriptive `.md` filename within that directory and report it explicitly.

Refresh the inventory before generating or regenerating Markdown. Keep the generated documentation consistent with the inventory's hook kind, arguments, locations, documentation status, dispatcher, and category. A class-wrapped hook is presented like the equivalent direct action or filter; the wrapper mechanism may be included as provenance, but it must not create a separate public hook entry.

## Transitional class types

Treat a hook argument whose type is a plugin-owned concrete class as a potentially unstable public contract while namespace migration is in progress.

- Check `.agents/skills/wpts-refactor-code-namespaces/class-list.json` when it exists and record the legacy name, target name, and migration status in the draft evidence.
- Prefer an existing stable interface or value shape only when the runtime contract actually guarantees it. Do not invent an abstraction to make the documentation look stable.
- Do not publish a provisional fully qualified class name as a permanent contract. Flag it as `transitional` and ask for developer confirmation before inserting that type into an inline public doc-comment.
- Account for runtime legacy aliases, but still flag code that depends on textual class names through `get_class()`, reflection, serialization, or persisted values.
- Stable scalar types, array shapes, WordPress types, and already settled interfaces may be documented immediately when supported by evidence.

## Validation

After editing inline hook documentation:

1. Run `php -l` on every touched PHP file.
2. Run `bash bin/update-hooks-inventory.sh`, then inspect `./hook-docs/hooks-inventory.json` and confirm the intended occurrence moved from `undocumentedHooks` to `documentedHooks` without changing hook resolution or argument expressions. Inspect the returned `docComment` and confirm that it contains exactly one `@category` tag.
3. Run `git diff --check` on the touched files and inspect for runtime changes. A documentation task must not alter executable behavior unless separately requested.
4. Run focused tests when a comment edit required moving code or changing syntax; otherwise report syntax and extractor validation separately from runtime tests.

After changing the extractor, run:

```text
php .agents/skills/wpts-document-hooks/tests/extract-hooks-test.php
```
