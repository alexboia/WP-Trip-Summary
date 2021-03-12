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
	return wp_create_nonce(ABP01_ACTION_GET_TRACK . ':' . $postId);
}

/**
 * Creates a nonce to be used when downloading a trips' GPX track
 * 
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_download_track_nonce($postId) {
    return wp_create_nonce(ABP01_ACTION_DOWNLOAD_TRACK . ':' . $postId);
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
	return check_ajax_referer(ABP01_ACTION_GET_TRACK . ':' . $postId, 'abp01_nonce_get', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of downloading a trip's GPS track
 * 
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_download_track_nonce($postId) {
    return check_ajax_referer(ABP01_ACTION_DOWNLOAD_TRACK . ':' . $postId, 'abp01_nonce_download', false);
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
function abp01_get_admin_trip_summary_editor_script_translations() {
	return Abp01_TranslatedScriptMessages::getAdminTripSummaryEditorScriptTranslations();
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
function abp01_get_frontend_viewer_script_translations() {
	return Abp01_TranslatedScriptMessages::getFrontendViewerScriptTranslations();
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
		Abp01_PluginModules_FrontendViewerPluginModule::class,
		Abp01_PluginModules_TeaserTextsSyncPluginModule::class
	));
	$pluginModuleHost->load();
}

function abp01_init_plugin() {
	//abp01_init_viewer_content_hooks();	
	if (!abp01_is_editor_classic_active()) {
		abp01_init_block_editor_blocks();
	}
}

function abp01_update_if_needed() {
	abp01_get_installer()->updateIfNeeded();
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

function abp01_get_gps_track_download_url($postId) {
	return add_query_arg(array(
		'action' => ABP01_ACTION_DOWNLOAD_TRACK,
		'abp01_nonce_download' => abp01_create_download_track_nonce($postId),
		'abp01_postId' => $postId,
		'_cb' => abp01_get_cachebuster()
	), abp01_get_ajax_baseurl());
}

function abp01_add_admin_editor_launcher($post, $args) {
	$postId = intval($post->ID);
	$manager = abp01_get_route_manager();

	$data = new stdClass();
	$data->postId = $postId;
	$data->hasRouteTrack = $manager->hasRouteTrack($postId);
	$data->hasRouteInfo = $manager->hasRouteInfo($postId);
	$data->trackDownloadUrl = abp01_get_gps_track_download_url($postId);

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
	$data->hasRouteTrack = $manager->hasRouteTrack($post->ID);
	$data->hasRouteInfo = $manager->hasRouteInfo($post->ID);
	$data->trackDownloadUrl = abp01_get_gps_track_download_url($post->ID);

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
 * Queues the appropriate styles with respect to the current admin screen
 * 
 * @return void
 */
function abp01_add_admin_styles() {
	//if in post editing page and IF the user is allowed to edit a post's trip summary
	//include the the styles required by the trip summary editor
	if (abp01_should_add_admin_editor()) {
		Abp01_Includes::includeStyleAdminMain();
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
		Abp01_Includes::includeScriptAdminEditorMain(true, abp01_get_admin_trip_summary_editor_script_translations());
	}
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

	if ($manager->saveRouteInfo($postId, $info, get_current_user_id())) {
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

    if (!abp01_get_settings()->getAllowTrackDownload()) {
        die;
    }

	$trackFileDownloader = new Abp01_Transfer_TrackFileDownloader();
	$trackFileDownloader->sendTrackFileForPostId($postId);

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
	add_action('wp_ajax_' . ABP01_ACTION_DOWNLOAD_TRACK, 'abp01_download_track');

	add_action('wp_ajax_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');
	add_action('wp_ajax_nopriv_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');
	add_action('wp_ajax_nopriv_' . ABP01_ACTION_DOWNLOAD_TRACK, 'abp01_download_track');

	add_action('plugins_loaded', 'abp01_setup_plugin');
	add_action('init', 'abp01_init_plugin');
}

//the autoloaders are ready, general!
abp01_init_autoloaders();
abp01_run();