<?php 
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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
 * This script serves as a handler used to wrap leafleft plug-ins 
 *  with a self-executing function
 *  that provides the correct value for the L global javascript variable.
 * The value is taken from the global variable populated by the plug-in
 *  with its own leaflet instance,
 *  whose provided by the ABP01_WRAPPED_LEAFLET_CONTEXT constant.
 * 
 * Thus, the content served by this handler would look something like:
 *      (function(L) { 
 *          ...plug-in script content... 
 *      })(window.abp01Leaflet);
 * 
 * @see ABP01_WRAPPED_LEAFLET_CONTEXT
 * 
 * @package WP-Trip-Summary
 */

error_reporting(0);

if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(dirname(dirname(__DIR__))) . '/');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

require_once __DIR__ . '/abp01-plugin-wpshim.php';
require_once __DIR__ . '/abp01-plugin-header.php';

// Fixed for REPORT-2026-09-24/SEC-04
// Also see ./bin/tools/test-leaflet-wrapper.php

/**
 * Attempts to increase execution limits: time and memory.
 * Additionally, if the xdebug extension is installed, 
 *  the xdebug.max_nesting_level option is also increased
 * 
 * @see ABP01_WRAPPED_SCRIPT_MAX_EXECUTION_TIME_MINUTES
 * @see ABP01_WRAPPED_SCRIPT_MAX_MEMORY
 * 
 * @return void
 */
function wpts_wrapper_increase_limits(): void {
    if (function_exists('set_time_limit')) {
		@set_time_limit(ABP01_WRAPPED_SCRIPT_MAX_EXECUTION_TIME_MINUTES * 60);
	}
	if (function_exists('ini_set')) {
		@ini_set('memory_limit', ABP01_WRAPPED_SCRIPT_MAX_MEMORY);
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
 * Determine which script should be wrapped.
 * First looks at the 'load' URL parameter. 
 *  If it's set, its value is returned.
 *  If not, the path component of the REQUEST_URI server var is returned.
 * 
 * @return string The relative path to WP's root directory of the script that must be wrapped
 */
function wpts_wrapper_get_file_to_wrap(): ?string {
    $load = !empty($_GET['load']) 
        ? $_GET['load']
        : null;

    $requestUri = isset($_SERVER['REQUEST_URI']) 
        ? $_SERVER['REQUEST_URI']
        : null;

    $uri = parse_url($requestUri);
    if (empty($load)) {
        $load = $uri['path'];
    } else if(is_array($load) || is_object($load)) {
        return null;
    } else {
        if (str_ends_with($load, "\0")) {
            return null;
        }
        $load = urldecode(trim(strip_tags($load)));
    }

    return $load;
}

function wpts_wrapper_is_path_valid(string $load): bool {
    $absoluteLoadDirPath = wpts_wrapper_get_absolute_load_path($load);
    if ($absoluteLoadDirPath === null) {
        return false;
    }


    $allowedAbsolutePath = wpts_wrapper_get_plugin_absolute_allowed_base_path();
    if ($allowedAbsolutePath === null) {
        return false;
    }

    return str_starts_with($absoluteLoadDirPath, 
        $allowedAbsolutePath);
}

function wpts_wrapper_get_absolute_load_path(string $load): ?string {
    $cleanLoad = preg_replace('/[^a-zA-Z0-9\/.\-_]/',  '', $load);
    $realLoad = wpts_wrapper_expand_to_disk_path($cleanLoad);
    return $realLoad;
}

function wpts_wrapper_get_plugin_relative_base_url(): string {
    return '/wp-content/plugins/' . ABP01_PLUGIN_ROOT_NAME . '/';
}

function wpts_wrapper_get_plugin_absolute_allowed_base_path(): ?string {
    $path = realpath(__DIR__ . '/media/js/3rdParty/leaflet-plugins');
    if ($path === false) {
        return null;
    }

    $path = rtrim($path, DIRECTORY_SEPARATOR) 
        . DIRECTORY_SEPARATOR;

    return $path;
}

function wpts_wrapper_get_plugin_absolute_base_path(): string {
    return __DIR__;
}

function wpts_wrapper_expand_to_disk_path(string $relativeUrlPath): ?string {
    $relativeUrlPath = ltrim($relativeUrlPath, '/');
    $pluginBaseUrl = ltrim(wpts_wrapper_get_plugin_relative_base_url(), '/');

    $relativeUrlPath = str_replace($pluginBaseUrl, 
        '', 
        $relativeUrlPath);

    $relativePath = trim($relativeUrlPath);
    $relativePath = ltrim($relativePath, '.');
    $relativePath = ltrim($relativePath, '/');
    $relativePath = trim($relativePath);
    $relativePath = './' . $relativePath;
    
    $realPath = realpath($relativePath);

    if ($realPath !== false) {
        if (is_dir($realPath)) {
            $realPath = rtrim($realPath, DIRECTORY_SEPARATOR) 
                . DIRECTORY_SEPARATOR;
        }
    } else {
        $realPath = null;
    }

    return $realPath;
}

function wpts_wrapper_get_script_etag(): string {
    $version = isset($_GET['ver']) 
        ? $_GET['ver'] 
        : null;

    $etag = ABP01_VERSION;
    if (!empty($version)) {
        $version = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $version);
        $etag = $version . '-' . $etag;
    }

    return $etag;
}

/**
 * Tests whether the given requrest URI path
 *  matches an allowed pattern, to avoid serving only some files we deem fit
 * @return boolean True if allowed, false otherwise
 */
function wpts_wrapper_should_serve_script(?string $requestUriPath): bool {
    $isPathValid = wpts_wrapper_is_path_valid($requestUriPath);
    if (!$isPathValid) {
        return false;
    }

    $absoluteLoadPath = wpts_wrapper_get_absolute_load_path($requestUriPath);
    return str_ends_with( strtolower($absoluteLoadPath), '.js');
}

/**
 * Locate the file and return its absolute path
 * @return string The absolute file path
 */
function wpts_wrapper_locate_file_from_uri(?string $requestUriPath): ?string {
    return wpts_wrapper_get_absolute_load_path($requestUriPath);
}

/**
 * Fetches the contents of the script located at the given path
 *  and wraps it in a self executing function that ensures "L" points
 *  to the correct Leaflet version
 * 
 * @param string $filePath The absolute path of the script that should be processed
 * @return string The processed content
 */
function wpts_wrapper_process_script(string $filePath): string {
    $bom = pack('H*','EFBBBF');
    
    $contents = @file_get_contents($filePath);
    $contents = trim(preg_replace("/^$bom/", '', $contents));

    return (
        '(function (L) {' .  PHP_EOL .
            $contents  . PHP_EOL .
        '})(window.' . ABP01_WRAPPED_LEAFLET_CONTEXT . ');'
    );
}

/**
 * Run the entire script wrapping process
 * @return void
 */
function wpts_wrapper_serve_script(): never {
    $content = null;
    $contentLength = 0;
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    $etag = wpts_wrapper_get_script_etag();

    if (!in_array($protocol, array('HTTP/1.1', 'HTTP/2', 'HTTP/2.0'))) {
        $protocol = 'HTTP/1.0';
    }

    //See if there's anything to process
    $requestUri = wpts_wrapper_get_file_to_wrap();
    if (!empty($requestUri) && wpts_wrapper_should_serve_script($requestUri)) {
        //Locate script file and see if it's readable
        $wrapFilePath = wpts_wrapper_locate_file_from_uri($requestUri);
        if (!empty($wrapFilePath) && is_readable($wrapFilePath)) {
            if (isset($_SERVER['HTTP_IF_NONE_MATCH'] ) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                header($protocol . ' 304 Not Modified');
                die;
            }

            //Process content
            $content = wpts_wrapper_process_script($wrapFilePath);
            if (function_exists('mb_strlen')) {
                $contentLength = mb_strlen($content);
            } else {
                $contentLength = strlen($content);
            }
        }
    }

    if (!empty($content)) {
        //Spit it out
        header('Etag: ' . $etag);
        header('Content-Type: application/javascript; charset=UTF-8');
        header('Content-Length: ' . $contentLength);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + ABP01_WRAPPED_SCRIPT_MAX_AGE) . ' GMT');
        header('Cache-Control: public, max-age=' . ABP01_WRAPPED_SCRIPT_MAX_AGE );
        echo $content;
    } else {
        header($protocol . ' 404 Not Found');
    }

    //We done
    die;
}

wpts_wrapper_increase_limits();
wpts_wrapper_serve_script();