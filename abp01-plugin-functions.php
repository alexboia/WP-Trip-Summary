<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

if (!function_exists('abp01_die')) {
	function abp01_die() {
		if (func_num_args() == 1) {
			die(func_get_arg(0));
		} else {
			die;
		}
	}
}

/**
 * Initializes the autoloading process
 * 
 * @return void
 */
function abp01_init_autoloaders() {
	require_once ABP01_VENDOR_DIR . '/autoload.php';
	require_once ABP01_LIB_DIR . '/Autoloader.php';
	Abp01_Autoloader::init(ABP01_LIB_DIR);
}

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
	return Abp01_Route_Manager_Default::getInstance();
}

/**
 * @return Abp01_Route_Log_Manager
 */
function abp01_get_route_log_manager() {
	return Abp01_Route_Log_Manager_Default::getInstance();
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
 * @return Abp01_Auth
 */
function abp01_get_auth() {
	return Abp01_Auth::getInstance();
}

/**
 * @return Abp01_Plugin 
 */
function abp01_get_plugin() {
	static $plugin = null;
	if ($plugin === null) {
		$plugin = new Abp01_Plugin();
	}
	return $plugin;
}

function abp01_is_not_empty($value) {
	return !empty($value);
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

function abp01_wp_error_from_mysqlidb(MysqliDb $db) {
	$lastErrNo = $db->getLastErrno();
	return $lastErrNo != 0 
		? new WP_Error($lastErrNo, $db->getLastError()) 
		: null;
}

function abp01_wp_error_from_exception(Exception $exc) {
	return new WP_Error($exc->getCode(), $exc->getMessage(), array(
		'file' => $exc->getFile(),
		'line' => $exc->getLine(),
		'stackTrace' => $exc->getTraceAsString()
	));
}

function abp01_get_log_manager() {
	static $logManager = null;
	if ($logManager === null) {
		$config = abp01_get_logger_config();
		$logManager = new Abp01_Logger_Manager($config);
	}

	return $logManager;
}

function abp01_get_logger_config() {
	$env = abp01_get_env();
	$defaultRotateLogs = defined('ABP01_LOGS_ROTATE') 
		? constant('ABP01_LOGS_ROTATE') 
		: true;

	$rotateLogs = apply_filters('abp01_get_logger_config_rotate_logs', 
		$defaultRotateLogs) === true;

	$defaultMaxLogFiles = defined('ABP01_LOGS_MAX_LOG_FILES')
		? intval(constant('ABP01_LOGS_MAX_LOG_FILES'))
		: 10;

	$maxLogFiles = intval(apply_filters('abp01_get_logger_config_max_log_files', 
		$defaultMaxLogFiles));

	if ($maxLogFiles <= 0) {
		$maxLogFiles = $defaultMaxLogFiles;
	}

	$config = new Abp01_Logger_Config($env->getLogStorageDir(), 
		$rotateLogs, 
		$maxLogFiles, 
		$env->isDebugMode());

	return $config;
}

if (!function_exists('write_log')) {
	function write_log ($message)  {
	   if (is_array($message) || is_object($message)) {
			ob_start();
			var_dump($message);
			$message = ob_get_clean();
			error_log(trim($message) . PHP_EOL, 3, ABSPATH . 'error_log');
	   } else {
			error_log(trim($message) . PHP_EOL, 3, ABSPATH . 'error_log');
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

	abp01_send_header('Content-Type: application/json');
	if (extension_loaded('zlib') && function_exists('ini_set')) {
		@ini_set('zlib.output_compression', false);
		@ini_set('zlib.output_compression_level', 0);
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			abp01_send_header('Content-Encoding: gzip');
			$data = gzencode($data, 8, FORCE_GZIP);
		}
	}
	
	abp01_die($data);
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

function abp01_underscorize($value) {
	if (empty($value)) {
		return $value;
	}

	$returnParts = array();
	$parts = preg_split('/([A-Z]{1}[^A-Z]*)/', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

	foreach ($parts as $p) {
		$returnParts[] = strtolower($p);
	}

	$returnValue = join('_', $returnParts);
	return $returnValue;
}

/**
 * Reads the public instance variables of the source object,
 * 	escapes their values and adds them to a result that 
 * 	depends on the second, $output, parameter:
 * 	- if OBJECT, then the result is a stdClass whose public instance 
 * 		variables ar the same as the ones read from the source;
 * 	- if ARRAY_A, then the result is an associative array, whose 
 * 		keys are the same as the public instance variables read from the source;
 * 	- if ARRAY_N, then the result is an indexed array that only contains 
 * 		the values read from the source object.
 * 
 * @param mixed $sourceObject The source object
 * @param string $output One of OBJECT|ARRAY_A|ARRAY_N constants
 * @return stdClass|array The result, according to the $output parameter
 */
function abp01_esc_js_object($sourceObject, $output = OBJECT) {
	if ($sourceObject == null) {
		return null;
	}

	if (!is_object($sourceObject)) {
		throw new InvalidArgumentException('Input is not an object');
	}

	$escaped = $output == OBJECT 
		? new stdClass() 
		: array();

	foreach (get_object_vars($sourceObject) as $propKey => $propVal) {
		if ($output == OBJECT) {
			$escaped->$propKey = esc_js($propVal);
		} else if ($output == ARRAY_A) {
			$escaped[$propKey] = $propVal;
		} else {
			$escaped[] = $propVal;
		}
	}

	return $escaped;
}

/**
 * Convers the input variable to the string 
 * 	equivalent of its boolean value.
 * 
 * @param mixed $var 
 * @return string 'true' if the input evaluates to true, 'false' otherwise
 */
function abp01_bool2str($var) {
	return $var ? 'true' : 'false';
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
	abp01_get_installer()->ensureStorageDirectoriesAndAssets();
}

/**
 * Retrieve the translated label that corresponds 
 * 	to the given lookup type/category
 * 
 * @param string $type The type for which to retrieve the translated label
 * @return string The translated label
 */
function abp01_get_lookup_type_label($type) {
	static $translations = null; 
	
	if ($translations === null) {
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
	}

	$translatedLabel = isset($translations[$type]) 
		? $translations[$type] 
		: null;

	$filteredTranslatedLabel = apply_filters('abp01_get_lookup_type_label', 
		$translatedLabel, 
		$type);

	return $filteredTranslatedLabel;
}

/**
 * Remove all files that match the given absolute glob path pattern
 * 	and returns the removed files or null if no matched file found
 * 
 * @param string $globPathPattern 
 * @return array|null 
 */
function abp01_delete_files_by_glob_pattern($globPathPattern) {
	$files = glob($globPathPattern, GLOB_NOESCAPE);
	if (is_array($files)) {
		foreach ($files as $file) {
			@unlink($file);
		}
		
		return $files;
	} else {
		return null;
	}
}

if (!function_exists('wp_script_get_data')) {
	/**
	 * Retrieves the data section associated with a script handle.
	 * 
	 * @param string $handle The script handle
	 * @return string The data section
	 */
	function wp_script_get_data($handle) {
		return wp_scripts()->get_data($handle, 'data');
	}
}

if (!function_exists('wp_script_has_localization')) {
	/**
	 * Checks whether or not the script has a localization with the given name.
	 * 
	 * @param string $handle The script handle
	 * @param string $localizationVarName The localization (javascript variable) name
	 * @return bool True if does, false if it does not
	 */
	function wp_script_has_localization($handle, $localizationVarName) {
		$data = wp_script_get_data($handle);
		if ($data !== false) {
			$searchMarker = sprintf('var %s', $localizationVarName);
			return strpos($data, $searchMarker) !== false;
		} else {
			return false;
		}
	}
}

if (!function_exists('abp01_send_header')) {
	/**
	 * Send a raw HTTP header. Essentially a wrapper over the native header() function, 
	 * 	with the same signature.
	 * 
	 * It is meant to be pluggable, so that it may be 
	 * 	replaced with either a user-defined function 
	 * 	or with a test double.
	 * 
	 * @param string $header The header string
	 * @param bool $replace Whether the header should replace a previous similar header, or add a second header of the same type
	 * @param int $httpResponseCode Forces the HTTP response code to the specified value
	 * @return void 
	 */
	function abp01_send_header($header, $replace = true, $httpResponseCode = 0) {
		header($header, $replace, $httpResponseCode);
	}
}

if (!function_exists('abp01_set_http_response_code')) {
	/**
	 * Set the HTTP response code. Essentially a rapper over the native http_response_code() function, 
	 * 	but with the notable difference that it only supports 
	 * 	setting the http response code.
	 * 
	 * It is meant to be pluggable, so that it may be 
	 * 	replaced with either a user-defined function 
	 * 	or with a test double.
	 * 
	 * @param mixed $responseCode The response code
	 * @return void 
	 */
	function abp01_set_http_response_code($responseCode) {
		http_response_code($responseCode);
	}
}

if (!function_exists('abp01_is_url_rewrite_enabled')) {
	function abp01_is_url_rewrite_enabled() {
		static $enabled = null;

		if ($enabled === null) {
			if (!function_exists('got_url_rewrite')) {
				require_once ABSPATH . 'wp-admin/includes/misc.php';
			}
			$enabled = got_url_rewrite();
		}

		return $enabled;
	}
}

function abp01_format_timestamp($timestamp, $withTime = true) {
	$format = abp01_determine_date_format($withTime);
	return wp_date($format, $timestamp);
}

function abp01_format_db_date($dbDate, $withTime = true) {
	$format = abp01_determine_date_format($withTime);
	return mysql2date($format, $dbDate, true);
}

function abp01_format_time_in_hours($timeInHours) {
	return !empty($timeInHours) 
		? $timeInHours . ' ' . _n('hour', 'hours', $timeInHours, 'abp01-trip-summary') 
		: '-';
}

function abp01_determine_date_format($withTime) {
	$format = get_option('date_format');
	if ($withTime) {
		$format .= ' ' . get_option('time_format');
	}

	return apply_filters('abp01_determine_date_format', 
		$format, 
		$withTime);
}

function abp01_run() {
	abp01_get_plugin()->run();
}
