<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

require_once 'faker/autoload.php';
require_once 'mockery/helpers.php';
require_once 'mockery/autoload.php';
require_once 'polyfills/phpunitpolyfills-autoload.php';

require_once 'lib/testDoubles/abp01Noop.php';
require_once 'lib/testDoubles/abp01Die.php';
require_once 'lib/testDoubles/abp01SendHeader.php';
require_once 'lib/testDoubles/abp01SetHttpResonseCode.php';
require_once 'lib/testDoubles/abp01IsUrlRewriteEnabled.php';

require_once 'lib/data/ExpectedLookupData.php';
require_once 'lib/data/GpxTestDataProvider.php';

require_once 'lib/DbTestHelpers.php';
require_once 'lib/GenericTestHelpers.php';
require_once 'lib/IncludesTestDataHelpers.php';
require_once 'lib/TestDataFileHelpers.php';
require_once 'lib/TestAuthDataHelpers.php';
require_once 'lib/LookupDataTestHelpers.php';
require_once 'lib/RouteInfoTestDataSets.php';
require_once 'lib/RouteTrackBboxTestDataHelpers.php';
require_once 'lib/RouteTrackPathHelpers.php';
require_once 'lib/RouteTrackTestDataHelpers.php';
require_once 'lib/RouteTrackDocumentTestHelpers.php';
require_once 'lib/RouteTrackPointTestDataHelpers.php';
require_once 'lib/RouteTrackAltitudeProfileTestDataHelpers.php';
require_once 'lib/ViewerTestDataHelpers.php';
require_once 'lib/SettingsDataHelpers.php';
require_once 'lib/AdminTestDataHelpers.php';
require_once 'lib/GpsDocumentFakerDataProvider.php';
require_once 'lib/GpxDocumentFakerDataProvider.php';
require_once 'lib/GeoJsonDocumentFakerDataProvider.php';
require_once 'lib/IntegerIdGenerator.php';
require_once 'lib/AuditLogDataHelpers.php';
require_once 'lib/TestRouteDataProvider.php';
require_once 'lib/RouteLogTestHelpers.php';

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if (is_dir($_tests_dir)) {
	require_once $_tests_dir . '/includes/functions.php';
} else {
	die('Test directory not found');
}

function _get_tests_base_dir() {
	return __DIR__;
}

function _get_plugin_base_dir() {
	return dirname(__DIR__);
}

function _get_tests_file_path($file) {
	return _get_tests_base_dir() . '/' . $file;
}

function _has_plugin_been_installed_before() {
	return file_exists(_get_tests_file_path('.wpts-installed'));
}

function _set_plugin_installed() {
	file_put_contents(_get_tests_file_path('.wpts-installed'), 'yes');
}

function _manually_load_plugin() {
	require_once _get_plugin_base_dir() . '/abp01-plugin-main.php';
	_override_plugin_db_wrapper();
}

function _override_plugin_db_wrapper() {
	require_once 'lib/MysqliDbTestWrapper.php';
	abp01_get_env()->configureMysqliDbClass(MysqliDbTestWrapper::class);
}

function _manually_install_plugin() {
	$installer = new Abp01_Installer();

	if (_has_plugin_been_installed_before()) {
		if (!$installer->uninstall()) {
			var_dump($installer->getLastError());
			die;
		}
	}

	if (!$installer->activate()) {
		die('Failed to activate plugin. Cannot continue testing.');
	}

	_set_plugin_installed();
	_include_plugin_dependent_test_classes();
	_include_asserts();
}

function _include_plugin_dependent_test_classes() {
	require_once 'lib/testDoubles/abp01StubLogger.php';
	require_once 'lib/testDoubles/abp01InvalidStubLogger.php';

	require_once 'lib/TestFrontendTheme.php';
	require_once 'lib/MockDocumentParser.php';
	require_once 'lib/MockFileValidator.php';
	require_once 'lib/sampleModuleClasses/SamplePluginModuleDependency.php';
	require_once 'lib/sampleModuleClasses/SamplePluginModuleCallState.php';
	require_once 'lib/sampleModuleClasses/SamplePluginModuleCreationState.php';
	require_once 'lib/sampleModuleClasses/NoDependenciesSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/RequiresSupportedDependenciesSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/RequiresAllSupportedDependenciesSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/RequiresOneSupportedDependencySamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/RequiresSomeUnsupportedDependenciesSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/RequiresOnlyUnsupportedDependenciesSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/NotAValidModuleClassSamplePluginModule.php';
	require_once 'lib/sampleModuleClasses/HasOnlyCustomAvailableDependenciesPluginModule.php';
	require_once 'lib/GpsDocumentFormatTestInfo.php';
	require_once 'lib/GpxDocumentFormatTestInfo.php';
	require_once 'lib/GeoJsonDocumentFormatTestInfo.php';
}

function _include_asserts() {
	spl_autoload_register(function($className) {
		if (strpos($className, 'Assert') === 0) {
			require_once 'lib/assert/' . $className . '.php';
		}
	});
}

function _sync_wp_tests_config($testsDir) {
	$thisConfig = _get_tests_base_dir() . '/wp-tests-config.php';
	$runtimeConfig = $testsDir . '/wp-tests-config.php';

	if (is_readable($thisConfig)) {
		echo sprintf('Local wp-tests-config.php found. Overriding %s.%s', 
			$runtimeConfig, 
			PHP_EOL);

		file_put_contents($runtimeConfig, file_get_contents($thisConfig));
	}
}

function _register_setup_actions() {
	tests_add_filter('muplugins_loaded', '_manually_load_plugin');
	tests_add_filter('setup_theme', '_manually_install_plugin');
}

_sync_wp_tests_config($_tests_dir);
_register_setup_actions();

require $_tests_dir . '/includes/bootstrap.php';