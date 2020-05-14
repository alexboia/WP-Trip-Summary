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

 //Check that we're not being directly called
defined('ABP01_LOADED') or die;

/**
 * Returns the current environment accessor instance
 * 
 * @return Abp01_Env The current environment accessor instance
 */
function abp01_get_env() {
	return Abp01_Env::getInstance();
}

/**
 * Returns the current installer instance
 * 
 * @return Abp01_Installer The current installer instance
 */
function abp01_get_installer() {
	return new Abp01_Installer();
}

/**
 * Returns the current settings manager instance
 * 
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
 * @return Abp01_Help
 */
function abp01_get_help() {
	return new Abp01_Help();
}

/**
 * @return Abp01_View
 */
function abp01_get_view() {
	static $view = null;
	if ($view === null) {
		$view = new Abp01_View();
	}
	return $view;
}

/**
 * Initializes the autoloading process
 * 
 * @return void
 */
function abp01_init_autoloaders() {
	require_once ABP01_LIB_DIR . '/Autoloader.php';
	Abp01_Autoloader::init(ABP01_LIB_DIR);
}

/**
 * Dumps information about the given variable.
 * It uses xdebug_var_dump if available, otherwise it falls back to the standard var_dump, wrapping it in <pre /> tags.
 * 
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

if (!function_exists('write_log')) {
	function write_log ($message)  {
	   if (is_array($message) || is_object($message)) {
			ob_start();
			var_dump($message);
			$message = ob_get_clean();
			error_log($message);
	   } else {
			error_log($message);
	   }
	}
 }

/**
 * Increase script execution time limit and maximum memory limit
 * 
 * @param int $executionTimeMinutes The execution time in minutes, to raise the limit to. Defaults to 5 minutes.
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
 * 
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
 * Encodes and outputs the given data as JSON and sets the appropriate headers
 * 
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
 * Creates a unique string that can be used as a cache buster in URLs
 * 
 * @return string The generated string
 */
function abp01_get_cachebuster() {
	return sha1(microtime() .  uniqid());
}

/**
 * Appends the given error to the given message if WP_DEBUG is set to true; 
 * otherwise returns the original message
 * 
 * @param string $message
 * @param string|Exception|WP_Error $error
 * @return string The processed message
 */
function abp01_append_error($message, $error) {
	if (defined('WP_DEBUG') && WP_DEBUG) {
		if ($error instanceof Exception) {
			$message .= sprintf(': %s (%s) in file %s line %d', 
				$error->getMessage(), 
				$error->getCode(), 
				$error->getFile(), 
				$error->getLine());
		} else if (function_exists('is_wp_error') && is_wp_error($error)) {
			$message .= sprintf(': %s', join(', ', $error->get_error_messages()));
		} else if (!empty($error)) {
			$message .= ': ' . $error;
		}
	}
	return $message;
}

/**
 * Constructs the standard AJAX response structure 
 *  returned by admin-ajax.php ajax actions.
 * Optionally, additional properties can be added, as an associative array
 * 
 * @param array $additionalProps Additional properties to add.
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
			if (is_object($post) && isset($post->ID)) {
				$postIds[] = intval($post->ID);
			}
		}
	}

	return $postIds;
}

/**
 * @return boolean True if it's enabled, false otherwise
 */
function abp01_is_editor_classic_active() {
	return abp01_get_env()->isPluginActive('classic-editor/classic-editor.php');
}

/**
 * Gets the path to be used as a basis when constructing AJAX calls.
 * This is, in effect, the absolute path to WP's admin-ajax.php.
 * 
 * @return string The absolute path to admin-ajax.php
 */
function abp01_get_ajax_baseurl() {
	return abp01_get_env()->getAjaxBaseUrl();
}

/**
 * Determine the HTTP method used with the current request
 * 
 * @return string The current HTTP method or null if it cannot be determined
 */
function abp01_get_http_method() {
	return abp01_get_env()->getHttpMethod();
}

/**
 * Ensures that the root storage directory of the plug-in
 *  exists and creates if it does not.
 * 
 * @return void
 */
function abp01_ensure_storage_directory() {
	abp01_get_installer()->ensureStorageDirectories();
}