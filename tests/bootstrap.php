<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

define('WPTS_TESTS_ROOT', __DIR__);
define('WPTS_ROOT', realpath(__DIR__ . '/..'));

require_once WPTS_ROOT . '/vendor/autoload.php';

require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01Noop.php';
require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01Die.php';
require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01SendHeader.php';
require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01SetHttpResonseCode.php';
require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01IsUrlRewriteEnabled.php';

require_once WPTS_TESTS_ROOT . '/lib/data/ExpectedLookupData.php';
require_once WPTS_TESTS_ROOT . '/lib/data/GpxTestDataProvider.php';

require_once WPTS_TESTS_ROOT . '/lib/DbTestHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/GenericTestHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/IncludesTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/TestDataFileHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/TestAuthDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/LookupDataTestHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteInfoTestDataSets.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackBboxTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackPathHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackDocumentTestHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackPointTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteTrackAltitudeProfileTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/ViewerTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/SettingsDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/AdminTestDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/GpsDocumentFakerDataProvider.php';
require_once WPTS_TESTS_ROOT . '/lib/GpxDocumentFakerDataProvider.php';
require_once WPTS_TESTS_ROOT . '/lib/GeoJsonDocumentFakerDataProvider.php';
require_once WPTS_TESTS_ROOT . '/lib/IntegerIdGenerator.php';
require_once WPTS_TESTS_ROOT . '/lib/AuditLogDataHelpers.php';
require_once WPTS_TESTS_ROOT . '/lib/TestRouteDataProvider.php';
require_once WPTS_TESTS_ROOT . '/lib/RouteLogTestHelpers.php';

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
	$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: '/tmp/wordpress-tests-lib';
}

if (!is_readable($_tests_dir . '/includes/functions.php')
	|| !is_readable($_tests_dir . '/includes/bootstrap.php')) {
	fwrite(STDERR, sprintf(
		'WordPress test library not found in "%s". Set WP_TESTS_DIR to a directory containing includes/functions.php and includes/bootstrap.php, or run composer install to install wp-phpunit/wp-phpunit.%s',
		$_tests_dir,
		PHP_EOL));
	exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

if (!defined( 'WP_CORE_DIR')) {
	$_wp_core_dir = getenv('WP_CORE_DIR');
	if (!$_wp_core_dir) {
		$_wp_core_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress';
	}
	define('WP_CORE_DIR', $_wp_core_dir);
}

// Detect stale core downloads before WordPress creates or resets any test tables.
$_wp_core_version_file = rtrim(WP_CORE_DIR, '/\\') . '/wp-includes/version.php';
if (!is_readable($_wp_core_version_file)) {
	fwrite(STDERR, 'WordPress test core is missing. Set WP_CORE_DIR to the core version reported by php bin/tools/get-wp-test-version.php.' . PHP_EOL);
	exit(1);
}

$_wp_php_unit_installed_path = realpath(\Composer\InstalledVersions::getInstallPath('wp-phpunit/wp-phpunit'));
if (realpath($_tests_dir) === $_wp_php_unit_installed_path) {
	$_wp_test_version = ltrim(\Composer\InstalledVersions::getPrettyVersion('wp-phpunit/wp-phpunit'), 'v');
	$_wp_core_version = (static function(string $versionFile): string {
		require $versionFile;
		return $wp_version;
	})($_wp_core_version_file);

	// WordPress writes initial major releases as X.Y; Composer uses X.Y.0.
	$_wp_core_version = preg_match('/^\d+\.\d+$/', $_wp_core_version)
		? $_wp_core_version . '.0'
		: $_wp_core_version;
		
	if (version_compare($_wp_core_version, $_wp_test_version, '!=')) {
		fwrite(STDERR, sprintf(
			'WordPress core %s does not match the installed test library %s. Point WP_CORE_DIR at a matching test installation.%s',
			$_wp_core_version,
			$_wp_test_version,
			PHP_EOL));
		exit(1);
	}
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
	define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

function _get_tests_base_dir() {
	return WPTS_TESTS_ROOT;
}

function _get_plugin_base_dir() {
	return WPTS_ROOT;
}

function _manually_load_plugin() {
	require_once _get_plugin_base_dir() . '/abp01-plugin-main.php';
	_override_plugin_db_wrapper();
}

function _override_plugin_db_wrapper() {
	require_once WPTS_TESTS_ROOT . '/lib/MysqliDbTestWrapper.php';
	abp01_get_env()->configureMysqliDbClass(MysqliDbTestWrapper::class);
}

function _manually_install_plugin() {
	// WordPress has already recreated its tables, including plugin options.
	// Reset only the plugin-owned tables that survive that process. A checkout
	// marker cannot tell whether they exist in the currently selected database.
	$env = abp01_get_env();
	$resetSchema = new Abp01_Installer_Step_UninstallSchema($env);
	if (!$resetSchema->execute()) {
		throw new RuntimeException('Failed to reset plugin test tables.');
	}

	$installer = new Abp01_Installer();
	if (!$installer->activate()) {
		throw new RuntimeException('Failed to activate plugin. Cannot continue testing.');
	}

	_include_plugin_dependent_test_classes();
	_include_asserts();
}

function _include_plugin_dependent_test_classes() {
	require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01StubLogger.php';
	require_once WPTS_TESTS_ROOT . '/lib/testDoubles/abp01InvalidStubLogger.php';

	require_once WPTS_TESTS_ROOT . '/lib/TestFrontendTheme.php';
	require_once WPTS_TESTS_ROOT . '/lib/MockDocumentParser.php';
	require_once WPTS_TESTS_ROOT . '/lib/MockFileValidator.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/SamplePluginModuleDependency.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/SamplePluginModuleCallState.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/SamplePluginModuleCreationState.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/NoDependenciesSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/RequiresSupportedDependenciesSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/RequiresAllSupportedDependenciesSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/RequiresOneSupportedDependencySamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/RequiresSomeUnsupportedDependenciesSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/RequiresOnlyUnsupportedDependenciesSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/NotAValidModuleClassSamplePluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/sampleModuleClasses/HasOnlyCustomAvailableDependenciesPluginModule.php';
	require_once WPTS_TESTS_ROOT . '/lib/GpsDocumentFormatTestInfo.php';
	require_once WPTS_TESTS_ROOT . '/lib/GpxDocumentFormatTestInfo.php';
	require_once WPTS_TESTS_ROOT . '/lib/GeoJsonDocumentFormatTestInfo.php';
}

function _include_asserts() {
	spl_autoload_register(function($className) {
		if (strpos($className, 'Assert') === 0) {
			require_once WPTS_TESTS_ROOT . '/lib/assert/' . $className . '.php';
		}
	});
}

function _configure_wp_tests_config() {
	if (defined('WP_TESTS_CONFIG_FILE_PATH')) {
		return;
	}
	$thisConfig = getenv('WP_TESTS_CONFIG_FILE_PATH') ?: _get_tests_base_dir() . '/wp-tests-config.php';

	if (!is_readable($thisConfig)) {
		fwrite(STDERR, sprintf('WordPress test configuration is not readable: %s%s', $thisConfig, PHP_EOL));
		exit(1);
	}
	define('WP_TESTS_CONFIG_FILE_PATH', $thisConfig);
}

function _register_setup_actions() {
	tests_add_filter('muplugins_loaded', '_manually_load_plugin');
	tests_add_filter('setup_theme', '_manually_install_plugin');
}

_configure_wp_tests_config();
_register_setup_actions();

require $_tests_dir . '/includes/bootstrap.php';
