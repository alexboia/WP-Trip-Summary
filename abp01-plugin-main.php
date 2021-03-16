<?php
/**
 * Plugin Name: WP Trip Summary
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.2.6
 * Description: Aids a travel blogger to add structured information about his tours (biking, hiking, train travels etc.)
 * License: New BSD License
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Text Domain: abp01-trip-summary
 * Requires PHP: 5.6.2
 * Requires at least: 5.3.0
 * WPTS Version Name: Johannes Honterus
 */

/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

//Check that we're not being directly called
defined('ABSPATH') or die;

//Include plug-in header
require_once __DIR__ . '/abp01-plugin-header.php';
require_once __DIR__ . '/abp01-plugin-functions.php';

/**
 * Retrieve the translated label that corresponds 
 * 	to the given lookup type/category
 * 
 * @param string $type The type for which to retrieve the translated label
 * @return string The translated label
 */
function abp01_get_lookup_type_label($type) {
	$translations = array(
		Abp01_Lookup::BIKE_TYPE => esc_html__('Bike type', 'abp01-trip-summary'),
		Abp01_Lookup::DIFFICULTY_LEVEL => esc_html__('Difficulty level', 'abp01-trip-summary'),
		Abp01_Lookup::PATH_SURFACE_TYPE => esc_html__('Path surface type', 'abp01-trip-summary'),
		Abp01_Lookup::RAILROAD_ELECTRIFICATION => esc_html__('Railroad electrification status', 'abp01-trip-summary'),
		Abp01_Lookup::RAILROAD_LINE_STATUS => esc_html__('Railroad line status', 'abp01-trip-summary'),
		Abp01_Lookup::RAILROAD_LINE_TYPE => esc_html__('Railroad line type', 'abp01-trip-summary'),
		Abp01_Lookup::RAILROAD_OPERATOR => esc_html__('Railroad operators', 'abp01-trip-summary'),
		Abp01_Lookup::RECOMMEND_SEASONS => esc_html__('Recommended seasons', 'abp01-trip-summary')
	);
	return isset($translations[$type]) ? $translations[$type] : null;
}

/**
 * Retrieve translations used when installing the plug-in
 * 
 * @return array key-value pairs where keys are installation error codes and values are the translated strings
 */
function abp01_get_installation_error_translations() {
	$env = abp01_get_env();

	load_plugin_textdomain('abp01-trip-summary', 
		false, 
		dirname(plugin_basename(__FILE__)) . '/lang/');

	return array(
		Abp01_Installer::INCOMPATIBLE_PHP_VERSION 
			=> sprintf(esc_html__('Minimum required PHP version is %s', 'abp01-trip-summary'), $env->getRequiredPhpVersion()), 
		Abp01_Installer::INCOMPATIBLE_WP_VERSION 
			=> sprintf(esc_html__('Minimum required WP version is %s', 'abp01-trip-summary'), $env->getRequiredWpVersion()), 
		Abp01_Installer::SUPPORT_LIBXML_NOT_FOUND 
			=> esc_html__('LIBXML support was not found on your system', 'abp01-trip-summary'), 
		Abp01_Installer::SUPPORT_MYSQLI_NOT_FOUND 
			=> esc_html__('Mysqli extension was not found on your system or is not fully compatible', 'abp01-trip-summary'), 
		Abp01_Installer::SUPPORT_MYSQL_SPATIAL_NOT_FOUND 
			=> esc_html__('MySQL spatial support was not found on your system', 'abp01-trip-summary')
	);
}

/**
 * Handles plug-in activation
 * 
 * @return void
 */
function abp01_activate() {
	if (!current_user_can('activate_plugins')) {
		return;
	}
	$installer = abp01_get_installer();
	$test = $installer->canBeInstalled();
	if ($test !== 0) {
		$errors = abp01_get_installation_error_translations();
		$message = isset($errors[$test]) 
			? $errors[$test] 
			: sprintf('%s (error code: %s)', 
				esc_html__('The plugin cannot be installed on your system', 'abp01-trip-summary'), 
				$test);
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die($message);
	} else if ($test === false) {
		wp_die(abp01_append_error('Failed plug-in compatibility check', $installer->getLastError()), 'Activation error');
	} else {
		if (!$installer->activate()) {
			wp_die(abp01_append_error('Could not activate plug-in', $installer->getLastError()), 'Activation error');
		}
	}
}

/**
 * Handles plug-in deactivation
 * 
 * @return void
 */
function abp01_deactivate() {
	if (!current_user_can('activate_plugins')) {
		return;
	}
	$installer = abp01_get_installer();
	if (!$installer->deactivate()) {
		wp_die(abp01_append_error('Could not deactivate plug-in', $installer->getLastError()), 'Deactivation error');
	}
}

/**
 * Handles plug-in removal
 * 
 * @return void
 */
function abp01_uninstall() {
	if (!current_user_can('activate_plugins')) {
		return;
	}
	$installer = abp01_get_installer();
	if (!$installer->uninstall()) {
		wp_die(abp01_append_error('Could not uninstall plug-in', $installer->getLastError()), 'Uninstall error');
	}
}

function abp01_init_core() {
	Abp01_Includes::configure(__FILE__, true);

	load_plugin_textdomain('abp01-trip-summary', 
		false, 
		dirname(plugin_basename(__FILE__)) . '/lang/');

	abp01_get_view()->initView();
}


function abp01_setup_plugin() {
	abp01_init_core();
	abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);
	abp01_update_if_needed();
	abp01_setup_plugin_modules();
}

function abp01_setup_plugin_modules() {
	$pluginModuleHost = new Abp01_PluginModules_PluginModuleHost(array(
		Abp01_PluginModules_SettingsPluginModule::class,
		Abp01_PluginModules_LookupDataManagementPluginModule::class,
		Abp01_PluginModules_HelpPluginModule::class,
		Abp01_PluginModules_PostListingCustomizationPluginModule::class,
		Abp01_PluginModules_DownloadGpxTrackDataPluginModule::class,
		Abp01_PluginModules_GetTrackDataPluginModule::class,
		Abp01_PluginModules_AdminTripSummaryEditorPluginModule::class,
		Abp01_PluginModules_FrontendViewerPluginModule::class,
		Abp01_PluginModules_TeaserTextsSyncPluginModule::class
	));
	$pluginModuleHost->load();
}

function abp01_update_if_needed() {
	abp01_get_installer()->updateIfNeeded();
}

function abp01_run() {
	register_activation_hook(__FILE__, 'abp01_activate');
	register_deactivation_hook(__FILE__, 'abp01_deactivate');
	register_uninstall_hook(__FILE__, 'abp01_uninstall');

	add_action('plugins_loaded', 'abp01_setup_plugin');
}

//the autoloaders are ready, general!
abp01_init_autoloaders();
abp01_run();