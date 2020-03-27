<?php
/**
 * Plugin Name: WP Trip Summary
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.2.3
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

/**
 * Any file used by this plugin can be protected against direct browser access by checking this flag
 */
define('ABP01_LOADED', true);
define('ABP01_PLUGIN_ROOT', dirname(__FILE__));
define('ABP01_LIB_DIR', ABP01_PLUGIN_ROOT . '/lib');
define('ABP01_VERSION', '0.2.3');

define('ABP01_MAX_EXECUTION_TIME_MINUTES', 10);
define('ABP01_DISABLE_MINIFIED', false);

define('ABP01_ACTION_EDIT', 'abp01_edit_info');
define('ABP01_ACTION_CLEAR_INFO', 'abp01_clear_info');
define('ABP01_ACTION_CLEAR_TRACK', 'abp01_clear_track');
define('ABP01_ACTION_UPLOAD_TRACK', 'abp01_upload_track');
define('ABP01_ACTION_GET_TRACK', 'abp01_get_track');
define('ABP01_ACTION_SAVE_SETTINGS', 'abp01_save_settings');
define('ABP01_ACTION_DOWNLOAD_TRACK', 'abp01_download_track');
define('ABP01_ACTION_GET_LOOKUP', 'abp01_get_lookup');
define('ABP01_ACTION_DELETE_LOOKUP', 'abp01_delete_lookup');
define('ABP01_ACTION_ADD_LOOKUP', 'abp01_add_lookup');
define('ABP01_ACTION_EDIT_LOOKUP', 'abp01_edit_lookup');

define('ABP01_NONCE_TOUR_EDITOR', 'abp01.nonce.tourEditor');
define('ABP01_NONCE_GET_TRACK', 'abp01.nonce.getTrack');
define('ABP01_NONCE_EDIT_SETTINGS', 'abp01.nonce.editSettings');
define('ABP01_NONCE_DOWNLOAD_TRACK', 'abp01.nonce.downloadTrack');
define('ABP01_NONCE_MANAGE_LOOKUP', 'abp01.nonce.manageLookup');

define('ABP01_TRACK_UPLOAD_KEY', 'abp01_track_file');
define('ABP01_TRACK_UPLOAD_CHUNK_SIZE', 102400);
define('ABP01_TRACK_UPLOAD_MAX_FILE_SIZE', max(wp_max_upload_size(), 10485760));

define('ABP01_MAIN_MENU_SLUG', 'abp01-trip-summary-settings');
define('ABP01_LOOKUP_SUBMENU_SLUG', 'abp01-trip-summary-lookup');
define('ABP01_HELP_SUBMENU_SLUG', 'abp01-trip-summary-help');

define('ABP01_STATUS_OK', 0);
define('ABP01_STATUS_ERR', 1);
define('ABP01_STATUS_WARN', 2);

define('ABP01_GET_INFO_DATA_TRANSIENT_DURATION', 10);

/**
 * Returns the current environment accessor instance
 * @return Abp01_Env The current environment accessor instance
 */
function abp01_get_env() {
	return Abp01_Env::getInstance();
}

/**
 * Returns the current installer instance
 * @return Abp01_Installer The current installer instance
 */
function abp01_get_installer() {
	return new Abp01_Installer();
}

/**
 * Returns the current settings manager instance
 * @return Abp01_Settings The current settings manager instance
 */
function abp01_get_settings() {
	return Abp01_Settings::getInstance();
}

/**
 * @return Abp01_Route_Manager
 */
function abp01_get_route_manager() {
	return Abp01_Route_Manager::getInstance();
}

/**
 * Initializes the autoloading process
 * @return void
 */
function abp01_init_autoloaders() {
	require_once ABP01_LIB_DIR . '/Autoloader.php';
	Abp01_Autoloader::init(ABP01_LIB_DIR);
}

/**
 * Retrieve the help file path that corresponds to the given locale
 * @param String $locale The locale to check for
 * @return String The absolute help file path
 */
function abp01_get_help_file_for_locale($locale) {
	if (empty($locale)) {
		return '';
	}

	return sprintf('%s/help/%s/index.html', 
		abp01_get_env()->getDataDir(), 
		$locale);
}

/**
 * Dumps information about the given variable.
 * It uses xdebug_var_dump if available, otherwise it falls back to the standard var_dump, wrapping it in <pre /> tags.
 * @param mixed $var The variable to dump
 * @return void
 */
function abp01_dump($var) {
	if (extension_loaded('xdebug')) {
		var_dump($var);
	} else {
		print '<pre>';
		var_dump($var);
		print '</pre>';
	}
}

/**
 * Appends the given error to the given message if WP_DEBUG is set to true; otherwise returns the original message
 * @param string $message
 * @param mixed $error
 * @return string The processed message
 */
function abp01_append_error($message, $error) {
	if (WP_DEBUG) {
		if ($error instanceof Exception) {
			$message .= sprintf(': %s (%s) in file %s line %d', 
				$error->getMessage(), 
				$error->getCode(), 
				$error->getFile(), 
				$error->getLine());
		} else if (!empty($error)) {
			$message .= ': ' . $error;
		}
	}
	return $message;
}

/**
 * Increase script execution limit and maximum memory limit
 * @return void
 */
function abp01_increase_limits($executionTimeMinutes = 5) {
	if (function_exists('set_time_limit')) {
		@set_time_limit($executionTimeMinutes * 60);
	}
	if (function_exists('ini_set')) {
		@ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
		//If the xdebubg extension is loaded, 
		//	attempt to increase the max_nesting_level setting, 
		//	since (as is the case with large GPX files) 
		//	the simplify algorithm will fail due to its recursive nature 
		//	reaching that limit quickly if it's set too low
		if (extension_loaded('xdebug')) {
			@ini_set('xdebug.max_nesting_level', 1000000);
		}
	}
}

/**
 * Enable script error reporting
 * @return void
 */
function abp01_enable_error_reporting() {
	if (function_exists('ini_set')) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
	}

	if (function_exists('error_reporting')) {
		error_reporting(E_ALL);
	}
}

/**
 * @return stdClass A new standard response instance
 */
function abp01_get_ajax_response($additionalProps = array()) {
	$response = new stdClass();
	$response->success = false;
	$response->message = null;

	foreach ($additionalProps as $key => $value) {
		$response->$key = $value;
	}

	return $response;
}

/**
 * Renders the given text formatted according to the given status information.
 * The text is wrapped in an HTML span element, with the appropriate CSS class:
 * 
 * @param string $text The text to format
 * @param int $status The status information
 * 
 * @return string The formatted HTML text.
 */
function abp01_get_status_text($text, $status) {
	$cssClass = 'abp01-status-neutral';
	switch ($status) {
		case ABP01_STATUS_OK:
			$cssClass = 'abp01-status-ok';
		break;
		case ABP01_STATUS_ERR:
			$cssClass = 'abp01-status-err';
		break;
		case ABP01_STATUS_WARN:
			$cssClass = 'abp01-status-warn';
		break;
	}

	return '<span class="abp01-status-text ' . $cssClass . '">' . $text . '</span>';
}

/**
 * Extracts the IDs of the posts from the given array
 * 
 * @param array $posts The posts array from which to extract the IDs
 * @return array The corresponding array of post IDs
 */
function abp01_extract_post_ids($posts) {
	$postIds = array();

	if (!empty($posts) && is_array($posts)) {
		foreach ($posts as $post) {
			if (isset($post->ID)) {
				$postIds[] = intval($post->ID);
			}
		}
	}

	return $postIds;
}

/**
 * Creates a nonce to be used in the trip summary editor
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_edit_nonce($postId) {
	return wp_create_nonce(ABP01_NONCE_TOUR_EDITOR . ':' . $postId);
}

/**
 * Creates a nonce to be used when reading a trip's GPX track
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_get_track_nonce($postId) {
	return wp_create_nonce(ABP01_NONCE_GET_TRACK . ':' . $postId);
}

/**
 * Creates a nonce to be used when downloading a trips' GPX track
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_download_track_nonce($postId) {
    return wp_create_nonce(ABP01_NONCE_DOWNLOAD_TRACK . ':' . $postId);
}

/**
 * Creates a nonce to be used when saving plugin settings
 * @return string The created nonce
 */
function abp01_create_edit_settings_nonce() {
	return wp_create_nonce(ABP01_NONCE_EDIT_SETTINGS);
}

/**
 * Creates a nonce to be used when managing look-up data operations
 * @return string The created nonce
 */
function abp01_create_manage_lookup_nonce() {
	return wp_create_nonce(ABP01_NONCE_MANAGE_LOOKUP);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of track editing
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_edit_nonce($postId) {
	return check_ajax_referer(ABP01_NONCE_TOUR_EDITOR . ':' . $postId, 'abp01_nonce', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of reading a trip's GPS track
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_get_track_nonce($postId) {
	return check_ajax_referer(ABP01_NONCE_GET_TRACK . ':' . $postId, 'abp01_nonce_get', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of downloading a trip's GPS track
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_download_track_nonce($postId) {
    return check_ajax_referer(ABP01_NONCE_DOWNLOAD_TRACK . ':' . $postId, 'abp01_nonce_download', false);
}

/**
 * Checks whether the current request has a valid edit settings nonce
 * @return bool True if valid, false otherwise
 */
function abp01_verify_edit_settings_nonce() {
	return check_ajax_referer(ABP01_NONCE_EDIT_SETTINGS, 'abp01_nonce_settings', false);
}

/**
 * Checks whether the current request has a valid lookup data management nonce
 * @return bool True if valid, false otherwise
 */
function abp01_verify_manage_lookup_nonce() {
	return check_ajax_referer(ABP01_NONCE_MANAGE_LOOKUP, 'abp01_nonce_lookup_mgmt', false);	
}

/**
 * Encodes and outputs the given data as JSON and sets the appropriate headers
 * @param mixed $data The data to be encoded and sent to client
 * @return void
 */
function abp01_send_json($data) {
	$data = json_encode($data);
	header('Content-Type: application/json');
	if (extension_loaded('zlib') && function_exists('ini_set')) {
		@ini_set('zlib.output_compression', false);
		@ini_set('zlib.output_compression_level', 0);
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			header('Content-Encoding: gzip');
			$data = gzencode($data, 8, FORCE_GZIP);
		}
	}
	die($data);
}

/**
 * Ensures that the root storage directory of the plug-in exists and creates if it does not.
 * @return void
 */
function abp01_ensure_storage_directory() {
	abp01_get_installer()->ensureStorageDirectories();
}

/**
 * Compute the full GPX file upload destination file path for the given post ID
 * @param int $postId
 * @return string The computed path
 */
function abp01_get_track_upload_destination($postId) {
	$fileName = sprintf('track-%d.gpx', $postId);
	$tracksStorageDir = abp01_get_env()->getTracksStorageDir();

	return is_dir($tracksStorageDir) 
		? wp_normalize_path($tracksStorageDir . '/' . $fileName) 
		: null;
}

/**
 * Determine the HTTP method used with the current request
 * @return string The current HTTP method or null if it cannot be determined
 */
function abp01_get_http_method() {
	return abp01_get_env()->getHttpMethod();
}

/**
 * Checks whether an options saving operations is currently underway
 * @return bool
 * */
function abp01_is_saving_options() {
	return abp01_get_env()->isSavingWpOptions();
}

/**
 * Checks whether the admin posts listing is currently being browsed (regardless of post ype)
 * @return bool
 */
function abp01_is_browsing_posts_listing() {
	return abp01_get_env()->isListingWpPosts();
}

/**
 * Check whether the currently displayed screen is either the post editing or the post creation screen
 * @return bool
 */
function abp01_is_editing_post() {
	return abp01_get_env()->isEditingWpPost();
}

/**
 * Check whether the currently displayed screen is the plugin settings management screen
 * @return boolean True if on plugin settings page, false otherwise
 */
function abp01_is_editing_settings() {
	return abp01_get_env()->isAdminPage(ABP01_MAIN_MENU_SLUG);
}

/**
 * Check whether the currently displayed screen is the plugin lookup data management screen
 * @return booelan True if on plugin lookup data management page, false otherwise
 */
function abp01_is_managing_lookup() {
	return abp01_get_env()->isAdminPage(ABP01_LOOKUP_SUBMENU_SLUG);
}

/**
 * Check whether the currently displayed screen is the help page
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
 * @return mixed Int if a post ID is found, null otherwise
 */
function abp01_get_current_post_id() {
	return abp01_get_env()->getCurrentPostId('abp01_postId');
}

/**
 * Checks whether the current user can edit the current post's trip summary details.
 * If null is given, the function tries to infer the current post ID from the current context
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
 * @return boolean True if it has permission, false otherwise
 */
function abp01_can_manage_plugin_settings() {
	return Abp01_Auth::getInstance()->canManagePluginSettings();
}

/**
 * Computes the GPX track cache file path for the given post ID
 * @param int $postId
 * @param bool $ensureExists
 * @return string
 */
function abp01_get_track_cache_file_path($postId) {
	$fileName = sprintf('track-%d.cache', $postId);
	$cacheStorageDir = abp01_get_env()->getCacheStorageDir();

	return is_dir($cacheStorageDir) 
		? wp_normalize_path($cacheStorageDir . '/' . $fileName) 
		: null;
}

/**
 * Caches the serialized version of the given GPX track document for the given post ID
 * @param int $postId
 * @param Abp01_Route_Track_Document $route
 * @return void
 */
function abp01_save_cached_track($postId, Abp01_Route_Track_Document $route) {
	//Ensure the storage directory structure exists
	abp01_ensure_storage_directory();

	//Compute the path at which to store the cached file 
	//	and store the serialized track data
	$path = abp01_get_track_cache_file_path($postId);
	if (!empty($path)) {
		file_put_contents($path, $route->serializeDocument(), LOCK_EX);
	}
}

/**
 * Retrieves and deserializes the cached version of the GPX track document corresponding to the given post ID
 * @param int $postId
 * @return Abp01_Route_Track_Document The deserialized document
 */
function abp01_get_cached_track($postId) {
	$path = abp01_get_track_cache_file_path($postId);

	if (empty($path) || !is_readable($path)) {
		return null;
	}

	$contents = file_get_contents($path);
	return Abp01_Route_Track_Document::fromSerializedDocument($contents);
}

/**
 * Get the directories in which the frontend viewer templates should be searched.
 * @return stdClass
 */
function abp01_get_frontend_template_locations() {
	return abp01_get_env()->getFrontendTemplateLocations();
}

/**
 * Retrieve the translated label that corresponds to the given lookup type/category
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
		'errServerUploadFail' =>  esc_html__('The file could not be uploaded', 'abp01-trip-summary'),
		'selectBoxPlaceholder' => esc_html__('Choose options', 'abp01-trip-summary'),
		'selectBoxCaptionFormat' => esc_html__('{0} selected', 'abp01-trip-summary'),
		'selectBoxSelectAllText' => esc_html__('Select all', 'abp01-trip-summary')
	);
}

/**
 * Retrieve translations used in the settings editor script
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
 * @return array key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_main_frontend_translations() {
	return array();
}

/**
 * Render the button that opens the editor, in the post creation or post edit screen
 * @param stdClass $data Context data
 * @return void
 */
function abp01_render_techbox_button(stdClass $data) {
	require_once abp01_get_env()->getViewFilePath('wpts-button.php');
}

/**
 * Renders the editor in the post creation or post edit screen
 * @param stdClass $data The existing trip summary and context data
 * @return void
 */
function abp01_render_techbox_editor(stdClass $data) {
	require_once abp01_get_env()->getViewHelpersFilePath('controls.php');
	require_once abp01_get_env()->getViewFilePath('wpts-editor.php');
}

/**
 * Renders the plugin settings editor page
 * @param stdClass $data The settings context and existing settings values
 * @return void
 */
function abp01_admin_settings_page_render(stdClass $data) {
	require_once abp01_get_env()->getViewFilePath('wpts-settings.php');
}

/**
 * Renders the plugin help page
 * @param stdClass $data The data with the help contents
 * @return void
 */
function abp01_admin_help_page_render(stdClass $data) {
	require_once abp01_get_env()->getViewFilePath('wpts-help.php');
}

/**
 * Renders the plugin lookup data management page
 * @param stdClass $data The lookup data management page context and the actual data
 * @return void
 */
function abp01_admin_lookup_page_render(stdClass $data) {
	require_once abp01_get_env()->getViewFilePath('wpts-lookup-data-management.php');
}

/**
 * Builds the URL to the lookup data management page, in the context of the given lookup type
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
 * @param stdClass $data The trip summary and context data
 * @return void
 */
function abp01_render_techbox_frontend(stdClass $data) {
	$locations = abp01_get_frontend_template_locations();
	$themeHelpers = $locations->theme . '/helpers/controls.frontend.php';

	//if the custom viewer theme has overridden the helpers, include those helpers
	if (is_readable($themeHelpers)) {
		require_once $themeHelpers;
	}

	//include the default helpers - the actual functions will only be defined if no overrides are found
	require_once $locations->default . '/helpers/controls.frontend.php';

	//if the custom viewer theme has overridden the main view, include the override
	$themeViewer = $locations->theme . '/wpts-frontend.php';
	if (!is_readable($themeViewer)) {
		//otherwise, include the default main view
		require $locations->default . '/wpts-frontend.php';
	} else {
		require $themeViewer;
	}
}

/**
 * Render the trip summary teaser
 * @param stdClass $data The trip summary and context data
 */
function abp01_render_techbox_frontend_teaser(stdClass $data) {	
	$locations = abp01_get_frontend_template_locations();
	$themeHelpers = $locations->theme . '/helpers/controls.frontend.php';

	//if the custom viewer theme has overridden the helpers, include those helpers
	if (is_readable($themeHelpers)) {
		require_once $themeHelpers;
	}

	//include the default helpers - the actual functions will only be defined if no overrides are found
	require_once $locations->default . '/helpers/controls.frontend.php';

	//if the custom viewer theme has overridden the teaser view, include the override
	$themeTeaser = $locations->theme . '/wpts-frontend-teaser.php';
	if (!is_readable($themeTeaser)) {
		//otherwise, include the default teaser view
		require $locations->default . '/wpts-frontend-teaser.php';
	} else {
		require $themeTeaser;
	}
}

/**
 * Handles plug-in activation
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

/**
 * Run plug-in init sequence
 */
function abp01_init_plugin() {
	//configure script&styles includes and load the text domain
	Abp01_Includes::setRefPluginsPath(__FILE__);
	Abp01_Includes::setScriptsInFooter(true);
	load_plugin_textdomain('abp01-trip-summary', false, dirname(plugin_basename(__FILE__)) . '/lang/');

	//check if update is needed
	$installer = abp01_get_installer();
	$installer->updateIfNeeded();
}

/**
 * Add the button that opens the editor, in the post creation or post edit screen.
 * For now, it simply requests the button be rendered, without any further actions being taken
 * @return void
 */
function abp01_add_editor_media_buttons() {
	if (abp01_can_edit_trip_summary(null)) {
		abp01_render_techbox_button(new stdClass());
	}
}

/**
 * Adds the editor in the post creation or post edit screen
 * @param object $post The current post being created or modified
 * @return void
 */
function abp01_add_admin_editor($post) {
	if (!abp01_can_edit_trip_summary($post)) {
		return;
	}

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

	$data->ajaxUrl = get_admin_url(null, 'admin-ajax.php', 'admin');
	$data->imgBaseUrl = plugins_url('media/img', __FILE__);
	$data->nonce = abp01_create_edit_nonce($data->postId);
	$data->nonceGet = abp01_create_get_track_nonce($data->postId);	

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
	abp01_render_techbox_editor($data);
}

/**
 * Queues the appropriate styles with respect to the current admin screen
 * @return void
 */
function abp01_add_admin_styles() {
	if (abp01_is_browsing_posts_listing()) {
		Abp01_Includes::includeStyleAdminPostsListing();
	}

	//if in post editing page and IF the user is allowed to edit a post's trip summary
	//include the the styles required by the trip summary editor
	if (abp01_is_editing_post() && abp01_can_edit_trip_summary(null)) {
		Abp01_Includes::includeStyleAdminMain();
	}

	//if in plug-in editing page and IF the user is allowed to edit the plug-in's settings
	//include the styles required by the settings editor
	if (abp01_is_editing_settings() && abp01_can_manage_plugin_settings()) {
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
 * Queues the appropriate frontend styles with respect to the current frontend screen
 * @return void
 */
function abp01_add_frontend_styles() {
	if (is_single() || is_page()) {
		Abp01_Includes::includeStyleFrontendMain();
	}
}

/**
 * Queues the appropriate scripts with respect to the current admin screen
 * @return void
 */
function abp01_add_admin_scripts() {
	if (abp01_is_editing_post() && abp01_can_edit_trip_summary(null)) {
		Abp01_Includes::includeScriptAdminEditorMain(true, abp01_get_main_admin_script_translations());
	}

	if (abp01_is_editing_settings() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeScriptAdminSettings(abp01_get_settings_admin_script_translations());
	}

	if (abp01_is_managing_lookup() && abp01_can_manage_plugin_settings()) {
		Abp01_Includes::includeScriptAdminLookupMgmt(abp01_get_lookup_admin_script_translations());
	}
}

/**
 * Queues the appropriate frontend scripts with respect to the current frontend screen
 * @return void
 */
function abp01_add_frontend_scripts() {
	if (is_single() || is_page()) {
		Abp01_Includes::includeScriptFrontendMain(true, abp01_get_main_frontend_translations());
	}
}

/**
 * Prepares the required data and renders the plug-in's settings administration page.
 * If the current user does not have the required permissions to manage the plug-in, then the function returns directly.
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
	$data->ajaxUrl = get_admin_url(null, 'admin-ajax.php', 'admin');

	//fetch and process tile layer information
	$settings = abp01_get_settings();
	$tileLayers = $settings->getTileLayers();

	//fetch the bulk of the settings
	$data->settings = new stdClass();
	$data->settings->showTeaser = $settings->getShowTeaser();
	$data->settings->topTeaserText = $settings->getTopTeaserText();
	$data->settings->bottomTeaserText = $settings->getBottomTeaserText();
	$data->settings->tileLayer = $tileLayers[0];
	$data->settings->showFullScreen = $settings->getShowFullScreen();
	$data->settings->showMagnifyingGlass = $settings->getShowMagnifyingGlass();
	$data->settings->unitSystem = $settings->getUnitSystem();
	$data->settings->showMapScale = $settings->getShowMapScale();
	$data->settings->allowTrackDownload = $settings->getAllowTrackDownload();
	$data->settings->trackLineColour = $settings->getTrackLineColour();

	//fetch all the allowed unit systems
	$data->settings->allowedUnitSystems = array();
	$allowedUnitSystems = $settings->getAllowedUnitSystems();

	foreach ($allowedUnitSystems as $system) {
		$data->settings->allowedUnitSystems[$system] = ucfirst($system);
	}

	abp01_admin_settings_page_render($data);
}

/**
 * This function handles the plug-in settings save action. It receives and processes the corresponding HTTP request.
 * Execution halts if the given request context is not valid:
 * - invalid HTTP method or...
 * - no valid nonce detected or...
 * - current user lacks proper capabilities
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
 * This function handles the admin help page. The execution halts if the user lacks the proper capabilities
 * @return void
 */
function abp01_admin_help_page() {
	if (!abp01_can_manage_plugin_settings()) {
		return;
	}

	$locale = get_locale();
	$helpFile = abp01_get_help_file_for_locale($locale);
	
	if (!is_file($helpFile) || !is_readable($helpFile)) {
		$helpFile = abp01_get_help_file_for_locale('default');
		$locale = 'default';
	}
	
	$data = new stdClass();	
	$data->helpContents = file_get_contents($helpFile);	
	
	$helpDataDirUrl = plugins_url('data/help/' . $locale, __FILE__);
	$data->helpContents = str_ireplace('$helpDataDirUrl$', $helpDataDirUrl, $data->helpContents);
	
	abp01_admin_help_page_render($data);
}

/**
 * Prepares the required data and renders the plug-in's lookup data management page
 * If the current user does not have the required permissions to manage the plug-in, then the function returns directly.
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
	$data->context->ajaxBaseUrl = get_admin_url(null, 'admin-ajax.php', 'admin');

	//render the page
	abp01_admin_lookup_page_render($data);
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
		$response->success = true;
	} else {
		$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
	}

	abp01_send_json($response);
}

function abp01_get_info_data_cache_key($postId) {
	return sprintf('_abp01_info_data_%s', $postId);
}

function abp01_get_info_data($postId) {
	//the_content may be called multiple times
	//	so we need to cache the data to allow 
	//	for correct handling of this situation
	//see: https://wordpress.stackexchange.com/questions/225721/hook-added-to-the-content-seems-to-be-called-multiple-times

	$cacheKey = abp01_get_info_data_cache_key($postId);
	$data = get_transient($cacheKey);

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
		$data->nonceGet = abp01_create_get_track_nonce($postId);
		$data->nonceDownload = abp01_create_download_track_nonce($data->postId);	
		$data->ajaxUrl = get_admin_url(null, 'admin-ajax.php', 'admin');
		$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
		$data->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;
		$data->imgBaseUrl = plugins_url('media/img', __FILE__);
		
		//get relevant plug-in settings
		$settings = abp01_get_settings();
		$data->settings = new stdClass();
		$data->settings->showTeaser = $settings->getShowTeaser();
		$data->settings->topTeaserText =  $settings->getTopTeaserText();
		$data->settings->bottomTeaserText = $settings->getBottomTeaserText();
		
		//get measurement units from the configured unit system
		$unitSystem = Abp01_UnitSystem::create($settings->getUnitSystem());
		$data->unitSystem = $unitSystem->asPlainObject();

		set_transient($cacheKey, $data, ABP01_GET_INFO_DATA_TRANSIENT_DURATION);
	}

	return $data;
}

/**
 * Filter function attached to the 'the_content' filter.
 * Its purpose is to render the trip summary viewer at the end of the post's content, but only within the post's page
 * The assumption is made that the wpautop filter has been previously removed from the filter chain
 * @param string $content The initial post content
 * @return string The filtered post content
 */
function abp01_get_info($content) {
	$content = wpautop($content);
	if (!is_single() && !is_page()) {
		return $content;
	}

	$postId = abp01_get_current_post_id();
	if (!$postId) {
		return $content;
	}

	//fetch data
	$data = abp01_get_info_data($postId);

	//render the teaser and the viewer and attach the results to the post content
	if ($data->info->exists || $data->track->exists) {
		ob_start();
		abp01_render_techbox_frontend_teaser($data);
		$content = ob_get_clean() . $content;

		ob_start();
		abp01_render_techbox_frontend($data);
		$content = $content . ob_get_clean();
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

	if (!$manager->deleteRouteInfo($postId)) {
		$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
	} else {
		$response->success = true;
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

	$currentUserId = get_current_user_id();
	$destination = abp01_get_track_upload_destination($postId);
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

	//if the upload has completed, then process the newly uploaded file and save the track information
	if ($result->ready) {
		$route = file_get_contents($destination);
		if (!empty($route)) {
			$parser = new Abp01_Route_Track_GpxDocumentParser();
			$route = $parser->parse($route);
			if ($route && !$parser->hasErrors()) {
				$manager = abp01_get_route_manager();
				$destination = basename($destination);
				$track = new Abp01_Route_Track($destination, 
					$route->getBounds(), 
					$route->minAlt, 
					$route->maxAlt);
				
				if (!$manager->saveRouteTrack($postId, $currentUserId, $track)) {
					$result->status = Abp01_Uploader::UPLOAD_INTERNAL_ERROR;
				}
			} else {
				$result->status = Abp01_Uploader::UPLOAD_NOT_VALID;
			}
		} else {
			$result->status = Abp01_Uploader::UPLOAD_NOT_VALID;
		}
	}

	abp01_send_json($result);
}

/**
 * Handles the track retrieval request. Script execution halts if the request context is not valid:
 *  - invalid HTTP method or...
 *  - invalid nonce provided
 * @return void
 */
function abp01_get_track() {
	//only HTTP GET method is allowed
	if (abp01_get_http_method() != 'get') {
		die;
	}

	$postId = abp01_get_current_post_id();
	if (!$postId || !abp01_verify_get_track_nonce($postId)) {
		die;
	}

	//increase script execution limits: 
	//memory & cpu time (& xdebug.max_nesting_level)
	abp01_increase_limits(ABP01_MAX_EXECUTION_TIME_MINUTES);

	//initialize response data
	$response = abp01_get_ajax_response(array(
		'track' => null
	));

	$route = abp01_get_cached_track($postId);
	if (!($route instanceof Abp01_Route_Track_Document)) {
		$manager = abp01_get_route_manager();
		$track = $manager->getRouteTrack($postId);
		if ($track) {
			$file = abp01_get_track_upload_destination($postId);
			if (is_readable($file)) {
				$parser = new Abp01_Route_Track_GpxDocumentParser();
				$route = $parser->parse(file_get_contents($file));
				if ($route) {
					$route = $route->simplify(0.01);
					$response->success = true;
					abp01_save_cached_track($postId, $route);
				} else {
					$response->message = esc_html__('Track file could not be parsed', 'abp01-trip-summary');
				}
			} else {
				$response->message = esc_html__('Track file not found or is not readable', 'abp01-trip-summary');
			}
		}
	} else {
		$response->success = true;
	}

	if ($response->success) {
		$response->track = new stdClass();
		$response->track->route = $route;
		$response->track->bounds = $route->getBounds();
		$response->track->start = $route->getStartPoint();
		$response->track->end = $route->getEndPoint();
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
    $trackFile = abp01_get_track_upload_destination($postId);
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
 * @return void
 */
function abp01_remove_track() {
	//only HTTP post method is allowed
	if (abp01_get_http_method() != 'post') {
		die;
	}

	$postId = abp01_get_current_post_id();
	if (!abp01_verify_edit_nonce($postId) || !abp01_can_edit_trip_summary($postId)) {
		die;
	}

	$response = abp01_get_ajax_response();
	$manager = abp01_get_route_manager();

	if ($manager->deleteRouteTrack($postId)) {
		//delete track file
		$trackFile = abp01_get_track_upload_destination($postId);
		if (!empty($trackFile) && file_exists($trackFile)) {
			@unlink($trackFile);
		}

		//delete cached track file
		$cacheFile = abp01_get_track_cache_file_path($postId);
		if (!empty($cacheFile) && file_exists($cacheFile)) {
			@unlink($cacheFile);
		}

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
			set_transient($key, $statusInfo, MINUTE_IN_SECONDS);
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
	if ($postType == 'post' || $postType == 'page') {
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
	if ($optName == 'WPLANG' && abp01_is_saving_options()) {
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

//the autoloaders are ready, general!
abp01_init_autoloaders();

if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__, 'abp01_activate');
}

if (function_exists('register_deactivation_hook')) {
	register_deactivation_hook(__FILE__, 'abp01_deactivate');
}

if (function_exists('register_uninstall_hook')) {
	register_uninstall_hook(__FILE__, 'abp01_uninstall');
}

if (function_exists('add_action')) {
	add_action('media_buttons', 'abp01_add_editor_media_buttons', 20);
	add_action('admin_enqueue_scripts', 'abp01_add_admin_styles');
	add_action('admin_enqueue_scripts', 'abp01_add_admin_scripts');
	add_action('edit_form_after_editor', 'abp01_add_admin_editor');

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
}

if (function_exists('add_filter') && function_exists('remove_filter')) {
	remove_filter('the_content', 'wpautop');
	add_filter('the_content', 'abp01_get_info', 0);

	add_filter('manage_posts_columns',  'abp01_register_post_listing_columns', 10, 2);
	add_filter('manage_pages_columns',  'abp01_register_page_listing_columns', 10, 1);
}