# Coding convention examples

Companion to [the main skill](../SKILL.md). Snippets are excerpts or shortened adaptations of the linked source, with incidental whitespace normalized. They illustrate conventions rather than propose new features. License headers are omitted from snippets; complete source files use [the license template](.license-header).

General block and indentation rules apply across languages; PHP arrays, namespaces, concatenation and WordPress APIs are PHP-specific. The `WPTS_`/`wpts_` examples illustrate the established policy for new global names, not names already present in the plugin.

## Contents

- [License header utility](#license-header-utility)
- [Blocks and expressions](#blocks-and-expressions)
- [Declarations and calls](#declarations-and-calls)
- [Arrays and access](#arrays-and-access)
- [String concatenation](#string-concatenation)
- [Global constants and functions](#global-constants-and-functions)
- [Classes and file layout](#classes-and-file-layout)
- [Filter and action hooks](#filter-and-action-hooks)
- [Templates](#templates)
- [JavaScript and TypeScript](#javascript-and-typescript)
- [CSS](#css)
- [Tests](#tests)

## License header utility

Run [license-header-utility.php](../scripts/license-header-utility.php) from the plugin root with PHP 8.2 or newer. It works without loading WordPress. Paths with spaces must be quoted.

After creating a new source file or modifying an existing one, insert the missing license or advance its year:

```text
php .agents/skills/wpts-coding-conventions/scripts/license-header-utility.php lib/Example.php --update
php .agents/skills/wpts-coding-conventions/scripts/license-header-utility.php lib/Example.php --check
```

These paths are illustrative; pass the actual file being edited. Create the file before calling the utility. Pure PHP files should already have their opening tag. An existing PHP/PHTML template containing only HTML receives a separate PHP comment block without changing its rendered markup.

To inspect a file or preview an update:

```text
php .agents/skills/wpts-coding-conventions/scripts/license-header-utility.php lib/Example.php --read --json
php .agents/skills/wpts-coding-conventions/scripts/license-header-utility.php lib/Example.php --read --full
php .agents/skills/wpts-coding-conventions/scripts/license-header-utility.php lib/Example.php --update --dry-run
```

| Option | Behavior |
| --- | --- |
| `--read` | Default action; report the recognized project license and its year |
| `--full` | Include the full comment in a text read report |
| `--json` | Return header metadata as JSON, or JSON `null` if absent |
| `--check` | Exit successfully when the header exists and its year is at least the target year; otherwise exit 1 |
| `--update` | Insert a missing header or update only the year of an existing project notice |
| `--dry-run` | With `--update`, print the proposed file contents without writing |
| `--year=YYYY` | Override the current-year target for `--update` or `--check`; useful for reproducible tests |
| `--help` | Print usage and exit successfully |

Read reports keep the comment's `offset` and `length` in original file bytes; the JSON `header` text normalizes CRLF to LF and includes the closing `*/`. Successful reads exit 0 even when no header is found; use `--check` when presence matters. Invalid arguments exit 2; file errors exit 1 and go to stderr.

Updates support `.php`, `.phtml`, `.js`, `.ts` (including `.d.ts`) and `.css`. They preserve existing code, other comments and LF/CRLF line endings. A current or later year produces no write. Existing notices are not replaced with the template, and WordPress plugin metadata is retained. The recognizer targets this project's `Copyright (c) 2014-YYYY Alexandru Boia and Contributors` block comment, not arbitrary license formats.

Apply updates only to authored source files being changed. Skip utility/build scripts, configuration, third-party code, generated assets and fixtures deliberately testing missing or old licenses. Browser comment recognition is a simple block-comment scan, not a JavaScript or CSS parser.

Standalone regression runners:

```text
php .agents/skills/wpts-coding-conventions/tests/license-header-test.php
php .agents/skills/wpts-coding-conventions/tests/license-header-utility-test.php
```

The [reader/generator/updater runner](../tests/license-header-test.php) uses the supplied [fixture directory](../tests/license-header-files/) and temporary copies. The [CLI runner](../tests/license-header-utility-test.php) verifies read/check/update/preview behavior, the current-year default, argument errors and paths with spaces.

## Blocks and expressions

One space after a control keyword and before `{`, tabs inside blocks, and `else if` on the closing-brace line. Even a single-statement body uses braces. Adapted from [PluginModule.php](../../../../lib/pluginModules/PluginModule.php).

```php
if ($post && is_object($post)) {
	$postId = intval($post->ID);
} else if ($post && is_numeric($post)) {
	$postId = $post;
} else {
	$postId = 0;
}
```

Nested blocks add one tab per level. Logical continuations start with their operator; a multiline ternary places `?` and `:` on equally indented lines. Patterns from [Includes/Manager](../../../../lib/includes/Manager.php) and [plugin functions](../../../../abp01-plugin-functions.php):

```php
if ($enabled === null) {
	if (!function_exists('got_url_rewrite')) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}
	$enabled = got_url_rewrite();
}

$isAbsolute = is_array($path)
	&& isset($path['absolute'])
	&& $path['absolute'] === true;

$deps = isset($script['deps']) && is_array($script['deps'])
	? $script['deps']
	: array();
```

Both strict and loose comparisons exist. Preserve their intended behavior; this formatting example does not prescribe replacing one with the other.

## Declarations and calls

No padding inside parentheses; one space after commas, around default-value `=` and after a return-type colon. Examples with zero, one and multiple arguments:

```php
function wpts_get_current_locale(): string {
	return get_locale();
}

function wpts_normalize_label(string $label): string {
	return trim($label);
}

function wpts_format_date(string $format, string $date, bool $translate = true): string|int|false {
	return mysql2date($format, $date, $translate);
}
```

For wrapped declarations, the first argument commonly remains on the opening line. This class-body excerpt follows [installer step Activate](../../../../lib/installer/step/Activate.php):

```php
private function _executeHookStep(Abp01_Installer_Step $step,
	Abp01_Installer_Context $context): bool {
	$result = $step->execute();
	$error = $step->getLastError();

	if ($error !== null) {
		$context->pushHookError(get_class($step), $error);
	}

	return $result;
}
```

Ordinary wrapped calls close after the last argument, as in [Includes/Manager](../../../../lib/includes/Manager.php). More deeply nested calls can put the opening and closing argument-list delimiters on their own lines, as in `Activate`:

```php
wp_enqueue_script($handle,
	$srcUrl,
	$deps,
	$script['version'],
	$this->_scriptsInFooter);

$preInstallOk = $this->_executeHookStep(
	new Abp01_Installer_Step_RunPreInstallHooks($context),
	$context
);
```

Anonymous functions keep `function(` tight and separate the capture clause with a space. From [FeatureStatus tests](../../../../tests/test-FeatureStatus.php):

```php
$filter = static function($enabled) use ($filteredStatus) {
	return $filteredStatus;
};
```

## Arrays and access

PHP uses `array(...)` for literals, `[]` for indexing/appending, and tight `->`/`::` access. Nested entries get another tab; `=>` has one space on each side. Adapted from [ExpectedLookupData](../../../../tests/lib/data/ExpectedLookupData.php):

```php
$lookupItem = array(
	'default' => 'Easy',
	'translations' => array(
		'ro_RO' => 'Ușor',
		'fr_FR' => 'Facile',
		'de_DE' => 'Leicht'
	)
);

$label = $lookupItem['translations'][$langCode];
$labels[] = $label;
$env = \WpTripSummary\Env::getInstance();
$db = $env->getDb();
```

Callback arrays and multiline chains follow the same spacing. From [Includes/Manager](../../../../lib/includes/Manager.php) and [AboutPagePluginModule](../../../../lib/pluginModules/AboutPagePluginModule.php):

```php
add_action('admin_enqueue_scripts',
	array($this, 'onAdminEnqueueStyles'));

$deps = $this->_scriptsDependencySelector
	->selectDependencies($deps);
```

Most production arrays omit the final comma. Some data providers, including `tests/test-FeatureStatus.php`, retain trailing commas; match the surrounding collection when editing it.

## String concatenation

Keep one space between PHP `.` and its operands. For a wrapped concatenation, keep the dot at the end of the preceding line. These examples retain the convention previously specified by this skill; older source also contains leading-dot continuations.

```php
$message = 'Invalid value found: <' . $invalidValue . '>.';

$message = 'Invalid value found: <' . $invalidValue . '>. ' .
	'Another invalid value found: <' . $anotherInvalidValue . '>.';
```

Indent concatenated HTML to show its nesting, and escape dynamic text for its output context:

```php
$message =
	'<div>' .
		'<p>' .
			esc_html($text) .
		'</p>' .
	'</div>';
```

## Global constants and functions

New names use `WPTS_`/`wpts_`; existing `ABP01_`/`abp01_` names remain unchanged. These illustrative definitions mirror the conditional structure in [the plugin header](../../../../abp01-plugin-header.php) and [plugin functions](../../../../abp01-plugin-functions.php).

An ordinary constant and a configurable constant in `abp01-plugin-header.php`:

```php
define('WPTS_EXAMPLE_FORMAT_VERSION', 1);

if (!defined('WPTS_EXAMPLE_CACHE_ENABLED')) {
	define('WPTS_EXAMPLE_CACHE_ENABLED', true);
}
```

A pluggable function in `abp01-plugin-functions.php` checks its exact global name before declaring it:

```php
if (!function_exists('wpts_get_current_locale')) {
	function wpts_get_current_locale(): string {
		return get_locale();
	}
}
```

For an ordinary helper, use the unguarded declaration shown under [declarations and calls](#declarations-and-calls). Only extension points intended to be overridden receive a guard.

## Classes and file layout

Legacy example from [RequiredVersion](../../../../lib/installer/requirement/RequiredVersion.php). The class name maps to `lib/installer/requirement/RequiredVersion.php`. Backing properties use `$_camelCase`, public methods use camelCase, and the existing untyped signatures remain intact.

```php
<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Installer_Requirement_RequiredVersion implements Abp01_Installer_Requirement {
	private $_currentVersion;

	private $_requiredVersion;

	public function __construct($currentVersion, $requiredVersion) {
		$this->_currentVersion = $currentVersion;
		$this->_requiredVersion = $requiredVersion;
	}

	public function isSatisfied() {
		return version_compare($this->_currentVersion, $this->_requiredVersion, '>=');
	}

	public function getLastError() {
		return null;
	}
}
```

A namespaced file instead wraps its contents in braces. Shortened from [Locale.php](../../../../lib/Locale.php), mapped to `WpTripSummary\Locale` by [Autoloader.php](../../../../lib/Autoloader.php):

```php
<?php
declare(strict_types=1);

namespace WpTripSummary {
	if (!defined('ABP01_LOADED')) {
		exit;
	}

	class Locale {
		public static function getCurrentLocale(): string {
			return get_locale();
		}
	}
}
```

In a complete new PHP source file, insert the license immediately after `<?php`, before `declare` or imports. There is no closing PHP tag. If imports are needed in a namespaced file, place them inside the namespace before the class, as in [Exception.php](../../../../lib/Exception.php); an import or a leading `\` prevents a global class from resolving into `WpTripSummary` accidentally.

## Filter and action hooks

Document an exposed filter's initial value, result handling and argument order beside its call. Adapted from [installer step InstallSchema](../../../../lib/installer/step/InstallSchema.php):

```php
/**
 * Filters custom database table definitions installed alongside the built-in tables.
 * Initial value is an empty array, keyed by table name with CREATE TABLE statements as values.
 * Non-array results are discarded; built-in table definitions take precedence afterwards.
 *
 * @since 0.3.0
 * @category Installer
 *
 * @param array<string, string> $customTables Custom table definitions keyed by table name.
 * @param array<string, string> $ownTables Built-in table definitions keyed by table name.
 */
$customTables = apply_filters('abp01_install_tables_definitions',
	array(),
	$ownTables);

if (!is_array($customTables)) {
	$customTables = array();
}
```

Actions pass context to listeners without assigning a filtered value. This call comes from [RunInstallHook](../../../../lib/installer/service/RunInstallHook.php); its dynamic hook name and surrounding error handling belong to that service's contract:

```php
do_action($this->_hookName, $this->_context);
```

Use callback arrays as shown under [arrays and access](#arrays-and-access), or closures where the surrounding code uses them. When registering a callback that consumes extra hook arguments, keep its signature and the registration's accepted-argument count consistent.

## Templates

Adapted from [lookup management view](../../../../views/wpts-admin-lookup-data-management.php). Keep the `$data` model, alternative control syntax, HTML indentation and contextual escaping. The text domain is `abp01-trip-summary`, independently of class or function naming migrations.

```php
<?php
defined('ABP01_LOADED') or die;

/**
 * @var \stdClass $data
 */
?>
<label for="abp01-lookupTypeSelect"><?php echo esc_html__('Lookup type:', 'abp01-trip-summary'); ?></label>
<select id="abp01-lookupTypeSelect" name="lookupType" class="abp01-lookupControl">
	<?php foreach ($data->controls->availableCategories as $key => $label): ?>
		<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
	<?php endforeach; ?>
</select>
```

## JavaScript and TypeScript

The wrapper provides jQuery as `$`; functions and variables use camelCase and statements end with semicolons. `$me` denotes a jQuery collection. Shortened from [numeric stepper](../../../../media/js/abp01-numeric-stepper.js):

```javascript
(function($) {
	"use strict";

	$.fn.abp01NumericStepper = function(opts) {
		var $me = this;

		opts = $.extend({
			minValue: 1,
			increment: 1
		}, opts);

		function getCurrentValue() {
			return parseInt($me.val(), 10);
		}

		return {
			getValue: getCurrentValue
		};
	};
})(jQuery);
```

TypeScript adds explicit types, tight unions, triple-slash references and companion declarations. Excerpt from [modal declarations](../../../../media/js/components/abp01-modal.d.ts):

```typescript
/// <reference types="jquery" />

interface WpTripSummaryModal {
	show(): void;
	hide(): void;
	findAnd(selector: string, callback: Function): JQuery;
}

interface WpTripSummaryModalOptions {
	trigger: string|null;
	onShow?: Function;
	onHide?: Function;
}
```

The corresponding [modal implementation](../../../../media/js/components/abp01-modal.ts) uses typed locals, template literals and chains:

```typescript
var myId: string|null = $me.get(0)?.id || null;
var mySelector: string = `#${myId}`;

$wrap?.find('.modal-backdrop')
	.fadeIn('slow');
```

Edit `.ts` implementations when present, then use the repository's TypeScript compilation workflow for `.js`. Handwritten `.d.ts` interfaces describe the public surface and may also need corresponding changes; do not treat them all as generated output.

## CSS

One selector per line in a group, one declaration per line and one space after `:`. Adapted from [admin common styles](../../../../media/css/admin/abp01-admin-common.css):

```css
.abp01-bootstrap .form-control.form-select.abp01-select,
.abp01-bootstrap .form-control.abp01-textarea-input,
.abp01-bootstrap .form-control.abp01-text-input {
	min-width: 450px;
	width: 450px;
}
```

Keep the established selector scope and declaration order when modifying a rule; the codebase does not impose alphabetical property sorting.

## Tests

Tests generally extend `WP_UnitTestCase`, use `test_` methods and share helper traits. From [NoneNonceProvider tests](../../../../tests/test-NoneNonceProvider.php):

```php
class NoneNonceProviderTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canGenerateNonce_withResourceId() {
		$provider = new Abp01_NonceProvider_None();
		$this->assertEmpty($provider->generateNonce($this->_generateNonEmptyAscii()));
	}
}
```

Use descriptive scenario suffixes and the existing test helpers. Tests that change WordPress state restore it, as in this method from [FeatureStatus tests](../../../../tests/test-FeatureStatus.php):

```php
public function test_tripSummaryLogCanBeDisabledByFilter(): void {
	$this->_skipWhenFeatureConstantIsDefined();

	$filter = static function($enabled) {
		return false;
	};

	add_filter(self::FILTER_NAME, $filter);
	try {
		$this->assertFalse(FeatureStatus::tripSummaryLogEnabled());
	} finally {
		remove_filter(self::FILTER_NAME, $filter);
	}
}
```

Other suites use `setUp(): void`/`tearDown(): void` and database helper traits, for example [InstallLookupData tests](../../../../tests/test-InstallLookupDataInstallationService.php). Match the suite being extended rather than adding an unrelated test lifecycle.
