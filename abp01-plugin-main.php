<?php
/**
 * Plugin Name: WP Trip Summary
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.2.5
 * Description: Aids a travel blogger to add structured information about his tours (biking, hiking, train travels etc.)
 * License: New BSD License
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Text Domain: abp01-trip-summary
 */

/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
 * Creates a nonce to be used in the trip summary editor
 * 
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_edit_nonce($postId) {
	return wp_create_nonce(ABP01_NONCE_TRIP_SUMMARY_EDITOR . ':' . $postId);
}

/**
 * Creates a nonce to be used when reading a trip's GPX track
 * 
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_get_track_nonce($postId) {
	return wp_create_nonce(ABP01_NONCE_GET_TRACK . ':' . $postId);
}

/**
 * Creates a nonce to be used when downloading a trips' GPX track
 * 
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_download_track_nonce($postId) {
    return wp_create_nonce(ABP01_NONCE_DOWNLOAD_TRACK . ':' . $postId);
}

/**
 * Creates a nonce to be used when saving plugin settings
 * 
 * @return string The created nonce
 */
function abp01_create_edit_settings_nonce() {
	return wp_create_nonce(ABP01_NONCE_EDIT_SETTINGS);
}

/**
 * Creates a nonce to be used when managing look-up data operations
 * 
 * @return string The created nonce
 */
function abp01_create_manage_lookup_nonce() {
	return wp_create_nonce(ABP01_NONCE_MANAGE_LOOKUP);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of track editing
 * 
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_edit_nonce($postId) {
	return check_ajax_referer(ABP01_NONCE_TRIP_SUMMARY_EDITOR . ':' . $postId, 'abp01_nonce', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of reading a trip's GPS track
 * 
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_get_track_nonce($postId) {
	return check_ajax_referer(ABP01_NONCE_GET_TRACK . ':' . $postId, 'abp01_nonce_get', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of downloading a trip's GPS track
 * 
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_download_track_nonce($postId) {
    return check_ajax_referer(ABP01_NONCE_DOWNLOAD_TRACK . ':' . $postId, 'abp01_nonce_download', false);
}

/**
 * Checks whether the current request has a valid edit settings nonce
 * 
 * @return bool True if valid, false otherwise
 */
function abp01_verify_edit_settings_nonce() {
	return check_ajax_referer(ABP01_NONCE_EDIT_SETTINGS, 'abp01_nonce_settings', false);
}

/**
 * Checks whether the current request has a valid lookup data management nonce
 * 
 * @return bool True if valid, false otherwise
 */
function abp01_verify_manage_lookup_nonce() {
	return check_ajax_referer(ABP01_NONCE_MANAGE_LOOKUP, 'abp01_nonce_lookup_mgmt', false);	
}

/**
 * Checks whether an options saving operations is currently underway
 * 
 * @return bool
 * */
function abp01_is_saving_wp_options() {
	return abp01_get_env()->isSavingWpOptions();
}

/**
 * Checks whether the admin posts listing is currently being browsed (regardless of post ype)
 * 
 * @return bool
 */
function abp01_is_browsing_posts_listing() {
	return abp01_get_env()->isListingWpPosts();
}

/**
 * Check whether the currently displayed screen is either the post editing or the post creation screen
 * 
 * @return bool
 */
function abp01_is_editing_post() {
	$env = abp01_get_env();
	if (func_num_args() > 0) {
		return call_user_func_array(array($env, 'isEditingWpPost'), func_get_args());
	} else {
		return $env->isEditingWpPost();
	}
}

/**
 * Check whether the currently displayed screen is the plugin settings management screen
 * 
 * @return boolean True if on plugin settings page, false otherwise
 */
function abp01_is_editing_plugin_settings() {
	return abp01_get_env()->isAdminPage(ABP01_MAIN_MENU_SLUG);
}

/**
 * Check whether the currently displayed screen is the plugin lookup data management screen
 * 
 * @return booelan True if on plugin lookup data management page, false otherwise
 */
function abp01_is_managing_lookup() {
	return abp01_get_env()->isAdminPage(ABP01_LOOKUP_SUBMENU_SLUG);
}

/**
 * Check whether the currently displayed screen is the help page
 * 
 * @return booelan True if on plugin lookup help page, false otherwise
 */
function abp01_is_browsing_help() {
	return abp01_get_env()->isAdminPage(ABP01_HELP_SUBMENU_SLUG);
}

/**
 * Tries to infer the current post ID from the current context. Several paths are tried:
 *  - The global $post object
 *  - The value of the _GET 'post' parameter
 *  - The value of the _GET 'abp01_postId' post parameter
 * 
 * @return mixed Int if a post ID is found, null otherwise
 */
function abp01_get_current_post_id() {
	return abp01_get_env()->getCurrentPostId('abp01_postId');
}

function abp01_get_current_post_content() {
	$postId = abp01_get_current_post_id();
	return !empty($postId) 
		? get_the_content(null, false, $postId) 
		: null;
}

/**
 * Checks whether the current user can edit the current post's trip summary details.
 * If null is given, the function tries to infer the current post ID from the current context
 * 
 * @param mixed The current post as either a WP_Post instance, an integer or a null value
 * @return bool True if can edit, false otherwise
 */
function abp01_can_edit_trip_summary($post = null) {
	if ($post && is_object($post)) {
		$postId = intval($post->ID);
	} else if ($post && is_numeric($post)) {
		$postId = $post;
	} else {
		$postId = abp01_get_current_post_id();
	}
	return Abp01_Auth::getInstance()->canEditTripSummary($postId);
}

/**
 * Checkes whether the current user has the permission to manage plug-in settings
 * 
 * @return boolean True if it has permission, false otherwise
 */
function abp01_can_manage_plugin_settings() {
	return Abp01_Auth::getInstance()->canManagePluginSettings();
}

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
 * Retrieve translations used in the main editor script
 * 
 * @return array key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_main_admin_script_translations() {
	return array(
		'btnClearInfo' => esc_html__('Clear info', 'abp01-trip-summary'), 
		'btnClearTrack' => esc_html__('Clear track', 'abp01-trip-summary'), 
		'lblPluploadFileTypeSelector' => esc_html__('GPX files', 'abp01-trip-summary'), 
		'lblGeneratingPreview' => esc_html__('Generating preview. Please wait...', 'abp01-trip-summary'), 
		'lblTrackUploadingWait' => esc_html__('Uploading track', 'abp01-trip-summary'), 
		'lblTrackUploaded' => esc_html__('The track has been uploaded and saved successfully', 'abp01-trip-summary'), 
		'lblTypeBiking' => esc_html__('Biking', 'abp01-trip-summary'), 
		'lblTypeHiking' => esc_html__('Hiking', 'abp01-trip-summary'), 
		'lblTypeTrainRide' => esc_html__('Train ride', 'abp01-trip-summary'), 
		'lblClearingTrackWait' => esc_html__('Clearing track. Please wait...', 'abp01-trip-summary'), 
		'lblTrackClearOk' => esc_html__('The track has been successfully cleared', 'abp01-trip-summary'), 
		'lblTrackClearFail' => esc_html__('The data could not be updated', 'abp01-trip-summary'), 
		'lblTrackClearFailNetwork' => esc_html__('The data could not be updated due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
		'lblSavingDataWait' => esc_html__('Saving data. Please wait...', 'abp01-trip-summary'), 
		'lblDataSaveOk' => esc_html__('The data has been saved', 'abp01-trip-summary'), 
		'lblDataSaveFail' => esc_html__('The data could not be saved', 'abp01-trip-summary'), 
		'lblDataSaveFailNetwork' => esc_html__('The data could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
		'lblClearingInfoWait' => esc_html__('Clearing trip info. Please wait...', 'abp01-trip-summary'), 
		'lblClearInfoOk' => esc_html__('The trip info has been cleared', 'abp01-trip-summary'), 
		'lblClearInfoFail' => esc_html__('The trip info could not be cleared', 'abp01-trip-summary'), 
		'lblClearInfoFailNetwork' => esc_html__('The trip info could not be cleared due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
		'errPluploadTooLarge' => esc_html__('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'), 
		'errPluploadFileType' => esc_html__('The selected file type is not valid. Only GPX files are allowed', 'abp01-trip-summary'), 
		'errPluploadIoError' => esc_html__('The file could not be read', 'abp01-trip-summary'), 
		'errPluploadSecurityError' => esc_html__('The file could not be read', 'abp01-trip-summary'), 
		'errPluploadInitError' => esc_html__('The uploader could not be initialized', 'abp01-trip-summary'), 
		'errPluploadHttp' =>  esc_html__('The file could not be uploaded', 'abp01-trip-summary'), 
		'errServerUploadFileType' =>  esc_html__('The selected file type is not valid. Only GPX files are allowed', 'abp01-trip-summary'), 
		'errServerUploadTooLarge' =>  esc_html__('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'), 
		'errServerUploadNoFile' =>  esc_html__('No file was uploaded', 'abp01-trip-summary'), 
		'errServerUploadInternal' =>  esc_html__('The file could not be uploaded due to a possible internal server issue', 'abp01-trip-summary'), 
		'errServerUploadStoreFailed' => esc_html__('The file could not be stored on the server (#2). This usually indicates an internal server issue.', 'abp01-trip-summary'),
		'errServerUploadStoreInitiationFailed' => esc_html__('The file could not be stored on the server (#1). This usually indicates an internal server issue.', 'abp01-trip-summary'),
		'errServerUploadInvalidUploadParams' => esc_html__('The upload request contains some invalid parameters. This might indicate an error within the plug-in itself or an attempt to forge the request.', 'abp01-trip-summary'),
		'errServerUploadDestinationFileNotFound' => esc_html__('The destination were the track file was uploaded cannot be found. This usually indicates an internal server issue.', 'abp01-trip-summary'),
		'errServerUploadDestinationFileCorrupt' => esc_html__('The destination were the track file was uploaded has been found, but is corrupt. This usually indicates a problem with the file itself or, less likely, an internal server issue.', 'abp01-trip-summary'),
		'errServerUploadFail' =>  esc_html__('The file could not be uploaded', 'abp01-trip-summary'),
		'errServerCustomValidationFail' => esc_html__('The uploaded file was not a valid GPX file', 'abp01-trip-summary'), 
		'selectBoxPlaceholder' => esc_html__('Choose options', 'abp01-trip-summary'),
		'selectBoxCaptionFormat' => esc_html__('{0} selected', 'abp01-trip-summary'),
		'selectBoxSelectAllText' => esc_html__('Select all', 'abp01-trip-summary'),
		'lblStatusTextTripSummaryInfoPresent' => esc_html__('Trip summary information is present for this post', 'abp01-trip-summary'),
		'lblStatusTextTripSummaryInfoNotPresent' => esc_html__('Trip summary information is not present for this post', 'abp01-trip-summary'),
		'lblStatusTextTripSummaryTrackPresent' => esc_html__('Trip summary track is present for this post', 'abp01-trip-summary'),
		'lblStatusTextTripSummaryTrackNotPresent' => esc_html__('Trip summary track is not present for this post', 'abp01-trip-summary')
	);
}

/**
 * Retrieve translations used in the settings editor script
 * 
 * @return array Key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_settings_admin_script_translations() {
	return array(
		'errSaveFailNetwork' => esc_html__('The settings could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
		'errSaveFailGeneric' => esc_html__('The settings could not be saved due to a possible internal server issue', 'abp01-trip-summary'), 
		'msgSaveOk' => esc_html__('Settings successfully saved', 'abp01-trip-summary'), 
		'msgSaveWorking' => esc_html__('Saving settings. Please wait...', 'abp01-trip-summary')
	);
}

/**
 * Retrieve translations used in the settings editor script
 * 
 * @return array Key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_lookup_admin_script_translations() {
	return array(
		'msgWorking' => esc_html__('Working. Please wait...', 'abp01-trip-summary'),
		'msgSaveOk' => esc_html__('Item successfully saved', 'abp01-trip-summary'),
		'addItemTitle' => esc_html__('Add new item', 'abp01-trip-summary'),
		'editItemTitle' => esc_html__('Modify item', 'abp01-trip-summary'),
		'errFailNetwork' => esc_html__('The item could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'),
		'errFailGeneric' => esc_html__('The item could not be saved due to a possible internal server issue', 'abp01-trip-summary'),
		'ttlConfirmDelete' => esc_html__('Confirm item removal', 'abp01-trip-summary'),
		'errDeleteFailedNetwork' => esc_html__('The item could not be deleted due to a possible network error or an internal server issue', 'abp01-trip-summary'),
		'errDeleteFailedGeneric' => esc_html__('The item could not be deleted due to a possible internal server issue', 'abp01-trip-summary'),
		'msgDeleteOk' => esc_html__('The item has been successfully deleted', 'abp01-trip-summary'),
		'errListingFailNetwork' => esc_html__('The lookup items could not be loaded due to a possible network error or an internal server issue', 'abp01-trip-summary'),
		'errListingFailGeneric' => esc_html__('The lookup items could not be loaded', 'abp01-trip-summary')
	);
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
 * Retrieve translations used in the main frontend script
 * 
 * @return array key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_main_frontend_translations() {
	return array(
		'lblMinAltitude' => esc_html__('Minimum altitude:', 'abp01-trip-summary'),
		'lblMaxAltitude' => esc_html__('Maximum altitude:', 'abp01-trip-summary'),
		'lblAltitude' => esc_html__('Altitude:', 'abp01-trip-summary'),
		'lblDistance' => esc_html__('Distance:', 'abp01-trip-summary')
	);
}

/**
 * Render the editour launcher metabox, 
 * 	in the post creation or post edit screen
 * 
 * @param stdClass $data Context data
 * @return void
 */
function abp01_render_trip_summary_launcher_metabox(stdClass $data) {
	return abp01_get_view()->renderAdminTripSummaryEditorLauncherMetabox($data);
}

/**
 * Renders the editor in the post creation or post edit screen
 * 
 * @param stdClass $data The existing trip summary and context data
 * @return void
 */
function abp01_render_admin_trip_summary_editor(stdClass $data) {
	return abp01_get_view()->renderAdminTripSummaryEditor($data);
}

/**
 * Renders the plugin settings editor page
 * 
 * @param stdClass $data The settings context and existing settings values
 * @return void
 */
function abp01_admin_settings_page_render(stdClass $data) {
	return abp01_get_view()->renderAdminSettingsPage($data);
}

/**
 * Renders the plugin help page
 * 
 * @param stdClass $data The data with the help contents
 * @return void
 */
function abp01_admin_help_page_render(stdClass $data) {
	return abp01_get_view()->renderAdminHelpPage($data);
}

/**
 * Renders the plugin lookup data management page
 * 
 * @param stdClass $data The lookup data management page context and the actual data
 * @return void
 */
function abp01_admin_lookup_page_render(stdClass $data) {
	return abp01_get_view()->renderAdminLookupPage($data);
}

/**
 * Builds the URL to the lookup data management page, 
 * 	in the context of the given lookup type
 * 
 * @param string $lookupType The lookup-type for which to load the list of items when first loading the page
 * @return string The generated URL
 */
function abp01_get_admin_lookup_url($lookupType) {
	$url = menu_page_url(ABP01_LOOKUP_SUBMENU_SLUG, false);
	if (!empty($lookupType)) {
		$url = sprintf('%s&abp01_type=%s', $url, $lookupType);
	}
	return $url;
}

/**
 * Creates the plug-in administration menu structure
 * 
 * @return void
 */
function abp01_create_admin_menu() {
	//add main menu entry
	add_menu_page(
		esc_html__('Trip Summary Settings', 'abp01-trip-summary'),  //page title
		esc_html__('Trip Summary', 'abp01-trip-summary'), //menu title
			Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, //required page capability
			ABP01_MAIN_MENU_SLUG, //menu slug - unique handle for this menu
				'abp01_admin_settings_page', //callback for rendering the page
				'dashicons-chart-area', //icon css class
					81);

	//add submenu entries - the submenu settings page
	add_submenu_page(
		ABP01_MAIN_MENU_SLUG, 
			esc_html__('Trip Summary Settings', 'abp01-trip-summary'), 
			esc_html__('Settings', 'abp01-trip-summary'), 
				Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, 
				ABP01_MAIN_MENU_SLUG,
					'abp01_admin_settings_page');

	//add submenu entries - loookup data management apge
	add_submenu_page(
		ABP01_MAIN_MENU_SLUG, 
			esc_html__('Lookup data management', 'abp01-trip-summary'), 
			esc_html__('Lookup data management', 'abp01-trip-summary'), 
				Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, 
				ABP01_LOOKUP_SUBMENU_SLUG, 
					'abp01_admin_lookup_page');
	
	add_submenu_page(ABP01_MAIN_MENU_SLUG, 
		esc_html__('Help', 'abp01-trip-summary'), 
		esc_html__('Help', 'abp01-trip-summary'), 
			Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, 
			ABP01_HELP_SUBMENU_SLUG, 
				'abp01_admin_help_page');
}

/**
 * Render the trip summary viewer
 * 
 * @param stdClass $data The trip summary and context data
 * @return void
 */
function abp01_render_trip_summary_frontend(stdClass $data) {
	return abp01_get_view()->renderFrontendViewer($data);
}

/**
 * Render the trip summary teaser
 * 
 * @param stdClass $data The trip summary and context data
 */
function abp01_render_trip_summary_frontend_teaser(stdClass $data) {	
	return abp01_get_view()->renderFrontendTeaser($data);
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
	Abp01_Includes::setRefPluginsPath(__FILE__);
	Abp01_Includes::setScriptsInFooter(true);

	load_plugin_textdomain('abp01-trip-summary', 
		false, 
		dirname(plugin_basename(__FILE__)) . '/lang/');

	abp01_get_view()->initView();
}

function abp01_init_viewer_content_hooks() {
	$priority = has_filter('the_content', 'wpautop');
	if ($priority !== false) {
		remove_filter('the_content', 'wpautop', $priority);
	}
	
	add_filter('the_content', 'abp01_add_viewer', 0);
}

/**
 * Run plug-in init sequence
 */
function abp01_init_plugin() {
	//configure script&styles includes and load the text domain
	abp01_init_core();

	//check if update is needed
	abp01_get_installer()->updateIfNeeded();

	//init content hooks
	abp01_init_viewer_content_hooks();	

	//register blocks only if classic editor plug-in is not active
	if (!abp01_is_editor_classic_active()) {
		abp01_init_block_editor_blocks();
	}
}

function abp01_render_trip_summary_shortcode_block($attributes, $content) {
	static $rendered = false;
	if ($rendered === false || !doing_filter('the_content')) {
		$rendered = true;
		return '<div class="abp01-viewer-shortcode-block">' . ('[' . ABP01_VIEWER_SHORTCODE . ']') . '</div>';
	} else {
		return '';
	}
}

function abp01_init_block_editor_blocks() {
	if (function_exists('register_block_type')) {
		Abp01_Includes::includeScriptBlockEditorViewerShortCodeBlock();
		register_block_type('abp01/block-editor-shortcode', array(
			'editor_script' => 'abp01-viewer-short-code-block',
			//we need server side rendering to account for 
			//	potential changes of the configured shortcode tag name
			'render_callback' => 'abp01_render_trip_summary_shortcode_block'
		));
	}
}

function abp01_register_classic_editor_settings($settings) {
	//ponly register settings if the classic editor is active
	if (abp01_is_editor_classic_active()) {
		$settings['abp01_viewer_short_code_name'] = ABP01_VIEWER_SHORTCODE;
	}
	return $settings;
}

function abp01_register_classic_editor_buttons($buttons) {
	//only register buttons if classic editor is active
	if (abp01_is_editor_classic_active()) {
		$buttons = array_merge($buttons, array(
			'abp01_insert_viewer_shortcode'
		));
	}

	return $buttons;
}

function abp01_register_classic_editor_plugins($plugins) {
	if (abp01_is_editor_classic_active()) {
		$plugins['abp01_viewer_shortcode'] = Abp01_Includes::getClassicEditorViewerShortcodePluginUrl();
	}
	return $plugins;
}

function abp01_register_metaboxes($postType, $post) {
	if (in_array($postType, array('post', 'page')) && abp01_can_edit_trip_summary($post)) {
		add_meta_box('abp01-enhanced-editor-launcher-metabox', 
			__('Trip summary', 'abp01-trip-summary'),
			'abp01_add_admin_editor', 
			$postType, 
			'side', 
			'high', 
			array(
				'postType' => $postType,
				'post' => $post
			));
	}
}

function abp01_add_admin_editor($post, $args) {
	if (abp01_can_edit_trip_summary($post)) {
		abp01_add_admin_editor_form($post, $args);
		abp01_add_admin_editor_launcher($post, $args);
	}
}

function abp01_add_admin_editor_launcher($post, $args) {
	$postId = intval($post->ID);
	$routeManager = abp01_get_route_manager();

	$data = new stdClass();
	$data->postId = $postId;
	$data->hasRouteTrack = $routeManager->hasRouteTrack($postId);
	$data->hasRouteInfo = $routeManager->hasRouteInfo($postId);

	$data->trackDownloadUrl = add_query_arg(array(
		'action' => ABP01_ACTION_DOWNLOAD_TRACK,
		'abp01_nonce_download' => abp01_create_download_track_nonce($postId),
		'abp01_postId' => $postId,
		'_cb' => abp01_get_cachebuster()
	), abp01_get_ajax_baseurl());

	echo abp01_render_trip_summary_launcher_metabox($data);
}

/**
 * Adds the editor in the post creation or post edit screen
 * 
 * @param object $post The current post being created or modified
 * @return void
 */
function abp01_add_admin_editor_form($post, $args) {
	$data = new stdClass();
	$lookup = new Abp01_Lookup();
	$manager = abp01_get_route_manager();

	//get the lookup data
	$data->difficultyLevels = $lookup->getDifficultyLevelOptions();
	$data->difficultyLevelsAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::DIFFICULTY_LEVEL);

	$data->pathSurfaceTypes = $lookup->getPathSurfaceTypeOptions();
	$data->pathSurfaceTypesAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::PATH_SURFACE_TYPE);

	$data->recommendedSeasons = $lookup->getRecommendedSeasonsOptions();
	$data->recommendedSeasonsAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::RECOMMEND_SEASONS);

	$data->bikeTypes = $lookup->getBikeTypeOptions();
	$data->bikeTypesAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::BIKE_TYPE);

	$data->railroadOperators = $lookup->getRailroadOperatorOptions();
	$data->railroadOperatorsAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::RAILROAD_OPERATOR);

	$data->railroadLineStatuses = $lookup->getRailroadLineStatusOptions();
	$data->railroadLineStatusesAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::RAILROAD_LINE_STATUS);

	$data->railroadLineTypes = $lookup->getRailroadLineTypeOptions();
	$data->railroadLineTypesAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::RAILROAD_LINE_TYPE);

	$data->railroadElectrification = $lookup->getRailroadElectrificationOptions();
	$data->railroadElectrificationAdminUrl = abp01_get_admin_lookup_url(Abp01_Lookup::RAILROAD_ELECTRIFICATION);

	//current context information
	$data->postId = intval($post->ID);
	$data->hasTrack = $manager->hasRouteTrack($post->ID);

	$data->ajaxEditInfoAction = ABP01_ACTION_EDIT;
	$data->ajaxUploadTrackAction = ABP01_ACTION_UPLOAD_TRACK;
	$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;	
	$data->ajaxClearTrackAction = ABP01_ACTION_CLEAR_TRACK;
	$data->ajaxClearInfoAction = ABP01_ACTION_CLEAR_INFO;
	$data->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;

	$data->nonce = abp01_create_edit_nonce($data->postId);
	$data->nonceGet = abp01_create_get_track_nonce($data->postId);	
	$data->nonceDownload = abp01_create_download_track_nonce($data->postId);

	$data->ajaxUrl = abp01_get_ajax_baseurl();
	$data->imgBaseUrl = plugins_url('media/img', __FILE__);
	$data->flashUploaderUrl = includes_url('js/plupload/plupload.flash.swf');
	$data->xapUploaderUrl = includes_url('js/plupload/plupload.silverlight.xap');

	$data->uploadMaxFileSize = ABP01_TRACK_UPLOAD_MAX_FILE_SIZE;
	$data->uploadChunkSize = ABP01_TRACK_UPLOAD_CHUNK_SIZE;
	$data->uploadKey = ABP01_TRACK_UPLOAD_KEY;

	//the already existing values
	$info = $manager->getRouteInfo($data->postId);
	if ($info instanceof Abp01_Route_Info) {
		$data->tourInfo = $info->getData();
		$data->tourType = $info->getType();
	} else {
		$data->tourType = null;
		$data->tourInfo = null;
	}

	//finally, render the editor	
	echo abp01_render_admin_trip_summary_editor($data);
}

/**
 * Checks whether the WPTS admin editor should be included, given the current context:
 * 	- current page (must be post editing screen);
 * 	- current type of post being edited (must be 'page' or 'post');
 * 	- current user permissions (must have the permission to edit trip summary for current post).
 * 
 * @return boolean True if so, false otherwise
 */
function abp01_should_add_admin_editor() {
	static $addEditor = null;

	if ($addEditor === null) {
		$addEditor = abp01_is_editing_post('post', 'page') 
			&& abp01_can_edit_trip_summary(null);
	}

	return $addEditor;
}

/**
 * Checks whether the WPTS viewer should be included, given the current context:
 * 	- current page (must be a post viewing screen);
 * 	- current type of post being viewed (must be 'page' or 'post');
 * 	- current status of WPTS data for current post (must have route details, or route track, or both).
 * 
 * @return boolean True if so, false otherwise
 */
function abp01_should_add_frontend_viewer() {
	static $addViewer = null;

	if ($addViewer === null) {
		$addViewer = false;
		if (is_single() || is_page()) {
			$postId = abp01_get_current_post_id();
			$statusInfo = abp01_get_route_manager()->getTripSummaryStatusInfo($postId);

			if (!empty($statusInfo[$postId])) {
				$statusInfo = $statusInfo[$postId];
				$addViewer = ($statusInfo['has_route_details'] || $statusInfo['has_route_track']);
			}
		}
	}

	return $addViewer;
}

/**
 * Queues the appropriate styles with respect to the current admin screen
 * 
 * @return void
 */
function abp01_add_admin_styles() {
	if (abp01_is_browsing_posts_listing()) {
		Abp01_Includes::includeStyleAdminPostsListing();
	}

	//if in post editing page and IF the user is allowed to edit a post's trip summary
	//include the the styles required by the trip summary editor
	if (abp01_should_add_admin_editor()) {
		Abp01_Includes::includeStyleAdminMain();
	}

	//if in plug-in editing page and IF the user is allowed to edit the plug-in's settings
	//include the styles required by the settings editor
	if (abp01_is_editing_plugin_settings() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeStyleAdminSettings();
	}

	if (abp01_is_managing_lookup() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeStyleAdminLookupManagement();
	}
	
	if (abp01_is_browsing_help() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeStyleAdminHelp();
	}
}

/**
 * Queues the appropriate frontend styles with 
 * 	respect to the current frontend screen
 * 
 * @return void
 */
function abp01_add_frontend_styles() {
	if (abp01_should_add_frontend_viewer()) {
		abp01_get_view()->includeFrontendViewerStyles();
	}
}

/**
 * Queues the appropriate scripts with respect 
 * 	to the current admin screen
 * 
 * @return void
 */
function abp01_add_admin_scripts() {
	if (abp01_should_add_admin_editor()) {
		Abp01_Includes::includeScriptAdminEditorMain(true, abp01_get_main_admin_script_translations());
	}

	if (abp01_is_editing_plugin_settings() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeScriptAdminSettings(abp01_get_settings_admin_script_translations());
	}

	if (abp01_is_managing_lookup() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeScriptAdminLookupMgmt(abp01_get_lookup_admin_script_translations());
	}
}

/**
 * Queues the appropriate frontend scripts 
 * 	with respect to the current frontend screen
 * 
 * @return void
 */
function abp01_add_frontend_scripts() {
	if (abp01_should_add_frontend_viewer()) {
		abp01_get_view()->includeFrontendViewerScripts(abp01_get_main_frontend_translations());
	}
}

/**
 * Prepares the required data and renders 
 * 	the plug-in's settings administration page.
 * If the current user does not have 
 * 	the required permissions to manage the plug-in, 
 * 	then the function returns directly.
 * 
 * @return void
 * */
function abp01_admin_settings_page() {
	//check if the current user is allowed to access the settings page
	if (!abp01_can_manage_plugin_settings()) {
		return;
	}

	//init data and populate execution context
	$data = new stdClass();
	$data->nonce = abp01_create_edit_settings_nonce();
	$data->ajaxSaveAction = ABP01_ACTION_SAVE_SETTINGS;
	$data->ajaxUrl = abp01_get_ajax_baseurl();

	//fetch and process tile layer information
	$settings = abp01_get_settings();
	$data->settings = $settings->asPlainObject();

	echo abp01_admin_settings_page_render($data);
}

/**
 * This function handles the plug-in settings save action. 
 * It receives and processes the corresponding HTTP request.
 * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - no valid nonce detected or...
 * - current user lacks proper capabilities
 * 
 * @return void
 * */
function abp01_save_admin_settings_page_save() {
	//only HTTP POST methods are allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}
		
	//current user must have the right to edit plugin settings
	//and the received nonce must be valid
	if (!abp01_can_manage_plugin_settings() || !abp01_verify_edit_settings_nonce()) {
		die;
	}

	//initialize response data
	$response = abp01_get_ajax_response();

	//check that given unit system is supported
	$unitSystem = Abp01_InputFiltering::getFilteredPOSTValue('unitSystem');
	if (!Abp01_UnitSystem::isSupported($unitSystem)) {
		$response->message = esc_html__('Unsupported unit system', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//collect and fill in layer parameters
	$tileLayer = new stdClass();
	$tileLayer->url = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerUrl');
	$tileLayer->attributionUrl = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerAttributionUrl');
	$tileLayer->attributionTxt = Abp01_InputFiltering::getFilteredPOSTValue('tileLayerAttributionTxt');

	//tile layer URL must not be empty
	if (empty($tileLayer->url)) {
		$response->message = esc_html__('Tile layer URL is required', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//check tile layer URL format
	$tileLayerUrlValidator = new Abp01_Validate_TileLayerUrl();
	if (!$tileLayerUrlValidator->validate($tileLayer->url)) {
		$response->message = esc_html__('Tile layer URL does not have a valid format', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//check tile layer attribution URL; empty values are allowed
	$urlValidator = new Abp01_Validate_Url(true);
	if (!$urlValidator->validate($tileLayer->attributionUrl)) {
		$response->message = esc_html__('Tile layer attribution URL does not have a valid format', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//collect the track line colour
	$trackLineColour = Abp01_InputFiltering::getFilteredPOSTValue('trackLineColour');

	//check whether the track line colour is a valid hex value
	$hexColourValidator = new Abp01_Validate_HexColourCode(true);
	if (!$hexColourValidator->validate($trackLineColour)) {
		$response->message = esc_html__('The track line colour is not a valid HEX colour code', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//collect track line weight
	$trackLineWeight = max(1, Abp01_InputFiltering::getPOSTValueAsInteger('trackLineWeight', 3));

	//fill in and save settings
	$settings = abp01_get_settings();
	$settings->setShowTeaser(Abp01_InputFiltering::getPOSTValueAsBoolean('showTeaser'));
	$settings->setTopTeaserText(Abp01_InputFiltering::getFilteredPOSTValue('topTeaserText'));
	$settings->setBottomTeaserText(Abp01_InputFiltering::getFilteredPOSTValue('bottomTeaserText'));

	$settings->setShowFullScreen(Abp01_InputFiltering::getPOSTValueAsBoolean('showFullScreen'));
	$settings->setShowMagnifyingGlass(Abp01_InputFiltering::getPOSTValueAsBoolean('showMagnifyingGlass'));
	$settings->setShowMapScale(Abp01_InputFiltering::getPOSTValueAsBoolean('showMapScale'));
	$settings->setAllowTrackDownload(Abp01_InputFiltering::getPOSTValueAsBoolean('allowTrackDownload'));
	$settings->setTrackLineColour($trackLineColour);
	$settings->setTrackLineWeight($trackLineWeight);
	$settings->setShowMinMaxAltitude(Abp01_InputFiltering::getPOSTValueAsBoolean('showMinMaxAltitude'));
	$settings->setShowAltitudeProfile(Abp01_InputFiltering::getPOSTValueAsBoolean('showAltitudeProfile'));

	$settings->setTileLayers($tileLayer);
	$settings->setUnitSystem($unitSystem);

	if ($settings->saveSettings()) {
		$response->success = true;
	} else {
		$response->message = esc_html__('The settings could not be saved. Please try again.', 'abp01-trip-summary');
	}

	abp01_send_json($response);
}

/**
 * This function handles the admin help page. 
 * The execution halts if the user lacks 
 * 	the proper capabilities
 * 
 * @return void
 */
function abp01_admin_help_page() {
	if (!abp01_can_manage_plugin_settings()) {
		return;
	}

	$data = new stdClass();	
	$data->helpContents = abp01_get_help()->getHelpContentForCurrentLocale();
	
	echo abp01_admin_help_page_render($data);
}

/**
 * Prepares the required data and renders 
 * 	the plug-in's lookup data management page
 * If the current user does not have 
 * 	the required permissions to manage the plug-in, 
 * 	then the function returns directly.
 * 
 * @return void
 * */
function abp01_admin_lookup_page() {
	if (!abp01_can_manage_plugin_settings()) {
		return;
	}
	
	//set available lookup categories/types
	$data = new stdClass();
	$data->controllers = new stdClass();
	$data->controllers->availableTypes = array();
	foreach (Abp01_Lookup::getSupportedCategories() as $category) {
		$data->controllers->availableTypes[$category] = abp01_get_lookup_type_label($category);
	}

	//set available languages
	$data->controllers->availableLanguages = Abp01_Lookup::getSupportedLanguages();

	//set selected language
	if (!empty($_GET['abp01_lang']) && array_key_exists($_GET['abp01_lang'], $data->controllers->availableLanguages)) {
		$data->controllers->selectedLanguage = $_GET['abp01_lang'];
	} else {
		$data->controllers->selectedLanguage = '_default';
	}
	
	//set selected category/type
	if (!empty($_GET['abp01_type']) && array_key_exists($_GET['abp01_type'], $data->controllers->availableTypes)) {
		$data->controllers->selectedType = $_GET['abp01_type'];
	} else {
		$data->controllers->selectedType = current(array_keys($data->controllers->availableTypes));
	}

	//set current context
	$data->context = new stdClass();
	$data->context->nonce = abp01_create_manage_lookup_nonce();
	$data->context->getLookupAction = ABP01_ACTION_GET_LOOKUP;
	$data->context->addLookupAction = ABP01_ACTION_ADD_LOOKUP;
	$data->context->editLookupAction = ABP01_ACTION_EDIT_LOOKUP;
	$data->context->deleteLookupAction = ABP01_ACTION_DELETE_LOOKUP;
	$data->context->ajaxBaseUrl = abp01_get_ajax_baseurl();

	//render the page
	echo abp01_admin_lookup_page_render($data);
}

/**
 * Retrieves the list of lookup items for the given lookup item type and language.
 * Lookup item type and language are passed as URl parameters
 * Execution halts if the given request context is not valid:
 * - invalit HTTP method or...
 * - no valid nonce detected or...
 * - the current user lacks proper capabilities or...
 * - one of the above-mentioned parameters is empty or...
 * - the given lookup item type is not supported
 * 
 * @return void
 */
function abp01_get_lookup_items() {
	//check http method - must be GET
	if (abp01_get_http_method() != 'get') {
		die;
	}

	//check acces rights and look for valid nonce
	if (!abp01_can_manage_plugin_settings() || !abp01_verify_manage_lookup_nonce()) {
		die;
	}

	$type = Abp01_InputFiltering::getGETvalueOrDie('type', array('Abp01_Lookup', 'isTypeSupported'));
	$lang = Abp01_InputFiltering::getGETvalueOrDie('lang', array('Abp01_Lookup', 'isLanguageSupported'));

	$lookup = new Abp01_Lookup($lang);
	$items = $lookup->getLookupOptions($type);

	$response = abp01_get_ajax_response(array(
		'lang' => $lang,
		'type' => $type,
		'items' => $items,
		'success' => true
	));

	abp01_send_json($response);
}

/**
 * Handles the lookup item creation save action. 
 * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - the current user does not have the required permissions or...
 * - no valid nonce was found or...
 * - the required lang & type parameters were not provided.
 * 
 * @return void
 */
function abp01_add_lookup_item() {
	//check HTTP method - must be POST
	if (abp01_get_http_method() != 'post') {
		die;
	}

	//check access rights and look for valid nonce
	if (!abp01_can_manage_plugin_settings() || !abp01_verify_manage_lookup_nonce()) {
		die;
	}

	$type = Abp01_InputFiltering::getPOSTValueOrDie('type', array('Abp01_Lookup', 'isTypeSupported'));
	$lang = Abp01_InputFiltering::getPOSTValueOrDie('lang', array('Abp01_Lookup', 'isLanguageSupported'));

	//initialize response
	$response = abp01_get_ajax_response(array(
		'item' => null
	));

	//fetch labels from POSTed data
	$defaultLabel = Abp01_InputFiltering::getFilteredPOSTValue('defaultLabel');
	$translatedLabel = Abp01_InputFiltering::getFilteredPOSTValue('translatedLabel');

	//the default label must not be empty
	if (empty($defaultLabel)) {
		$response->message = esc_html__('The default label is mandatory', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	$lookup = new Abp01_Lookup($lang);
	$item = $lookup->createLookupItem($type, $defaultLabel);

	//check if the item has been successfully created
	if ($item == null) {
		$response->message = esc_html__('The lookup item could not be created', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	//check if we should add the translation as well
	if (!Abp01_Lookup::isDefaultLanguage($lang) && !empty($translatedLabel)) {
		$response->success = $lookup->addLookupItemTranslation($item->id, $translatedLabel);
		if ($response->success) {
			$item->label = $translatedLabel;
			$item->hasTranslation = true;
		} else {
			$response->message = esc_html__('The lookup item has been created, but the translation could not be saved', 'abp01-trip-summary');
		}
	} else {
		$response->success = true;
	}

	$response->item = $item;
	abp01_send_json($response);
}

/**
 * Handles the lookup item editing save action.
  * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - the current user does not have the required permissions or...
 * - no valid nonce was found or...
 * - the required lang & id parameters were not provided.
 * 
 * @return void
 */
function abp01_edit_lookup_item() {
	if (abp01_get_http_method() != 'post') {
		die;
	}

	if (!abp01_can_manage_plugin_settings() || !abp01_verify_manage_lookup_nonce()) {
		die;
	}

	$id = Abp01_InputFiltering::getPOSTValueOrDie('id', 'is_numeric');
	$lang = Abp01_InputFiltering::getPOSTValueOrDie('lang', array('Abp01_Lookup', 'isLanguageSupported'));

	//initialize response
	$response = abp01_get_ajax_response();

	//fetch labels from POSTed data
	$defaultLabel = Abp01_InputFiltering::getFilteredPOSTValue('defaultLabel');
	$translatedLabel = Abp01_InputFiltering::getFilteredPOSTValue('translatedLabel');

	//the default label must not be empty
	if (empty($defaultLabel)) {
		$response->message = esc_html__('The default label is mandatory', 'abp01-trip-summary');
		abp01_send_json($response);
	}

	$lookup = new Abp01_Lookup($lang);
	$modifyItemOk = $lookup->modifyLookupItem($id, $defaultLabel);
	$modifyItemTranslationOk = false;

	//if there alread is a translation for the item, modify it
	if ($lookup->hasLookupItemTranslation($id)) {
		$modifyItemTranslationOk = empty($translatedLabel)
			? $lookup->deleteLookupItemTranslation($id)
			: $lookup->modifyLookupItemTranslation($id, $translatedLabel);
	} else {
		//otherwise, create a new one, if the translated label is not empty
		$modifyItemTranslationOk = !empty($translatedLabel) 
			? $lookup->addLookupItemTranslation($id, $translatedLabel) 
			: true;
	}

	//check overall result
	if (!$modifyItemOk || !$modifyItemTranslationOk) {
		$response->message = esc_html__('The lookup item could not be modified', 'abp01-trip-summary');
	} else {
		$response->success = true;
	}

	abp01_send_json($response);
}

/**
 * Handles the lookup item deletion action.
 * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - the current user does not have the required permissions or...
 * - no valid nonce was found or...
 * - the required lang & id parameters were not provided or...
 * - the required deleteOnlyLang was not provided
 * 
 * @return void
 */
function abp01_delete_lookup_item() {
	if (abp01_get_http_method() != 'post') {
		die;
	}

	//check for required permission and if the nonce is valid
	if (!abp01_can_manage_plugin_settings() || !abp01_verify_manage_lookup_nonce()) {
		die;
	}

	//fetch required parameters
	$id = Abp01_InputFiltering::getPOSTValueOrDie('id', 'is_numeric');
	$lang = Abp01_InputFiltering::getPOSTValueOrDie('lang', array('Abp01_Lookup', 'isLanguageSupported'));
	$deleteOnlyLang = Abp01_InputFiltering::getPOSTValueOrDie('deleteOnlyLang') === 'true';

	//initialize response
	$response = abp01_get_ajax_response();

	$lookup = new Abp01_Lookup($lang);
	//if a translation-only deletion was requested and the language is not the default one,
	//delete only the translation for the given language
	if ($deleteOnlyLang && !Abp01_Lookup::isDefaultLanguage($lang)) {
		if ($lookup->deleteLookupItemTranslation($id)) {
			$response->success = true;
		} else {
			$response->message = esc_html__('The item translation could not be deleted.', 'abp01-trip-summary');
		}
	} else {
		//otherwise, delete the entire item, all translations included
		//however, check first whether or not the item is still in use
		if ($lookup->isLookupInUse($id)) {
			$response->message = esc_html__('The item could not be deleted because it is still in use', 'abp01-trip-summary');
			abp01_send_json($response);
		}
	
		if ($lookup->deleteLookup($id)) {
			$response->success = true;
		} else {
			$response->message = esc_html__('The item could not be deleted', 'abp01-trip-summary');
		}
	}

	abp01_send_json($response);
}

/**
 * Handles the data submitted by the user from the post editor. The result of this operation is sent back as JSON.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * 
 * @return void
 */
function abp01_save_info() {
	//only HTTP post method is allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}

	$postId = abp01_get_current_post_id();
	if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
		die;
	}

	$type = Abp01_InputFiltering::getPOSTValueOrDie('type');
	if (!Abp01_Route_Info::isTypeSupported($type)) {
		die;
	}

	$info = new Abp01_Route_Info($type);
	$manager = abp01_get_route_manager();
	$response = abp01_get_ajax_response();

	foreach ($info->getValidFieldNames() as $field) {
		if (isset($_POST[$field])) {
			//Value is filtered on assignment.
			// @see Abp01_Route_Info::__set
			// @see Abp01_Route_Info::_filterFieldValue
			$info->$field = $_POST[$field];
		}
	}

	if ($manager->saveRouteInfo($postId, get_current_user_id(), $info)) {
		abp01_clear_post_viewer_data_cache($postId);
		$response->success = true;
	} else {
		$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
	}

	abp01_send_json($response);
}

function abp01_get_post_viewer_data_cache_key($postId) {
	return sprintf('_abp01_info_data_%s', $postId);
}

function abp01_clear_post_viewer_data_cache($postId) {
	$cacheKey = abp01_get_post_viewer_data_cache_key($postId);
	delete_transient($cacheKey);
}

function abp01_store_post_viewer_data_cache($postId, $data) {
	if (ABP01_POST_TRIP_SUMMARY_DATA_CACHE_EXPIRATION_SECONDS > 0) {
		$cacheKey = abp01_get_post_viewer_data_cache_key($postId);
		set_transient($cacheKey, $data, ABP01_POST_TRIP_SUMMARY_DATA_CACHE_EXPIRATION_SECONDS);
	}
}

function abp01_get_post_viewer_data_cache($postId) {
	$cacheKey = abp01_get_post_viewer_data_cache_key($postId);
	return get_transient($cacheKey);
}

function abp01_get_info_data($postId) {
	//the_content may be called multiple times
	//	so we need to cache the data to allow 
	//	for correct handling of this situation
	//see: https://wordpress.stackexchange.com/questions/225721/hook-added-to-the-content-seems-to-be-called-multiple-times

	$settings = abp01_get_settings();
	$data = abp01_get_post_viewer_data_cache($postId);

	if (empty($data) || !($data instanceof stdClass)) {
		$lookup = new Abp01_Lookup();
		$routeManager = abp01_get_route_manager();

		$data = new stdClass();
		$routeInfo = $routeManager->getRouteInfo($postId);

		$data->info = new stdClass();
		$data->info->exists = false;

		$data->track = new stdClass();
		$data->track->exists = $routeManager->hasRouteTrack($postId);

		//set the current trip summary information
		if ($routeInfo) {
			$data->info->exists = true;
			$data->info->isBikingTour = $routeInfo->isBikingTour();
			$data->info->isHikingTour = $routeInfo->isHikingTour();
			$data->info->isTrainRideTour = $routeInfo->isTrainRideTour();

			foreach ($routeInfo->getData() as $field => $value) {
				$lookupKey = $routeInfo->getLookupKey($field);
				if ($lookupKey) {
					if (is_array($value)) {
						foreach ($value as $k => $v) {
							$value[$k] = $lookup->lookup($lookupKey, $v);
						}
					} else {
						$value = $lookup->lookup($lookupKey, $value);
					}
				}
				$data->info->$field = $value;
			}
		}

		//current context information
		$data->postId = $postId;
		$data->ajaxUrl = abp01_get_ajax_baseurl();
		$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
		$data->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;
		$data->imgBaseUrl = abp01_get_env()->getPluginAssetUrl('media/img');

		//refresh nonces and settings every time, 
		//	so we don't need to cache them
		$data->settings = null;
		$data->nonceGet = null;
		$data->nonceDownload = null;

		//cache data
		abp01_store_post_viewer_data_cache($postId, $data);
	}

	//get plug-in settings
	$data->settings = $settings->asPlainObject();

	//create new nonces
	$data->nonceGet = abp01_create_get_track_nonce($postId);
	$data->nonceDownload = abp01_create_download_track_nonce($data->postId);

	return $data;
}

function abp01_get_content_viewer_shortcode_regexp() {
	return '/(\[\s*' . ABP01_VIEWER_SHORTCODE . '\s*\])/';
}

function abp01_content_has_viewer_shortchode(&$content) {
	return preg_match(abp01_get_content_viewer_shortcode_regexp(), $content);
}

function abp01_content_has_viewer_shortcode_block(&$content) {
	return function_exists('has_block') && has_block('abp01/block-editor-shortcode', $content);
}

/**
 * Render the viewer components for the given post identifier.
 * Return value is an array with a position for each compoent:
 * 	- 'teaserHtml' key - the frontented teaser - the top part;
 * 	- 'viewerHtml' key - the viewer itself.
 * 
 * @param int $postId The post identifier
 * @return array The viewer components, as decribed above
 */
function abp01_render_viewer($postId) {
	static $viewer = null;

	//only render once for the current request
	if ($viewer === null) {
		//fetch data
		$viewerHtml = null;
		$teaserHtml = null;
		$data = abp01_get_info_data($postId);

		//render the teaser and the viewer and attach the results to the post content
		if ($data->info->exists || $data->track->exists) {
			$teaserHtml = abp01_render_trip_summary_frontend_teaser($data);
			$viewerHtml = abp01_render_trip_summary_frontend($data);
		}

		$viewer = array(
			'teaserHtml' => $teaserHtml,
			'viewerHtml' => $viewerHtml
		);
	}

	return $viewer;
}

function abp01_ensure_content_has_unique_shortcode(&$content) {
	$replaced = false;
	return preg_replace_callback(abp01_get_content_viewer_shortcode_regexp(), 
		function($matches) use (&$replaced) {
			if ($replaced === false) {
				$replaced = true;
				return '[' . ABP01_VIEWER_SHORTCODE . ']';
			} else {
				return '';
			}
		}, $content);
}

/**
 * Filter function attached to the 'the_content' filter.
 * Its purpose is to render the trip summary viewer 
 * 	at the end of the post's content, but only within the post's page
 * The assumption is made that the wpautop filter 
 * 	has been previously removed from the filter chain
 * 
 * @param string $content The initial post content
 * @return string The filtered post content
 */
function abp01_add_viewer($content) {
	$content = wpautop($content);
	if (!is_single() && !is_page()) {
		return $content;
	}

	$postId = abp01_get_current_post_id();
	if (!$postId) {
		return $content;
	}

	$viewer = abp01_render_viewer($postId);
	$content = $viewer['teaserHtml'] . $content;

	if (!abp01_content_has_viewer_shortchode($content)
		&& !abp01_content_has_viewer_shortcode_block($content)) {
		$content = $content . $viewer['viewerHtml'];
	} elseif (abp01_content_has_viewer_shortchode($content)) {
		//Replace all but on of the shortcode references
		$content = abp01_ensure_content_has_unique_shortcode($content);
	}

	return $content;
}

function abp01_render_viewer_shortcode($attributes) {
	$content = '';
	$postId = abp01_get_current_post_id();

	if (!empty($postId)) {
		$viewer = abp01_render_viewer($postId);
		$content = $viewer['viewerHtml'];
	}

	return $content;
}

/**
 * Handles the request for trip summary info removal.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * 
 * @return void
 */
function abp01_remove_info() {
	//only HTTP POST method is allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}

	$postId = abp01_get_current_post_id();
	if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
		die;
	}

	$response = abp01_get_ajax_response();
	$manager = abp01_get_route_manager();

	if ($manager->deleteRouteInfo($postId)) {
		abp01_clear_post_viewer_data_cache($postId);
		$response->success = true;
	} else {
		$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
	}

	abp01_send_json($response);
}

/**
 * Handles the GPX track upload requests. Chunked file uploads are supported.
 * After file transfer is completed, it is parsed and the route information is stored.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * 
 * @return void
 */
function abp01_upload_track() {
	//only HTTP POST method is allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}

	//current user must have rights to edit the trip summary for the current post
	//and the received nonce has to be valid for the current post
	$postId = abp01_get_current_post_id();
	if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
		die;
	}

	//increase script execution limits: 
	//memory & cpu time (& xdebug.max_nesting_level)
	abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);

	//ensure the storage directory structure exists
	abp01_ensure_storage_directory();

	$manager = abp01_get_route_manager();
	$currentUserId = get_current_user_id();

	$destination = $manager->getTrackFilePath($postId);
	if (empty($destination)) {
		die;
	}

	//detect chunking
	if (ABP01_TRACK_UPLOAD_CHUNK_SIZE > 0) {
		$chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
		$chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;
	} else {
		$chunk = $chunks = 0;
	}

	//create and configure the uploader
	$uploader = new Abp01_Uploader(ABP01_TRACK_UPLOAD_KEY, $destination, array(
		'chunk' => $chunk, 
		'chunks' => $chunks, 
		'chunkSize' => ABP01_TRACK_UPLOAD_CHUNK_SIZE, 
		'maxFileSize' => ABP01_TRACK_UPLOAD_MAX_FILE_SIZE, 
		'allowedFileTypes' => array(
			'application/gpx', 
			'application/x-gpx+xml', 
			'application/xml-gpx', 
			'application/xml', 
			'text/xml',
			'application/octet-stream'
	)));
	$uploader->setCustomValidator(array(new Abp01_Validate_GpxDocument(), 'validate'));

	$result = new stdClass();
	$result->status = $uploader->receive();
	$result->ready = $uploader->isReady();

	//if the upload has completed, then process the newly 
	//	uploaded file and save the track information
	if ($result->ready) {
		$route = file_get_contents($destination);
		if (!empty($route)) {
			$parser = new Abp01_Route_Track_GpxDocumentParser();
			$route = $parser->parse($route);
			if ($route && !$parser->hasErrors()) {
				$destination = basename($destination);
				$track = new Abp01_Route_Track($postId, 
					$destination, 
					$route->getBounds(), 
					$route->minAlt, 
					$route->maxAlt);
				
				if (!$manager->saveRouteTrack($track, $currentUserId)) {
					$result->status = Abp01_Uploader::UPLOAD_INTERNAL_ERROR;
				} else {
					abp01_clear_post_viewer_data_cache($postId);
				}
			} else {
				$result->status = Abp01_Uploader::UPLOAD_DESTINATION_FILE_CORRUPT;
			}
		} else {
			$result->status = Abp01_Uploader::UPLOAD_DESTINATION_FILE_NOT_FOUND;
		}
	}

	abp01_send_json($result);
}

/**
 * Handles the track retrieval request. 
 * Script execution halts if the request context is not valid:
 *  - invalid HTTP method or...
 *  - invalid nonce provided
 * 
 * @return void
 */
function abp01_get_track() {
	//only HTTP GET method is allowed
	if (abp01_get_http_method() != 'get') {
		die;
	}

	//Verify cuurent post ID and nonce
	$postId = abp01_get_current_post_id();
	if (!$postId || !abp01_verify_get_track_nonce($postId)) {
		die;
	}

	//increase script execution limits: 
	//memory & cpu time (& xdebug.max_nesting_level)
	abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);

	$settings = abp01_get_settings();
	$manager = abp01_get_route_manager();
	
	$response = abp01_get_ajax_response();
	$targetUnitSystem = $settings->getUnitSystem();

	$track = $manager->getRouteTrack($postId);
	if (!empty($track)) {
		$trackDocument = $manager->getOrCreateDisplayableTrackDocument($track);
		if (empty($trackDocument)) {
			$response->message = esc_html__('Track file not found or is not readable', 'abp01-trip-summary');
		} else {
			$response->success = true;
		}
	}

	if ($response->success) {
		$response->info = new stdClass();
		$response->profile = new stdClass();
		$response->track = $trackDocument->toPlainObject();

		//Only go through the trouble of converting 
		//	these values for display if the user 
		//	has opted to show min/max altitude information
		if ($settings->getShowMinMaxAltitude()) {
			$response->info = $manager
				->getDisplayableTrackInfo($track, $targetUnitSystem)
				->toPlainObject();
		}

		if ($settings->getShowAltitudeProfile()) {
			$profile = $manager->getOrCreateDisplayableAltitudeProfile($track, $targetUnitSystem, 8);
			if (!empty($profile)) {
				$response->profile = $profile->toPlainObject();
			}
		}
	}

	abp01_send_json($response);
}

/**
 * Handles the GPX track download request.
 * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - not valid post ID or...
 * - no valid nonce detected or...
 * - track file downloading is disabled.
 * 
 * @return void
 */
function abp01_download_track() {
    //only HTTP GET method is allowed
    if (abp01_get_http_method() != 'get') {
        die;
    }

    $postId = abp01_get_current_post_id();
    if (empty($postId) || !abp01_verify_download_track_nonce($postId)) {
        die;
    }

    //check if track downloads are enabled
    if (!abp01_get_settings()->getAllowTrackDownload()) {
        die;
    }

	//increase script execution limits:
	//memory & cpu time (& xdebug.max_nesting_level)
    abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);

    //get the file path and check if it's readable
    $trackFile = abp01_get_route_manager()->getTrackFilePath($postId);
    if (empty($trackFile) || !is_readable($trackFile)) {
        die;
    }

    $fileSize = filesize($trackFile);
    $fileName = basename($trackFile);

    header('Content-Type: application/gpx');
    header('Content-Length: ' . $fileSize);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    readfile($trackFile);
    die;
}

/**
 * Handles the GPX track removal request. The result of this operation is sent back as JSON
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * 
 * @return void
 */
function abp01_remove_track() {
	//only HTTP post method is allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}

	//Validate post ID, nonce and access rights
	$postId = abp01_get_current_post_id();
	if (empty($postId) 
		|| !abp01_verify_edit_nonce($postId) 
		|| !abp01_can_edit_trip_summary($postId)) {
		die;
	}

	$manager = abp01_get_route_manager();
	$response = abp01_get_ajax_response();

	if ($manager->deleteRouteTrack($postId)) {
		$manager->deleteTrackFiles($postId);
		abp01_clear_post_viewer_data_cache($postId);

		$response->success = true;
	} else {
		$response->message = esc_html__('The data could not be updated due to a possible database error', 'abp01-trip-summary');
	}

	abp01_send_json($response);
}

function abp01_get_posts_trip_summary_info_cache_key($postIds) {
	return sprintf('_abp01_posts_listing_info_%s', sha1(join('_', $postIds)));
}

function abp01_get_posts_trip_summary_info($posts) {
	$postIds = array();
	$statusInfo = array();

	//extract post IDs
	$postIds = abp01_extract_post_ids($posts);
	if (!empty($postIds)) {
		//Attempt to extract any cached data
		$key = abp01_get_posts_trip_summary_info_cache_key($postIds);
		$statusInfo = get_transient($key);

		//If there is no status information cached, fetch it
		if (!is_array($statusInfo)) {
			$statusInfo = abp01_get_route_manager()->getTripSummaryStatusInfo($postIds);
			set_transient($key, $statusInfo, MINUTE_IN_SECONDS / 2);
		}
	}

	return $statusInfo;
}

function abpp01_add_custom_columns($columns) {
	$columns['abp01_trip_summary_info_status'] 
		= esc_html__('Trip summary info', 'abp01-trip-summary');
	$columns['abp01_trip_summary_track_status'] 
		= esc_html__('Trip summary track', 'abp01-trip-summary');
	return $columns;
}

function abp01_register_post_listing_columns($columns, $postType) {
	if (in_array($postType, array('post', 'page'))) {
		 $columns = abpp01_add_custom_columns($columns);
	}
	return $columns;
}

function abp01_register_page_listing_columns($columns) {
	$columns = abpp01_add_custom_columns($columns);
	return $columns;
}

function abp01_get_post_listing_custom_column_value($columnName, $postId) {
	static $tripSummaryInfo = null;

	$postTripSummaryInfo = null;
	if ($columnName == 'abp01_trip_summary_info_status' 
		|| $columnName == 'abp01_trip_summary_track_status') {
		
		//Fetch and locally cache the trip summary info
		if ($tripSummaryInfo === null) {
			$query = isset($GLOBALS['wp_query'])
				? $GLOBALS['wp_query'] 
				: null;

			$tripSummaryInfo = $query != null 
				? abp01_get_posts_trip_summary_info($query->posts) 
				: null;
		}

		$postTripSummaryInfo = !empty($tripSummaryInfo) && isset($tripSummaryInfo[$postId]) 
			? $tripSummaryInfo[$postId]
			: null;
	}

	if ($columnName == 'abp01_trip_summary_info_status') {
		if (!empty($postTripSummaryInfo)) {
			echo $postTripSummaryInfo['has_route_details'] 
				? abp01_get_status_text(esc_html__('Yes', 'abp01-trip-summary'), ABP01_STATUS_OK) 
				: abp01_get_status_text(esc_html__('No', 'abp01-trip-summary'), ABP01_STATUS_ERR);
		} else {
			echo abp01_get_status_text(esc_html__('Not available', 'abp01-trip-summary'), ABP01_STATUS_WARN);
		}
	}

	if ($columnName == 'abp01_trip_summary_track_status') {
		if (!empty($postTripSummaryInfo)) {
			echo $postTripSummaryInfo['has_route_track'] 
				? abp01_get_status_text(esc_html__('Yes', 'abp01-trip-summary'), ABP01_STATUS_OK)
				: abp01_get_status_text(esc_html__('No', 'abp01-trip-summary'), ABP01_STATUS_ERR);
		} else {
			echo abp01_get_status_text(esc_html__('Not available', 'abp01-trip-summary'), ABP01_STATUS_WARN);
		}
	}
}

function abp01_on_language_updated($oldValue, $value, $optName) {
	//When the WPLANG updated hook is triggered, 
	//	the text domain is not yet loaded.
	//Thus, it's no point in resetting the teasers at this point, 
	//	since the values corresponding to the previous locale will be pulled.
	//The solution is to queue a flag that says it needs to be updated at a later point in time,
	//	which currently is when the footer is being generated, for lack of a better time and place.
	if ($optName == 'WPLANG' && abp01_is_saving_wp_options()) {
		set_transient('abp01_reset_teaser_text_required', 'true', MINUTE_IN_SECONDS);
	}
}

function abp01_on_footer_loaded() {
	$resetTeaserTextRequired = get_transient('abp01_reset_teaser_text_required');
	delete_transient('abp01_reset_teaser_text_required');
	if ($resetTeaserTextRequired === 'true') {
		$settings = abp01_get_settings();
		$settings->resetTopTeaserText();
		$settings->resetBottomTeaserText();
		$settings->saveSettings();
	}
}

function abp01_run() {
	register_activation_hook(__FILE__, 'abp01_activate');
	register_deactivation_hook(__FILE__, 'abp01_deactivate');
	register_uninstall_hook(__FILE__, 'abp01_uninstall');

	add_filter('mce_buttons', 'abp01_register_classic_editor_buttons');
	add_filter('mce_external_plugins', 'abp01_register_classic_editor_plugins');
	add_filter('tiny_mce_before_init', 'abp01_register_classic_editor_settings');

	add_action('add_meta_boxes', 'abp01_register_metaboxes', 10, 2);
	add_action('admin_enqueue_scripts', 'abp01_add_admin_styles');
	add_action('admin_enqueue_scripts', 'abp01_add_admin_scripts');

	add_action('wp_ajax_' . ABP01_ACTION_EDIT, 'abp01_save_info');
	add_action('wp_ajax_' . ABP01_ACTION_UPLOAD_TRACK, 'abp01_upload_track');
	add_action('wp_ajax_' . ABP01_ACTION_CLEAR_TRACK, 'abp01_remove_track');
	add_action('wp_ajax_' . ABP01_ACTION_CLEAR_INFO, 'abp01_remove_info');
	add_action('wp_ajax_' . ABP01_ACTION_SAVE_SETTINGS, 'abp01_save_admin_settings_page_save');
	add_action('wp_ajax_' . ABP01_ACTION_DOWNLOAD_TRACK, 'abp01_download_track');
	add_action('wp_ajax_' . ABP01_ACTION_GET_LOOKUP, 'abp01_get_lookup_items');
	add_action('wp_ajax_' . ABP01_ACTION_ADD_LOOKUP, 'abp01_add_lookup_item');
	add_action('wp_ajax_' . ABP01_ACTION_EDIT_LOOKUP, 'abp01_edit_lookup_item');
	add_action('wp_ajax_' . ABP01_ACTION_DELETE_LOOKUP, 'abp01_delete_lookup_item');

	add_action('wp_ajax_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');
	add_action('wp_ajax_nopriv_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');
	add_action('wp_ajax_nopriv_' . ABP01_ACTION_DOWNLOAD_TRACK, 'abp01_download_track');

	add_action('wp_enqueue_scripts', 'abp01_add_frontend_styles');
	add_action('wp_enqueue_scripts', 'abp01_add_frontend_scripts');

	add_action('init', 'abp01_init_plugin');
	add_action('admin_menu', 'abp01_create_admin_menu');
	add_action('update_option_WPLANG', 'abp01_on_language_updated', 10, 3);
	add_action('in_admin_footer', 'abp01_on_footer_loaded', 1);

	add_action('manage_posts_custom_column', 'abp01_get_post_listing_custom_column_value', 10, 2);
	add_action('manage_pages_custom_column', 'abp01_get_post_listing_custom_column_value', 10, 2);

	add_filter('manage_posts_columns',  'abp01_register_post_listing_columns', 10, 2);
	add_filter('manage_pages_columns',  'abp01_register_page_listing_columns', 10, 1);

	add_shortcode(ABP01_VIEWER_SHORTCODE, 'abp01_render_viewer_shortcode');
}

//the autoloaders are ready, general!
abp01_init_autoloaders();
abp01_run();