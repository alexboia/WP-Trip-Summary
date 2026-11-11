<!-- Generated from hooks-inventory.json and the parsed inline hook comments. -->
# WP Trip Summary hooks

Reference for the plugin-owned WordPress actions and filters in **0.3.3**. The spelling of public hook names is preserved, including `abp01_get_frotend_theme_class`.

The [canonical inventory](hooks-inventory.json) contains **51 unique hooks**: **37 filters** and **14 actions**, with one dispatch site each. Every occurrence has a parsed description, category, version and ordered parameter documentation. There are no undocumented occurrences, unresolved dynamic dispatchers or parser warnings.

The scan covers production PHP entry points, `lib/` (excluding bundled third-party code) and `views/`. WordPress hooks that the plugin only subscribes to are outside this inventory. The four installer wrapper sites represent four public actions; their shared internal dispatcher is not counted again.

## Using the reference

Register callbacks with `add_filter()` or `add_action()` and set `accepted_args` to the number of parameters your callback receives. Parameters below follow dispatch order. For filters, the first parameter is the value to return; later parameters provide context. Action return values are ignored. An object argument may be mutated without the parameter being passed by reference.

Read each contract for its initial value and result handling: several boolean filters require a literal `true`, and empty arrays do not always disable a feature.

**18 hooks are marked migration-sensitive.** Their `@unstable` notice means their public contract exposes plugin classes, class identifiers or an interface still scheduled for namespace migration. Types below describe the current runtime contract. Hooks without that notice are not a promise that every future release will keep the same API.

Examples: [custom viewer tabs](../examples/e03-custom-frontend-viewer-tabs/README.md) and [overriding the viewer subtitle](../examples/e04-overriding-viewer-subtitle/README.md).

For updates, follow [the contribution workflow](../CONTRIBUTING.md#updating-hook-documentation) and [the hook documentation skill](../.agents/skills/wpts-document-hooks/SKILL.md). Refresh the inventory and review the contracts before regenerating this Markdown snapshot. Release packaging does not regenerate it.

## Index

**[Front-end Viewer](#front-end-viewer)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_additional_frontend_viewer_tab_content`](#abp01_additional_frontend_viewer_tab_content) | action | 0.3.0 | 3 | No |
| [`abp01_additional_frontend_viewer_tabs`](#abp01_additional_frontend_viewer_tabs) | filter | 0.3.2 | 2 | No |
| [`abp01_get_frotend_theme_class`](#abp01_get_frotend_theme_class) | filter | 0.3.2 | 1 | Yes |
| [`abp01_info_item_field_icon_mapping`](#abp01_info_item_field_icon_mapping) | filter | 0.3.3 | 1 | No |
| [`abp01_info_item_icons`](#abp01_info_item_icons) | filter | 0.3.3 | 1 | No |
| [`abp01_is_post_trip_summary_protected`](#abp01_is_post_trip_summary_protected) | filter | 0.3.3 | 2 | No |
| [`abp01_jsonld_frontend_data_enabled`](#abp01_jsonld_frontend_data_enabled) | filter | 0.3.3 | 2 | No |
| [`abp01_should_render_frontend_route_log_meta`](#abp01_should_render_frontend_route_log_meta) | filter | 0.3.2 | 2 | No |
| [`abp01_show_frontend_viewer_credits`](#abp01_show_frontend_viewer_credits) | filter | 0.3.3 | 2 | No |
| [`abp01_show_frontend_viewer_subtitle`](#abp01_show_frontend_viewer_subtitle) | filter | 0.3.3 | 2 | No |

**[Installer](#installer)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_install_tables_definitions`](#abp01_install_tables_definitions) | filter | 0.3.0 | 2 | No |
| [`abp01_installer_do_post_install`](#abp01_installer_do_post_install) | action | 0.3.0 | 1 | Yes |
| [`abp01_installer_do_post_uninstall`](#abp01_installer_do_post_uninstall) | action | 0.3.0 | 1 | Yes |
| [`abp01_installer_do_pre_install`](#abp01_installer_do_pre_install) | action | 0.3.0 | 1 | Yes |
| [`abp01_installer_do_pre_uninstall`](#abp01_installer_do_pre_uninstall) | action | 0.3.0 | 1 | Yes |
| [`abp01_uninstall_table_names`](#abp01_uninstall_table_names) | filter | 0.3.3 | 2 | No |

**[Log Management](#log-management)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_customize_monolog_logger`](#abp01_customize_monolog_logger) | action | 0.3.2 | 1 | No |
| [`abp01_get_logger_class`](#abp01_get_logger_class) | filter | 0.3.1 | 1 | Yes |
| [`abp01_get_logger_config_max_log_files`](#abp01_get_logger_config_max_log_files) | filter | 0.3.2 | 1 | No |
| [`abp01_get_logger_config_rotate_logs`](#abp01_get_logger_config_rotate_logs) | filter | 0.3.2 | 1 | No |
| [`abp01_log_file_display_tail_line_count`](#abp01_log_file_display_tail_line_count) | filter | 0.3.2 | 1 | No |
| [`abp01_max_displayable_log_file_size`](#abp01_max_displayable_log_file_size) | filter | 0.3.2 | 1 | No |

**[Lookup Types Management](#lookup-types-management)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_get_lookup_type_label`](#abp01_get_lookup_type_label) | filter | 0.3.2 | 2 | No |

**[Maintenance Tools](#maintenance-tools)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_enable_clear_all_data_maintenance_tool`](#abp01_enable_clear_all_data_maintenance_tool) | filter | 0.2.8 | 1 | No |
| [`abp01_enable_clear_cache_maintenance_tool`](#abp01_enable_clear_cache_maintenance_tool) | filter | 0.2.8 | 1 | No |
| [`abp01_enable_detect_missing_tracks_maintenance_tool`](#abp01_enable_detect_missing_tracks_maintenance_tool) | filter | 0.2.8 | 1 | No |
| [`abp01_get_additional_maintenance_tools`](#abp01_get_additional_maintenance_tools) | filter | 0.3.2 | 1 | Yes |
| [`abp01_render_maintenance_tool_result`](#abp01_render_maintenance_tool_result) | filter | 0.2.8 | 3 | Yes |

**[Plugin Modules](#plugin-modules)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_get_injectable_service_factories`](#abp01_get_injectable_service_factories) | filter | 0.2.6 | 2 | Yes |

**[REST API](#rest-api)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_add_trip_summary_to_rest_api`](#abp01_add_trip_summary_to_rest_api) | filter | 0.3.0 | 1 | No |
| [`abp01_add_trip_summary_to_rest_api_listing`](#abp01_add_trip_summary_to_rest_api_listing) | filter | 0.3.0 | 1 | No |

**[Settings - Trip Summary Map](#settings---trip-summary-map)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_default_tile_layer_id`](#abp01_default_tile_layer_id) | filter | 0.2.7 | 2 | No |
| [`abp01_predefined_tile_layers`](#abp01_predefined_tile_layers) | filter | 0.3.2 | 1 | Yes |

**[Time Management](#time-management)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_determine_date_format`](#abp01_determine_date_format) | filter | 0.3.2 | 2 | No |

**[Trip Summary Management](#trip-summary-management)**

| Hook | Kind | Since | Arguments | Migration-sensitive |
| --- | --- | --- | ---: | --- |
| [`abp01_after_trip_summary_audit_log`](#abp01_after_trip_summary_audit_log) | action | 0.3.0 | 2 | Yes |
| [`abp01_after_trip_summary_info_audit_log`](#abp01_after_trip_summary_info_audit_log) | action | 0.3.0 | 2 | Yes |
| [`abp01_after_trip_summary_track_audit_log`](#abp01_after_trip_summary_track_audit_log) | action | 0.3.0 | 2 | Yes |
| [`abp01_before_trip_summary_audit_log`](#abp01_before_trip_summary_audit_log) | action | 0.3.0 | 2 | Yes |
| [`abp01_editor_launcher_metabox_after_status`](#abp01_editor_launcher_metabox_after_status) | action | 0.3.0 | 1 | No |
| [`abp01_editor_launcher_metabox_before_status`](#abp01_editor_launcher_metabox_before_status) | action | 0.3.0 | 1 | No |
| [`abp01_formatted_route_type_listing_label`](#abp01_formatted_route_type_listing_label) | filter | 0.3.2 | 4 | No |
| [`abp01_get_track_file_downloader`](#abp01_get_track_file_downloader) | filter | 0.3.3 | 2 | Yes |
| [`abp01_trip_summary_available_for_post_types`](#abp01_trip_summary_available_for_post_types) | filter | 0.3.2 | 1 | No |
| [`abp01_trip_summary_log_after_save_entry`](#abp01_trip_summary_log_after_save_entry) | action | 0.3.0 | 3 | Yes |
| [`abp01_trip_summary_log_before_save_entry`](#abp01_trip_summary_log_before_save_entry) | action | 0.3.0 | 2 | Yes |
| [`abp01_trip_summary_log_enabled`](#abp01_trip_summary_log_enabled) | filter | 0.3.2 | 1 | No |
| [`abp01_trip_summary_log_validate`](#abp01_trip_summary_log_validate) | filter | 0.3.3 | 3 | Yes |
| [`abp01_trip_summary_route_log_default_entry_date`](#abp01_trip_summary_route_log_default_entry_date) | filter | 0.3.0 | 2 | No |
| [`abp01_trip_summary_route_log_default_entry_rider`](#abp01_trip_summary_route_log_default_entry_rider) | filter | 0.3.0 | 2 | No |
| [`abp01_trip_summary_route_log_default_entry_vehicle`](#abp01_trip_summary_route_log_default_entry_vehicle) | filter | 0.3.0 | 2 | No |
| [`abp01_unformatted_route_type_label`](#abp01_unformatted_route_type_label) | filter | 0.3.2 | 3 | No |

## Front-end Viewer

### `abp01_additional_frontend_viewer_tab_content`

**Action · Since 0.3.0 · 3 callback argument(s)**

Fires when the content container for an additional front-end viewer tab is rendered. The action fires once for
every tab registered through the abp01_additional_frontend_viewer_tabs filter. Callbacks should inspect the
tab identifier and output safe HTML only for the tabs they handle. The output is rendered directly inside the
corresponding tab content container.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$tabId` | `string` | The identifier of the additional tab being rendered. |
| 2 | `$tabInfo` | `array<string, mixed>` | The registered tab information, including its label and optional icon. |
| 3 | `$data` | `stdClass` | The complete front-end viewer data for the current post. |

**Dispatch arguments:** `$tabId`, `$tabInfo`, `$data`.

**Source:** [views/wpts-frontend.php:204](../views/wpts-frontend.php#L204) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

**See also:** abp01_additional_frontend_viewer_tabs for registering additional tabs.

### `abp01_additional_frontend_viewer_tabs`

**Filter · Since 0.3.2 · 2 callback argument(s)**

Filters the additional front-end viewer tabs. It cannot be used to modify the existing ones. The initial value
is an empty array. Non-array results are discarded. Must be an array with tab codes as keys and, for each key,
an array with a mandatory "label" key as values. Other keys are valid, but left alone. Post ID can be an
positive integer or a null value. If a null value is passed, all available tabs should be provided, since this
is called from the settings page where the default selected tab is configured.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$additionalTabs` | `array<string, array>` | The list of additional tabs |
| 2 | `$postId` | `?int` | The post ID or null |

**Dispatch arguments:** `array()`, `$postId`.

**Source:** [lib/Viewer.php:103](../lib/Viewer.php#L103) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_frotend_theme_class`

**Filter · Since 0.3.2 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the class used as theme decorator. Must implement Abp01_FrontendTheme. If the result is not a string
or the string does not represent a valid class, the default theme decorator is used:
Abp01_FrontendTheme_Decorator.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$frontendThemeClass` | `string` | The theme class decorator FQN |

**Dispatch arguments:** `'Abp01_FrontendTheme_Decorator'`.

**Source:** [lib/View.php:91](../lib/View.php#L91) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_info_item_field_icon_mapping`

**Filter · Since 0.3.3 · 1 callback argument(s)**

Filters the icon identifier assigned to each front-end trip summary information field. The array is keyed by
information field name and each value is an icon identifier. Built-in identifiers are represented by the
ABP01_INFO_ITEM_ICON_* constants. Custom identifiers may also be used when matching trusted SVG markup is
registered through the abp01_info_item_icons filter. A field mapped to an identifier that is not present in
the icon registry is rendered without an icon. The filtered mapping is cached for the remainder of the
request. Non-array and empty-array results are ignored in favor of the defaults.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultMapping` | `array<string, string>` | The default icon identifiers keyed by information field name. |

**Dispatch arguments:** `$defaultMapping`.

**Source:** [views/helpers/controls.frontend.php:317](../views/helpers/controls.frontend.php#L317) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

**See also:** abp01-plugin-header.php for the constants representing built-in icon identifiers.; abp01_info_item_icons for registering built-in or custom icon markup.

### `abp01_info_item_icons`

**Filter · Since 0.3.3 · 1 callback argument(s)**

Filters the SVG element markup used for front-end trip summary information icons. The array is keyed by icon
identifier and each value contains the inner markup inserted unescaped into the common 24x24 SVG wrapper. Icon
identifiers are resolved through the info item field-to-icon mapping. The filtered array is cached for the
remainder of the request. Non-array and empty-array results are ignored in favor of the defaults. Callbacks
must provide trusted SVG element markup.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultIcons` | `array<string, string>` | The default raw SVG inner markup keyed by icon identifier. |

**Dispatch arguments:** `$defaultIcons`.

**Source:** [views/helpers/controls.frontend.php:392](../views/helpers/controls.frontend.php#L392) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

**See also:** abp01-plugin-header.php for ABP01_INFO_ITEM_ICON_* constants that represent built-in icon names.

### `abp01_is_post_trip_summary_protected`

**Filter · Since 0.3.3 · 2 callback argument(s)**

Filters whether to suppress the front-end trip summary viewer for the current post. Runs before automatic
attachment to the post content and before shortcode rendering. The initial value is true only when
post_password_required($postId) returns true for the current request. Callbacks can override this decision for
the given post. Only a literal boolean true suppresses the viewer: the content filter returns its incoming
content unchanged and the shortcode returns an empty string. Any other result allows the normal viewer
rendering checks to continue.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$isProtected` | `bool` | Whether to suppress the trip summary viewer in the current request. |
| 2 | `$postId` | `int` | The identifier of the post whose trip summary is being checked. |

**Dispatch arguments:** `$isProtected`, `$postId`.

**Source:** [lib/Auth.php:161](../lib/Auth.php#L161) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_jsonld_frontend_data_enabled`

**Filter · Since 0.3.3 · 2 callback argument(s)**

Filters whether to output JSON-LD frontend data for the given post. Defaults to the value of the corresponding
plug-in setting. Result is converted such that only boolean true enables the output.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$enabled` | `bool` | Whether or not the JSON-LD frontend data output is enabled |
| 2 | `$postId` | `int` | The post identifier |

**Dispatch arguments:** `$this->_settings->getEnableJsonLdFrontenData()`, `$postId`.

**Source:** [lib/pluginModules/JsonLdFrontendDataPluginModule.php:107](../lib/pluginModules/JsonLdFrontendDataPluginModule.php#L107) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_should_render_frontend_route_log_meta`

**Filter · Since 0.3.2 · 2 callback argument(s)**

Filters whether to display metadata for each front-end trip summary route log item. Defaults to true. Only a
literal boolean true enables the metadata.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$showMeta` | `bool` | True to show the metadata information, false otherwise |
| 2 | `$viewerData` | `stdClass` | The complete front-end viewer data for the current post. |

**Dispatch arguments:** `true`, `$viewerData`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:240](../lib/pluginModules/RouteLogPluginModule.php#L240) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_show_frontend_viewer_credits`

**Filter · Since 0.3.3 · 2 callback argument(s)**

Filters whether to display the front-end viewer credits. Runs at the beginning of the viewer template.
Defaults to true; the result is converted to boolean. Returning false omits the entire credits container,
independently of whether the bottom teaser is enabled.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$showCredits` | `bool` | Whether to display the credits. Defaults to true. |
| 2 | `$data` | `stdClass` | The complete front-end viewer data for the current post. |

**Dispatch arguments:** `true`, `$data`.

**Source:** [views/wpts-frontend.php:54](../views/wpts-frontend.php#L54) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_show_frontend_viewer_subtitle`

**Filter · Since 0.3.3 · 2 callback argument(s)**

Filters the subtitle displayed below the front-end viewer title. Defaults to null, which omits the subtitle
element. Any non-null result renders the element, including an empty string. The text is HTML-escaped.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$subtitle` | `string\|null` | The subtitle text, or null to omit the element. |
| 2 | `$data` | `stdClass` | The complete front-end viewer data for the current post. |

**Dispatch arguments:** `null`, `$data`.

**Source:** [views/wpts-frontend.php:70](../views/wpts-frontend.php#L70) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Installer

### `abp01_install_tables_definitions`

**Filter · Since 0.3.0 · 2 callback argument(s)**

Filters custom database table definitions installed alongside the built-in tables. Initial value is an empty
array. Each entry must use the table name as its key and a CREATE TABLE statement as its value. Non-array
results are discarded. Built-in definitions are merged afterwards, so a custom definition cannot override a
built-in table with the same name.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$customTables` | `array<string, string>` | Custom table definitions keyed by table name. |
| 2 | `$ownTables` | `array<string, string>` | Built-in table definitions keyed by table name. |

**Dispatch arguments:** `array()`, `$ownTables`.

**Source:** [lib/installer/step/InstallSchema.php:107](../lib/installer/step/InstallSchema.php#L107) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_installer_do_post_install`

**Action · Since 0.3.0 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after the plug-in installation attempt has finished. The hook runs after either a successful or failed
installation, provided the pre-install hook allowed the process to start. Callbacks can inspect the context to
determine the installation result and access any collected errors. An uncaught callback exception is recorded
as a hook error but does not change that result.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$context` | `Abp01_Installer_Context` | Installer context containing the completed operation status and collected errors. |

**Dispatch arguments:** `$context`.

**Source:** [lib/installer/step/RunPostInstallHooks.php:54](../lib/installer/step/RunPostInstallHooks.php#L54) · **Dispatcher:** `Abp01_Installer_Service_RunInstallHook` · **Resolution:** static · **Documentation:** documented.

### `abp01_installer_do_post_uninstall`

**Action · Since 0.3.0 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after the plug-in uninstallation process has finished. Callbacks can inspect the installer context to
determine whether the operation completed successfully and access any collected errors.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$context` | `Abp01_Installer_Context` | The current installer context. |

**Dispatch arguments:** `$context`.

**Source:** [lib/installer/step/RunPostUninstallHooks.php:51](../lib/installer/step/RunPostUninstallHooks.php#L51) · **Dispatcher:** `Abp01_Installer_Service_RunInstallHook` · **Resolution:** static · **Documentation:** documented.

### `abp01_installer_do_pre_install`

**Action · Since 0.3.0 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires before the plug-in installation process starts. Callbacks can inspect or modify the installer context.
Adding an operation error to the context prevents installation from starting. An uncaught callback exception
is captured as a hook error and also aborts installation.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$context` | `Abp01_Installer_Context` | Mutable installer context used to determine whether installation may proceed. |

**Dispatch arguments:** `$context`.

**Source:** [lib/installer/step/RunPreInstallHooks.php:53](../lib/installer/step/RunPreInstallHooks.php#L53) · **Dispatcher:** `Abp01_Installer_Service_RunInstallHook` · **Resolution:** static · **Documentation:** documented.

### `abp01_installer_do_pre_uninstall`

**Action · Since 0.3.0 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires before the plug-in uninstallation process starts. Callbacks can inspect the installer context to
determine whether the operation currently has any errors.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$context` | `Abp01_Installer_Context` | The current installer context. |

**Dispatch arguments:** `$context`.

**Source:** [lib/installer/step/RunPreUninstallHooks.php:51](../lib/installer/step/RunPreUninstallHooks.php#L51) · **Dispatcher:** `Abp01_Installer_Service_RunInstallHook` · **Resolution:** static · **Documentation:** documented.

### `abp01_uninstall_table_names`

**Filter · Since 0.3.3 · 2 callback argument(s)**

Filters custom database table names dropped during uninstallation. Initial value is an empty array. Return an
array whose values are non-empty table name strings; keys are ignored. Custom tables are processed in the
order provided. Invalid entries are removed. Empty or non-array results are ignored, and built-in tables are
always appended to the final list, so the filter cannot remove built-in tables from the drop list.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$customTables` | `string[]` | Custom table names. |
| 2 | `$ownTables` | `string[]` | Built-in table names in drop order. |

**Dispatch arguments:** `array()`, `$ownTables`.

**Source:** [lib/installer/step/UninstallSchema.php:93](../lib/installer/step/UninstallSchema.php#L93) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Log Management

### `abp01_customize_monolog_logger`

**Action · Since 0.3.2 · 1 callback argument(s)**

Fires after the Monolog logger has been configured and before it is cached for use. Callbacks can customize
the logger instance by adding or changing handlers, processors, and other Monolog configuration.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$logger` | `\Monolog\Logger` | The configured Monolog logger instance. |

**Dispatch arguments:** `$logger`.

**Source:** [lib/logger/MonologLogger.php:83](../lib/logger/MonologLogger.php#L83) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_logger_class`

**Filter · Since 0.3.1 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the class used to create the plug-in logger. The initial value is Abp01_Logger_MonologLogger. A custom
class must be a concrete implementation of Abp01_Logger and accept an Abp01_Logger_Config instance as its
constructor argument. Return an existing class name: the value is passed directly to class_implements().
Existing classes that do not implement Abp01_Logger, abstract classes, and constructors that throw an
Exception fall back to the default logger. Invalid names or other thrown errors are not guaranteed to be
handled.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$loggerClass` | `string` | The logger implementation class name. |

**Dispatch arguments:** `$defaultLoggerClass`.

**Source:** [lib/logger/Manager.php:79](../lib/logger/Manager.php#L79) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_logger_config_max_log_files`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters the number of rotated log files to retain. Initial and default value is either the value of the
integer constant ABP01_LOGS_MAX_LOG_FILES, if defined, or 10 by default. Automatically converted to integer.
Zero and negative filtered values reset to the configured default. To retain unlimited files, set
ABP01_LOGS_MAX_LOG_FILES to 0; returning 0 from this filter alone does not override a positive configured
default.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$maxLogFiles` | `int` | The configured maximum number of rotated log files. |

**Dispatch arguments:** `$maxLogFiles`.

**Source:** [abp01-plugin-functions.php:238](../abp01-plugin-functions.php#L238) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_logger_config_rotate_logs`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters whether or not to rotate logs. Initial value is either the value of boolean constant
ABP01_LOGS_ROTATE, if defined, or true by default. Only a literal boolean true enables rotation.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$rotateLogs` | `bool` | Whether logs should be rotated. |

**Dispatch arguments:** `$rotateLogs`.

**Source:** [abp01-plugin-functions.php:214](../abp01-plugin-functions.php#L214) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_log_file_display_tail_line_count`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters the number of trailing lines displayed for an oversized log file. The initial value is 512, or the
value of the ABP01_LOG_FILE_DISPLAY_TAIL_LINE_COUNT constant when defined. The filtered value is converted to
an integer; zero and negative values fall back to 512. This value also contributes to the default maximum
displayable file size.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$tailLineCount` | `int` | The number of trailing log lines to display. |

**Dispatch arguments:** `$tailLineCount`.

**Source:** [lib/pluginModules/SystemLogsManagementPluginModule.php:280](../lib/pluginModules/SystemLogsManagementPluginModule.php#L280) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_max_displayable_log_file_size`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters the maximum log file size that the system logs viewer displays in full. The initial value, in bytes,
is the effective log tail line count multiplied by 512. Files exceeding the resulting size are trimmed and
only their trailing lines are displayed. The filtered value is converted to an integer; zero and negative
values are ignored in favor of the initial value.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$maxSize` | `int` | The maximum file size, in bytes, to display in full. |

**Dispatch arguments:** `$maxSize`.

**Source:** [lib/pluginModules/SystemLogsManagementPluginModule.php:250](../lib/pluginModules/SystemLogsManagementPluginModule.php#L250) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Lookup Types Management

### `abp01_get_lookup_type_label`

**Filter · Since 0.3.2 · 2 callback argument(s)**

Filters the label for a lookup type. The initial value is the translated lookup type label, or null for an
unknown type. The filtered result is returned without further normalization.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$translatedLabel` | `string\|null` | The translated label, or null for an unknown type. |
| 2 | `$type` | `string` | The lookup type, as defined by the Abp01_Lookup class. |

**Dispatch arguments:** `$translatedLabel`, `$type`.

**Source:** [abp01-plugin-functions.php:579](../abp01-plugin-functions.php#L579) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

**See also:** Abp01_Lookup

## Maintenance Tools

### `abp01_enable_clear_all_data_maintenance_tool`

**Filter · Since 0.2.8 · 1 callback argument(s)**

Filters whether the built-in clear all trip summary data maintenance tool is available. Initial value is true.
Only the boolean value true keeps the tool available.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$enabled` | `bool` | Whether the clear all trip summary data maintenance tool is available. |

**Dispatch arguments:** `true`.

**Source:** [lib/Plugin.php:475](../lib/Plugin.php#L475) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_enable_clear_cache_maintenance_tool`

**Filter · Since 0.2.8 · 1 callback argument(s)**

Filters whether the built-in clear track data cache maintenance tool is available. Initial value is true. Only
the boolean value true keeps the tool available.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$enabled` | `bool` | Whether the clear track data cache maintenance tool is available. |

**Dispatch arguments:** `true`.

**Source:** [lib/Plugin.php:461](../lib/Plugin.php#L461) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_enable_detect_missing_tracks_maintenance_tool`

**Filter · Since 0.2.8 · 1 callback argument(s)**

Filters whether the built-in detect missing track files maintenance tool is available. Initial value is true.
Only the boolean value true keeps the tool available.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$enabled` | `bool` | Whether the detect missing track files maintenance tool is available. |

**Dispatch arguments:** `true`.

**Source:** [lib/Plugin.php:489](../lib/Plugin.php#L489) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_additional_maintenance_tools`

**Filter · Since 0.3.2 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the list of additional maintenance tools, on top of the built-in ones. Initial value is an empty
array. If result is not an array, it will be reset to an empty array. Entries which are not instance of
Abp01_MaintenanceTool will be removed, nulls included.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$tools` | `Abp01_MaintenanceTool[]` | The current list of additional tools. |

**Dispatch arguments:** `$tools`.

**Source:** [lib/Plugin.php:512](../lib/Plugin.php#L512) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

**See also:** Abp01_MaintenanceTool

### `abp01_render_maintenance_tool_result`

**Filter · Since 0.2.8 · 3 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the rendered content returned after a maintenance tool has executed. The initial value is the rendered
result template, an empty string when no matching result template exists, or false when rendering fails.
Returning an empty or false value prevents result content from being displayed.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$renderedResult` | `string\|false` | The rendered maintenance tool result. |
| 2 | `$toolId` | `string` | The executed maintenance tool identifier. |
| 3 | `$result` | `Abp01_MaintenanceTool_Result` | The maintenance tool execution result. |

**Dispatch arguments:** `$renderedResult`, `$toolId`, `$result`.

**Source:** [lib/pluginModules/MaintenancePluginModule.php:184](../lib/pluginModules/MaintenancePluginModule.php#L184) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Plugin Modules

### `abp01_get_injectable_service_factories`

**Filter · Since 0.2.6 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the service factories available for plugin module dependency injection. The array is keyed by
dependency class name. Each value is a zero-argument callable that returns the corresponding service instance.
Callbacks may add, replace, or remove factories before the plugin module activator is created. An empty array
or a value of another type is ignored in favor of the defaults.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultInjectables` | `array<string, callable>` | The default service factories keyed by dependency class name. |
| 2 | `$pluginModuleHost` | `Abp01_PluginModules_PluginModuleHost` | The plugin module host requesting the factories. |

**Dispatch arguments:** `$defaultInjectables`, `$this`.

**Source:** [lib/pluginModules/PluginModuleHost.php:109](../lib/pluginModules/PluginModuleHost.php#L109) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## REST API

### `abp01_add_trip_summary_to_rest_api`

**Filter · Since 0.3.0 · 1 callback argument(s)**

Filters whether to register the read-only wpts_trip_summary field on supported post types in the WordPress
REST API. Defaults to true. Only a literal boolean true enables registration.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$shouldAddTripSummaryToRestAPI` | `bool` | True if it should be added, false otherwise |

**Dispatch arguments:** `true`.

**Source:** [lib/pluginModules/RestApiEnhancementsPluginModule.php:74](../lib/pluginModules/RestApiEnhancementsPluginModule.php#L74) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_add_trip_summary_to_rest_api_listing`

**Filter · Since 0.3.0 · 1 callback argument(s)**

Filters whether to add trip summary data for each post when the request is for a post listing. Defaults to
false. Only a literal boolean true includes the data in listings; otherwise the registered field returns null
for those requests.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$shouldAddTripSummaryToRestApiListing` | `bool` | True to add, false otherwise. |

**Dispatch arguments:** `false`.

**Source:** [lib/pluginModules/RestApiEnhancementsPluginModule.php:136](../lib/pluginModules/RestApiEnhancementsPluginModule.php#L136) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Settings - Trip Summary Map

### `abp01_default_tile_layer_id`

**Filter · Since 0.2.7 · 2 callback argument(s)**

Filters the identifier of the pre-defined tile layer selected by default. The initial value is
"open-street-map". Callbacks also receive the identifiers available after the pre-defined tile layer list has
been filtered. The result must be a non-empty string identifying one of those layers. An empty or non-string
result falls back to "open-street-map"; an unknown non-empty string causes default tile layer resolution to
fail.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultTileLayerId` | `string` | The default pre-defined tile layer identifier. |
| 2 | `$allTileLayerIds` | `string[]` | The available pre-defined tile layer identifiers. |

**Dispatch arguments:** `self::TL_OPEN_STREET_MAP`, `$allTileLayerIds`.

**Source:** [lib/settings/PredefinedTileLayer.php:291](../lib/settings/PredefinedTileLayer.php#L291) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_predefined_tile_layers`

**Filter · Since 0.3.2 · 1 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the list of available pre-defined tile layers offered as options when configuring the tile layer used
for the viewer. The return value must be a non-empty array of Abp01_Settings_PredefinedTileLayer instances.
Invalid elements are filtered out. If the resulting value is an empty array, the default list is used instead.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$predefinedTileLayers` | `Abp01_Settings_PredefinedTileLayer[]` | The list of pre-defined tile layers. |

**Dispatch arguments:** `$predefinedTileLayers`.

**Source:** [lib/settings/PredefinedTileLayer.php:240](../lib/settings/PredefinedTileLayer.php#L240) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Time Management

### `abp01_determine_date_format`

**Filter · Since 0.3.2 · 2 callback argument(s)**

Filters the date format used by the plug-in, optionally including time format. Initial value is the format as
returned by date_format option, as well as time_format option, if time format is also required. If result is
empty, the initial value will be used.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$format` | `string` | The date format |
| 2 | `$withTime` | `bool` | Whether or not to include time format as well |

**Dispatch arguments:** `$format`, `$withTime`.

**Source:** [abp01-plugin-functions.php:734](../abp01-plugin-functions.php#L734) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

## Trip Summary Management

### `abp01_after_trip_summary_audit_log`

**Action · Since 0.3.0 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires immediately after the trip summary audit log table has been rendered. Callback output is rendered after
the closing table element.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The ID of the post whose trip summary audit log is being rendered. |
| 2 | `$data` | `Abp01_ViewModel_PostAuditLogVm` | The audit log view model and rendering context. |

**Dispatch arguments:** `$data->postId`, `$data`.

**Source:** [views/wpts-audit-log.php:168](../views/wpts-audit-log.php#L168) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_after_trip_summary_info_audit_log`

**Action · Since 0.3.0 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after the trip summary information audit rows have been rendered. The hook runs inside the table body,
immediately before the track audit section. Callback output should consist of valid table row elements.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The ID of the post whose trip summary audit log is being rendered. |
| 2 | `$data` | `Abp01_ViewModel_PostAuditLogVm` | The audit log view model and rendering context. |

**Dispatch arguments:** `$data->postId`, `$data`.

**Source:** [views/wpts-audit-log.php:103](../views/wpts-audit-log.php#L103) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_after_trip_summary_track_audit_log`

**Action · Since 0.3.0 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after the trip summary track audit rows have been rendered. The hook runs inside the table body,
immediately before its closing tag. Callback output should consist of valid table row elements.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The ID of the post whose trip summary audit log is being rendered. |
| 2 | `$data` | `Abp01_ViewModel_PostAuditLogVm` | The audit log view model and rendering context. |

**Dispatch arguments:** `$data->postId`, `$data`.

**Source:** [views/wpts-audit-log.php:149](../views/wpts-audit-log.php#L149) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_before_trip_summary_audit_log`

**Action · Since 0.3.0 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires immediately before the trip summary audit log table is rendered. Callback output is rendered before the
opening table element.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The ID of the post whose trip summary audit log is being rendered. |
| 2 | `$data` | `Abp01_ViewModel_PostAuditLogVm` | The audit log view model and rendering context. |

**Dispatch arguments:** `$data->postId`, `$data`.

**Source:** [views/wpts-audit-log.php:55](../views/wpts-audit-log.php#L55) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_editor_launcher_metabox_after_status`

**Action · Since 0.3.0 · 1 callback argument(s)**

Fires after the built-in status items in the trip summary editor launcher metabox. Callback output is rendered
directly inside the status container, after the trip summary information and track status items and before the
container closes.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$data` | `stdClass` | The editor launcher context containing the post ID, trip summary availability flags, and track download URL. |

**Dispatch arguments:** `$data`.

**Source:** [views/wpts-editor-launcher-metabox.php:87](../views/wpts-editor-launcher-metabox.php#L87) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_editor_launcher_metabox_before_status`

**Action · Since 0.3.0 · 1 callback argument(s)**

Fires before the built-in status items in the trip summary editor launcher metabox. Callback output is
rendered directly inside the status container, before the trip summary information and track status items.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$data` | `stdClass` | The editor launcher context containing the post ID, trip summary availability flags, and track download URL. |

**Dispatch arguments:** `$data`.

**Source:** [views/wpts-editor-launcher-metabox.php:50](../views/wpts-editor-launcher-metabox.php#L50) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_formatted_route_type_listing_label`

**Filter · Since 0.3.2 · 4 callback argument(s)**

Filters the formatted route type label displayed in the post listing. Initial value is a span containing the
unformatted label, with the base CSS class abp01-route-type-cell and a route-specific or none modifier class.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$formatted` | `string` | The formatted route type label HTML. |
| 2 | `$postId` | `int\|null` | The post identifier. |
| 3 | `$routeType` | `string\|null` | The route type code, or null/empty when none is assigned. |
| 4 | `$routeTypeLabel` | `string\|null` | The unformatted route type label after filtering. |

**Dispatch arguments:** `$formatted`, `$postId`, `$routeType`, `$routeTypeLabel`.

**Source:** [lib/display/postListing/TripSummaryRouteTypeColumn.php:73](../lib/display/postListing/TripSummaryRouteTypeColumn.php#L73) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_get_track_file_downloader`

**Filter · Since 0.3.3 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the track file download channel for a given post. Must be a valid Abp01_Transfer_FileDownloader
instance. If an invalid value is provided, then the default downloader is used.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$trackFileDownloaderInstance` | `Abp01_Transfer_FileDownloader` | The current track file downloader instance |
| 2 | `$postId` | `int` | The current post ID |

**Dispatch arguments:** `$defaultInstance`, `$postId`.

**Source:** [lib/transfer/TrackFileDownloader.php:103](../lib/transfer/TrackFileDownloader.php#L103) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_available_for_post_types`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters the post types for which trip summary is available. Initial value is [ 'post', 'page' ]. Can be empty,
but must be array. If non-array returned, initial value will be used.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postTypes` | `string[]` | The post types for which trip summary is available |

**Dispatch arguments:** `$postTypes`.

**Source:** [lib/AvailabilityHelper.php:64](../lib/AvailabilityHelper.php#L64) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_log_after_save_entry`

**Action · Since 0.3.0 · 3 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after a trip summary log entry save attempt, including an unsuccessful attempt. Callbacks receive the
current post identifier, the log entry instance and whether the operation succeeded or not.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The post identifier |
| 2 | `$logEntry` | `\Abp01_Route_Log_Entry` | The log entry instance after the save attempt. |
| 3 | `$success` | `bool` | Whether the save operation succeeded. |

**Dispatch arguments:** `$postId`, `$logEntry`, `$success`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:558](../lib/pluginModules/RouteLogPluginModule.php#L558) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_log_before_save_entry`

**Action · Since 0.3.0 · 2 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Fires after validation succeeds and before a trip summary log entry is saved. Callbacks receive the current
post identifier and the log entry instance.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$postId` | `int` | The post identifier |
| 2 | `$logEntry` | `\Abp01_Route_Log_Entry` | The log entry instance that is about to be saved |

**Dispatch arguments:** `$postId`, `$logEntry`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:531](../lib/pluginModules/RouteLogPluginModule.php#L531) · **Dispatcher:** `do_action` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_log_enabled`

**Filter · Since 0.3.2 · 1 callback argument(s)**

Filters whether the trip summary log feature should be made available. Initial value is either the value of
boolean constant ABP01_TRIP_SUMMARY_LOG_ENABLED, if defined, or true if not defined. Only a literal boolean
true enables the feature.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$enabled` | `bool` | Whether or not trip summary log is available |

**Dispatch arguments:** `$enabled`.

**Source:** [lib/FeatureStatus.php:58](../lib/FeatureStatus.php#L58) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_log_validate`

**Filter · Since 0.3.3 · 3 callback argument(s)**

> **Unstable:** Susceptible to breaking changes due to PSR-4 migration.

Filters the validation result for a trip summary route log entry. The initial value is the result of the
built-in input validation. Result is converted such that only boolean true is considered valid.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$isValid` | `bool` | Whether the log entry is valid. |
| 2 | `$postId` | `int` | The post identifier |
| 3 | `$logEntry` | `\Abp01_Route_Log_Entry` | The log entry to validate |

**Dispatch arguments:** `$isValid`, `$postId`, `$logEntry`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:500](../lib/pluginModules/RouteLogPluginModule.php#L500) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_route_log_default_entry_date`

**Filter · Since 0.3.0 · 2 callback argument(s)**

Filters the date used to prefill a new trip summary route log entry. The initial value is the current date in
Y-m-d format. A null result is normalized to an empty string and the final result is converted to string.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultDate` | `string` | The current date in Y-m-d format. |
| 2 | `$postId` | `int` | The ID of the post whose route log is being edited. |

**Dispatch arguments:** `$defaultDate`, `$postId`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:370](../lib/pluginModules/RouteLogPluginModule.php#L370) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_route_log_default_entry_rider`

**Filter · Since 0.3.0 · 2 callback argument(s)**

Filters the rider used to prefill a new trip summary route log entry. The initial value is the display name of
the current WordPress user. A null result is normalized to an empty string and the final result is converted
to string.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$displayName` | `string` | The current WordPress user's display name. |
| 2 | `$postId` | `int` | The ID of the post whose route log is being edited. |

**Dispatch arguments:** `$displayName`, `$postId`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:350](../lib/pluginModules/RouteLogPluginModule.php#L350) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_trip_summary_route_log_default_entry_vehicle`

**Filter · Since 0.3.0 · 2 callback argument(s)**

Filters the vehicle used to prefill a new trip summary route log entry. The initial value is the last vehicle
recorded for the post, or null when no vehicle is available. A null result is normalized to an empty string
and the final result is converted to string.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$defaultVehicle` | `string\|null` | The last vehicle recorded for the post, if available. |
| 2 | `$postId` | `int` | The ID of the post whose route log is being edited. |

**Dispatch arguments:** `$defaultVehicle`, `$postId`.

**Source:** [lib/pluginModules/RouteLogPluginModule.php:392](../lib/pluginModules/RouteLogPluginModule.php#L392) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.

### `abp01_unformatted_route_type_label`

**Filter · Since 0.3.2 · 3 callback argument(s)**

Filters the unformatted (i.e. no HTML) route type label displayed in the post listing. Initial value is the
translated route type label, a dash when no route type is assigned, or null when the route type is unknown. If
the return value is not a string, the initial value will be used.

| Position | Parameter | Type | Description |
| ---: | --- | --- | --- |
| 1 | `$routeTypeLabel` | `string\|null` | The unformatted route type label. |
| 2 | `$postId` | `int\|null` | The post identifier. |
| 3 | `$routeType` | `string\|null` | The route type code, or null/empty when none is assigned. |

**Dispatch arguments:** `$routeTypeLabel`, `$postId`, `$routeType`.

**Source:** [lib/display/postListing/TripSummaryRouteTypeColumn.php:104](../lib/display/postListing/TripSummaryRouteTypeColumn.php#L104) · **Dispatcher:** `apply_filters` · **Resolution:** static · **Documentation:** documented.
