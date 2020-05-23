<?php
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
 * The WP Trip Summary plug-in header defines all the required constants
 * 
 * @package WP-Trip-Summary
 */

defined('ABSPATH') or die;

/**
 * Marker constant for establihing that 
 *  WP Trip Summary core has been loaded.
 * All other files must check for the existence 
 *  of this constant  and die if it's not present.
 * 
 * @var boolean
 */
define('ABP01_LOADED', true);

/**
 * The absolute path to the plug-in's installation directory.
 *  Eg. /whatever/public_html/wp-content/plugins/wp-trip-summary.
 * 
 * @var string
 */
define('ABP01_PLUGIN_ROOT', __DIR__);

/**
 * The absolute path to this file - the plug-in header file
 * 
 * @var string
 */
define('ABP01_PLUGIN_HEADER', __FILE__);

/**
 * The absolute path to the main plug-in file - abp01-plugin-main.php
 * 
 * @var string
 */
define('ABP01_PLUGIN_MAIN', ABP01_PLUGIN_ROOT . '/abp01-plugin-main.php');

/**
 * The absolute path to the plug-in's functions file - abp01-plugin-functions.php
 * 
 * @var string
 */
define('ABP01_PLUGIN_FUNCTIONS', ABP01_PLUGIN_ROOT . '/abp01-plugin-functions.php');

/**
 * The name of the directory in which the plug-in is installed.
 *  Eg. wp-trip-summary.
 * 
 * @var string
 */
define('ABP01_PLUGIN_ROOT_NAME', basename(ABP01_PLUGIN_ROOT));

/**
 * The absolute path to the plug-in's library - lib - directory.
 *  This is where all the PHP dependencies are stored.
 *  Eg. /whatever/public_html/wp-content/plugins/wp-trip-summary/lib.
 * 
 * @var string
 */
define('ABP01_LIB_DIR', ABP01_PLUGIN_ROOT . '/lib');

/**
 * The current version of WP-Trip-Summary.
 *  Eg. 0.2.5.
 * 
 * @var string
 */
define('ABP01_VERSION', '0.2.5');

if (!defined('ABP01_MAX_EXECUTION_TIME_MINUTES')) {
    /**
     * The maximum time to which the execution time limit can be raised when required.
     *  For instance, when uploading and processing a track.
     *  Defaults to 10 minutes.
     * 
     * @var int
     */
    define('ABP01_MAX_EXECUTION_TIME_MINUTES', 10);
}

if (!defined('ABP01_DISABLE_MINIFIED')) {
    /**
     * Whether or not to disabled script and style minification. 
     *  This is not yet used.
     *  Defaults to false.
     * 
     * @var boolean
     */
    define('ABP01_DISABLE_MINIFIED', false);
}

/**
 * The action name used with admin-ajax.php when saving trip summary information 
 *  (data on the Info tab).
 * 
 * @var string
 */
define('ABP01_ACTION_EDIT', 'abp01_edit_info');

/**
 * The action name used with admin-ajax.php when removing trip summary information 
 *  (data on the Info tab).
 * 
 * @var string
 */
define('ABP01_ACTION_CLEAR_INFO', 'abp01_clear_info');

/**
 * The action name used with admin-ajax.php when removing the trip summary track 
 *  (data on the Map tab).
 * 
 * @var string
 */
define('ABP01_ACTION_CLEAR_TRACK', 'abp01_clear_track');

/**
 * The action name used with admin-ajax.php when uploading the trip summary track 
 *  (data on the Map tab).
 * 
 * @var string
 */
define('ABP01_ACTION_UPLOAD_TRACK', 'abp01_upload_track');

/**
 * The action name used with admin-ajax.php when retrieving 
 *  the trip summary track json data.
 * 
 * @var string
 */
define('ABP01_ACTION_GET_TRACK', 'abp01_get_track');

/**
 * The action name used with admin-ajax.php when saving plug-in settings. 
 * 
 * @var string
 */
define('ABP01_ACTION_SAVE_SETTINGS', 'abp01_save_settings');

/**
 * The action name used with admin-ajax.php when downloading the GPS track.
 * 
 * @var string
 */
define('ABP01_ACTION_DOWNLOAD_TRACK', 'abp01_download_track');

/**
 * The action name used with admin-ajax.php when retrieving 
 *  the list of lookup data items.
 * 
 * @var string
 */
define('ABP01_ACTION_GET_LOOKUP', 'abp01_get_lookup');

/**
 * The action name used with admin-ajax.php when removing a lookup data item.
 * 
 * @var string
 */
define('ABP01_ACTION_DELETE_LOOKUP', 'abp01_delete_lookup');

/**
 * The action name used with admin-ajax.php when creating a new a lookup data item.
 * 
 * @var string
 */
define('ABP01_ACTION_ADD_LOOKUP', 'abp01_add_lookup');

/**
 * The action name used with admin-ajax.php when editing an existing a lookup data item.
 * 
 * @var string
 */
define('ABP01_ACTION_EDIT_LOOKUP', 'abp01_edit_lookup');

/**
 * The prefix used to construct nonce tokens required 
 *  when saving trip summary information, as well as 
 *  when uploading GPS track data.
 * The post Id is appended to this prefix to establish 
 *  the final string used to generate the nonce. 
 * 
 * @var string
 */
define('ABP01_NONCE_TRIP_SUMMARY_EDITOR', 'abp01.nonce.tripSummaryEditor');

/**
 * The prefix used to construct nonce tokens required 
 *  when retrieving JSON GPS track data.
 * The post Id is appended to this prefix to establish 
 *  the final string used to generate the nonce. 
 * 
 * @var string
 */
define('ABP01_NONCE_GET_TRACK', 'abp01.nonce.getTrack');

/**
 * The prefix used to construct nonce tokens required 
 *  when saving plug-in settings
 * The post Id is appended to this prefix to establish 
 *  the final string used to generate the nonce. 
 * 
 * @var string
 */
define('ABP01_NONCE_EDIT_SETTINGS', 'abp01.nonce.editSettings');

/**
 * The prefix used to construct nonce tokens required 
 *  when downloading the original GPS track data file (GPX or otherwise)
 * The post Id is appended to this prefix to establish 
 *  the final string used to generate the nonce. 
 * 
 * @var string
 */
define('ABP01_NONCE_DOWNLOAD_TRACK', 'abp01.nonce.downloadTrack');

/**
 * The prefix used to construct nonce tokens required 
 *  when managing lookup data (list/add/edit/delete/get by id)
 * The post Id is appended to this prefix to establish 
 *  the final string used to generate the nonce. 
 * 
 * @var string
 */
define('ABP01_NONCE_MANAGE_LOOKUP', 'abp01.nonce.manageLookup');

/**
 * The name of the file upload file that, 
 *  albeit hidden from view, is used to 
 *  upload the track file to the server
 * 
 * @var string
 */
define('ABP01_TRACK_UPLOAD_KEY', 'abp01_track_file');

if (!defined('ABP01_TRACK_UPLOAD_CHUNK_SIZE')) {
    /**
     * The chunk size is the maxim file chunk, expressed in bytes, 
     *      that can be uploaded in one sitting.
     *  If a file is larger than this size, it will be split in multiple chunks, 
     *      which will be uploaded in sequence.
     * Can be overridden in wp-config.php.
     * 
     * @var int
     */
    define('ABP01_TRACK_UPLOAD_CHUNK_SIZE', 102400);
}

if (!defined('ABP01_TRACK_UPLOAD_MAX_FILE_SIZE')) {
    /**
     * The maximum size, in bytes, the the plug-in allows for the track file. 
     * That is, track files larger than this are rejected.
     * Defaults to 10485760 or wp_max_upload_size(), whichever is larger.
     * 
     * @var int
     */
    define('ABP01_TRACK_UPLOAD_MAX_FILE_SIZE', max(wp_max_upload_size(), 10485760));
}

/**
 * Slug used for main menu mentry, which corresponds to the plug-in settings page.
 * 
 * @var string
 */
define('ABP01_MAIN_MENU_SLUG', 'abp01-trip-summary-settings');

/**
 * Slug used for lookup data management page sub-menu mentry.
 * 
 * @var string
 */
define('ABP01_LOOKUP_SUBMENU_SLUG', 'abp01-trip-summary-lookup');

/**
 * Slug used for help page sub-menu mentry.
 * 
 * @var string
 */
define('ABP01_HELP_SUBMENU_SLUG', 'abp01-trip-summary-help');

/**
 * Value used to signify a successful/OK/all-good situation.
 * 
 * @var int
 */
define('ABP01_STATUS_OK', 0);

/**
 * Value used to signify an error/danger/something missing situation.
 * 
 * @var int
 */
define('ABP01_STATUS_ERR', 1);

/**
 * Value used to signify a situation that could be better, 
 *  but not is not quite an error, nor dangerous.
 * 
 * @var int
 */
define('ABP01_STATUS_WARN', 2);

if (!defined('ABP01_POST_TRIP_SUMMARY_DATA_CACHE_EXPIRATION_SECONDS')) {
    /**
     * The number of seconds that trip summary data is cached. 
     *  Defaults to 600 (10 minutes).
     * 
     * @var int
     */
    define('ABP01_POST_TRIP_SUMMARY_DATA_CACHE_EXPIRATION_SECONDS', 600);
}

/**
 * The name of the global javascript variable used to 
 *  store the Leaflet instance used by this plug-in.
 * 
 * @var string
 */
define('ABP01_WRAPPED_LEAFLET_CONTEXT', 'abp01Leaflet');

if (!defined('ABP01_WRAPPED_SCRIPT_MAX_AGE')) {
    /**
     * The maximum age, in seconds, used to compute the HTTP cache headers
     *  when sending the contents of the wrapped leaflet plug-in scripts.
     * Defauls to 31 days, in seconds: 44640.
     * 
     * @var int
     */
    define('ABP01_WRAPPED_SCRIPT_MAX_AGE', 31 * 24 * 60);
}

if (!defined('ABP01_WRAPPED_SCRIPT_MAX_MEMORY')) {
    /**
     * The memory limit thay may pe consumed by the leaflet plug-in script wrapper, 
     *  abp01-plugin-leaflet-plugins-wrapper.php.
     * Before running, the script wrapper attempts to set 
     *  the memory limit to this value.
     * Value may be expressed as integers (in which case they express bytes), 
     *  or as shorthand values, as described here: https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     * Defaults to '256M'.
     * 
     * @var int|string
     */
    define('ABP01_WRAPPED_SCRIPT_MAX_MEMORY', '256M');
}

if (!defined('ABP01_WRAPPED_SCRIPT_MAX_EXECUTION_TIME_MINUTES')) {
    /**
     * The maximum time to which the execution time limit for the leaflet plug-in script wrapper,
     *  abp01-plugin-leaflet-plugins-wrapper.php.
     * Before running, the script wrapper attempts to set 
     *  the execution time limit to this value.
     * Defaults to 1 minute.
     * 
     * @var int
     */
    define('ABP01_WRAPPED_SCRIPT_MAX_EXECUTION_TIME_MINUTES', 1);
}

if (!defined('ABP01_VIEWER_SHORTCODE')) {
    /**
     * The shortcode used to include the viewer at any point in the blog post contents.
     * Can be set in wp-config.php.
     * Default is 'abp01_trip_summary_viewer'.
     * 
     * @var string
     */
    define('ABP01_VIEWER_SHORTCODE', 'abp01_trip_summary_viewer');
}