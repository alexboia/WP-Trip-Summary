# WPTS inline hook documentation

Use WordPress-style inline hook doc-comments immediately before the statement that dispatches the hook.

## Action

```php
/**
 * Fires after a route log entry has been saved.
 *
 * @since 0.3.3
 * @category RouteLog
 *
 * @param int $postId The post identifier associated with the route log.
 */
do_action('abp01_example_action', $postId);
```

## Filter

```php
/**
 * Filters whether the route log is displayed in the frontend viewer.
 *
 * @since 0.3.3
 * @category RouteLog
 *
 * @param bool $enabled Whether the route log should be displayed.
 * @param int  $postId  The post identifier being displayed.
 */
$enabled = apply_filters('abp01_example_filter', $enabled, $postId);
```

Do not add `@return` to an inline hook doc-comment. The first parameter of a filter is its filtered value. Align parameter columns only when doing so matches the surrounding project style.

## Category

Every hook doc-comment has exactly one `@category` tag. The category identifies the functional subsystem or extension surface, not the dispatch mechanism and not the action/filter kind.

- Reuse an established project category with identical spelling and casing whenever one applies.
- Use the same category at every dispatch site of the same hook.
- Categorize a class-wrapped hook by the logical hook contract, not by the wrapper class.
- If no existing category fits, record a proposed category and its evidence in the draft, then obtain developer confirmation before inserting it.

## PSR-4 instability

Use the project-owned instability tag when an action or filter exposes a migration-sensitive plug-in class contract, including a concrete class instance or a scalar value that semantically contains a class identifier. Apply the rule identically to direct and class-wrapped dispatchers. Place the tag after `@category` and before `@param` tags, using the exact text below:

```php
/**
 * Filters the class used as the frontend theme decorator.
 *
 * @since 0.3.2
 * @category Front-end Viewer
 * @unstable Susceptible to breaking changes due to PSR-4 migration
 *
 * @param string $frontendThemeClass The fully qualified theme decorator class name.
 */
$frontendThemeClass = apply_filters(
    'abp01_get_frotend_theme_class',
    'Abp01_FrontendTheme_Decorator'
);
```

Do not add the tag solely because the enclosing implementation class is being migrated. A documented action argument, filter argument, or filtered value must expose a class-dependent contract that can break. Document the current runtime contract, and remove the tag only after the public contract is stable and the migration-sensitive compatibility decision has been resolved.

## Class-wrapped dispatcher

A registered class wrapper is documented exactly like its direct WordPress equivalent. Place the comment immediately before the `new` expression that performs the logical dispatch:

```php
/**
 * Fires before the plug-in installation begins.
 *
 * @since 0.3.0
 * @category Installer
 * @unstable Susceptible to breaking changes due to PSR-4 migration
 *
 * @param Abp01_Installer_Context $context The current installation context.
 */
new Abp01_Installer_Service_RunInstallHook(
    'abp01_installer_do_pre_install',
    $context
);
```

The wrapper's fully qualified class name belongs in the occurrence metadata as `dispatcher`; it does not require a different documentation format. Do not also document the wrapper's internal `do_action()` when it merely forwards the registered logical hook.

## Draft record for an undocumented hook

Before inserting a new comment, capture at least:

```text
hookName: abp01_example_filter
kind: filter
location: relative/path.php:123
summary: Filters ...
category: RouteLog
categoryStatus: existing | proposed
unstableTagRequired: true | false
unstableReason: Plug-in class identifier changes during PSR-4 migration, or null when stable.
parameters:
  - position: 1
    expression: $enabled
    proposedType: bool
    description: Whether ...
    evidence: Return type and strict comparison in EnclosingClass::method().
    confidence: high
typeStability: stable | transitional | unknown
openQuestions: []
```

For a plugin-owned concrete class, set `typeStability` to `transitional` while its namespace migration or public contract remains unsettled. Include both the current runtime type and planned target in evidence, but do not automatically publish both names as a union: a legacy alias and its namespaced class describe one runtime class, not two alternative value types.

## Dynamic dispatchers

When the extractor cannot resolve the first argument, trace its assignments and callers. Produce one draft per concrete hook name, while retaining the dynamic dispatcher location as evidence. If the possible names cannot be bounded deterministically, record an open question instead of inventing an exhaustive list.
