<?php
require_once 'faker/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if (is_dir($_tests_dir)) {
	require_once $_tests_dir . '/includes/functions.php';
} else {
	die('Test directory not found');
}

function _get_plugin_base_dir() {
	return dirname(dirname(__FILE__));
}

function _manually_install_plugin() {
	$installer = new Abp01_Installer(false);
	if (!$installer->activate()) {
		echo $installer->getLastError();
	}
}

function _manually_load_plugin() {
	require_once _get_plugin_base_dir() . '/abp01-plugin-main.php';
	_manually_install_plugin();
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
require $_tests_dir . '/includes/bootstrap.php';