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

defined('ABSPATH') or die;

//Any file used by this plugin can be protected against 
//  direct browser access by checking this flag
define('ABP01_LOADED', true);
define('ABP01_PLUGIN_ROOT', dirname(__FILE__));
define('ABP01_PLUGIN_ROOT_NAME', basename(ABP01_PLUGIN_ROOT));
define('ABP01_LIB_DIR', ABP01_PLUGIN_ROOT . '/lib');
define('ABP01_VERSION', '0.2.4');

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
define('ABP01_WRAPPED_LEAFLET_CONTEXT', 'abp01Leaflet');
define('ABP01_WRAPPED_SCRIPT_MAX_AGE', 31 * 24 * 60);
define('ABP01_WRAPPED_SCRIPT_MAX_MEMORY', '256M');
define('ABP01_WRAPPED_SCRIPT_MAX_EXECUTION_TIME', 1);