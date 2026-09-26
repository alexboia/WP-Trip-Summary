<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

/**
 * HTTP integration tests for abp01-plugin-leaflet-plugins-wrapper.php.
 *
 * Usage (from the plugin directory):
 *   php bin/tools/test-leaflet-wrapper.php alexboia.net.local:8080
 *   php bin/tools/test-leaflet-wrapper.php https://example.com --mode=load
 *   php bin/tools/test-leaflet-wrapper.php localhost --mode=rewrite --plugin-path=/wp-content/plugins/wp-trip-summary
 *
 * Requires PHP CLI with cURL and a host serving this checkout's plugin version
 * and assets. The default mode, "all", exercises both URL forms. Use "load"
 * when mod_rewrite is unavailable; "rewrite" requires the plugin's .htaccess
 * rules. Requests go through the real web server, including REQUEST_URI.
 * No WordPress bootstrap, PHPUnit, server configuration changes or remote
 * fixtures are needed. Existing public JavaScript files are traversal targets.
 *
 * Exit codes: 0 = passed, 1 = test failures, 2 = usage/setup/transport error.
 * 
 * Added as support for testing the fixes for REPORT-2026-09-24/SEC-04
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit;
}

function wpts_leaflet_test_usage(): string {
	return 'Usage: php test-leaflet-wrapper.php <hostname[:port]|http(s)://hostname[:port]>' . PHP_EOL .
		'       [--mode=all|load|rewrite] [--plugin-path=/wp-content/plugins/<directory>]' . PHP_EOL .
		'Use --mode=load on servers without mod_rewrite. Default: both modes.' . PHP_EOL .
		'The host must serve the same plugin version and JavaScript as this checkout.' . PHP_EOL .
		'Exit codes: 0 passed; 1 failed assertions; 2 usage, setup or HTTP transport error.' . PHP_EOL;
}

function wpts_leaflet_test_options(array $arguments): array {
	$options = wpts_leaflet_test_parse_arguments($arguments);
	$options['host'] = wpts_leaflet_test_normalize_host($options['host']);
	wpts_leaflet_test_validate_mode($options['mode']);
	$options['plugin-path'] = wpts_leaflet_test_normalize_plugin_path($options['plugin-path']);
	return $options;
}

function wpts_get_plugin_path(): string {
	return '/wp-content/plugins/' . basename(dirname(__DIR__, 2));
}

function wpts_leaflet_test_parse_arguments(array $arguments): array {
	$options = array(
		'host' => null,
		'mode' => 'all',
		'plugin-path' => wpts_get_plugin_path()
	);

	$validDashArgs = array('mode', 'plugin-path');

	foreach ($arguments as $argument) {
		if (str_starts_with($argument, '--')) {
			$parts = explode('=', substr($argument, 2), 2);
			if (count($parts) !== 2 
				|| !in_array($parts[0], $validDashArgs, true)) {
				throw new InvalidArgumentException('Unknown option: ' . $argument);
			}
			$options[$parts[0]] = $parts[1];
		} else if ($options['host'] === null) {
			$options['host'] = $argument;
		} else {
			throw new InvalidArgumentException('Only one hostname may be supplied.');
		}
	}
	return $options;
}

function wpts_leaflet_test_normalize_host(?string $host): string {
	if (empty($host)) {
		throw new InvalidArgumentException('A hostname is required.');
	}
	if (!str_contains($host, '://')) {
		$host = 'http://' . $host;
	}

	if (!wpts_leaflet_is_valid_host($host)) {
		throw new InvalidArgumentException('Use an HTTP(S) hostname with an optional port, without credentials, path or query.');
	}

	return rtrim($host, '/');
}

function wpts_leaflet_is_valid_host(string $host) {
	$parts = parse_url($host);
	if ($parts === false) {
		return false;
	}

	if (empty($parts['host'])) {
		return false;
	}

	$validSchemes = array('http', 'https');
	if (empty($parts['scheme']) 
		|| !in_array($parts['scheme'], $validSchemes, true)) {
		return false;
	}

	if (isset($parts['user']) 
		|| isset($parts['pass'])
		|| isset($parts['query']) 
		|| isset($parts['fragment'])) {
		return false;
	}

	if (!in_array($parts['path'] ?? '', array('', '/'), true)) {
		return false;
	}

	if (preg_match('/[\s\x00-\x1f\x7f]/', $host)) {
		return false;
	}

	return true;
}

function wpts_leaflet_test_validate_mode(string $mode): void {
	if (!in_array($mode, array('all', 'load', 'rewrite'), true)) {
		throw new InvalidArgumentException('Mode must be all, load or rewrite.');
	}
}

function wpts_leaflet_test_normalize_plugin_path(string $path): string {
	$path = rtrim($path, '/');
	if (!preg_match('#^/wp-content/plugins/[a-zA-Z0-9_-]+$#D', $path)) {
		throw new InvalidArgumentException('Plugin path must be /wp-content/plugins/<directory>.');
	}
	return $path;
}

function wpts_leaflet_test_expect(bool $condition, string $message): void {
	if (!$condition) {
		throw new UnexpectedValueException($message);
	}
}

function wpts_leaflet_test_request(string $url, array $requestHeaders = array()): array {
	$headers = array();

	$handle = wpts_leaflet_test_create_http_request($url, 
		$requestHeaders, 
		$headers);

	$body = curl_exec($handle);
	$status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
	$error = curl_error($handle);
	
	if ($body === false) {
		throw new RuntimeException('HTTP request failed: ' . $url . PHP_EOL . $error);
	}

	return array(
		'url' => $url, 
		'status' => $status, 
		'headers' => $headers, 
		'body' => $body
	);
}

/** Send the path unchanged: cURL must not silently remove traversal segments. */
function wpts_leaflet_test_create_http_request(string $url, 
	array $requestHeaders, 
	array &$responseHeaders): CurlHandle {
	$handle = curl_init($url);
	curl_setopt_array($handle, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_PATH_AS_IS => true,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT => 15,
		CURLOPT_HTTPHEADER => array_merge(
			array('Accept-Encoding: identity'), 
			$requestHeaders
		),
		CURLOPT_USERAGENT => 'WP-Trip-Summary-Leaflet-Wrapper-Tests',
		CURLOPT_HEADERFUNCTION => static function($handle, string $line) use (&$responseHeaders): int {
			return wpts_leaflet_test_collect_response_header($responseHeaders, $line);
		}
	));
	return $handle;
}

function wpts_leaflet_test_collect_response_header(array &$headers, string $line): int {
	if (preg_match('#^HTTP/\S+ \d{3}#', $line)) {
		$headers = array();
	} else if (str_contains($line, ':')) {
		list($name, $value) = explode(':', $line, 2);
		$name = strtolower(trim($name));
		$headers[$name] = isset($headers[$name])
			? $headers[$name] . ', ' . trim($value)
			: trim($value);
	}
	return strlen($line);
}

function wpts_leaflet_test_url(array $options, string $mode, string $script, array $query = array()): string {
	$pluginUrl = $options['host'] . $options['plugin-path'] . '/';
	if ($mode === 'load') {
		$url = $pluginUrl . 'abp01-plugin-leaflet-plugins-wrapper.php';
		$query = array_merge(
			array('load' => $script), 
			$query
		);
	} else {
		$url = $pluginUrl . $script;
	}

	return $url . (empty($query) 
		? '' 
		: '?' . http_build_query($query, 
			'', 
			'&', 
			PHP_QUERY_RFC3986)
		);
}

function wpts_leaflet_test_status(array $response, array $expected): void {
	wpts_leaflet_test_expect(in_array($response['status'], $expected, true),
		'Expected HTTP ' . implode('/', $expected) . ', received ' . $response['status'] .
		' (' . strlen($response['body']) . ' bytes).' . PHP_EOL . '  ' . $response['url']);
}

function wpts_leaflet_test_script(array $response, string $source, string $version = ''): void {
	wpts_leaflet_test_status($response, array(200));
	wpts_leaflet_test_script_headers($response);
	wpts_leaflet_test_script_content($response['body'], $source);
	wpts_leaflet_test_etag($response['headers'], $version);
	wpts_leaflet_test_cache_control($response['headers']);
	wpts_leaflet_test_expires($response['headers']);
}

function wpts_leaflet_test_script_headers(array $response): void {
	$headers = $response['headers'];
	wpts_leaflet_test_expect((bool)preg_match('#^application/javascript\s*;\s*charset=utf-8$#i',
		$headers['content-type'] ?? ''), 'Expected JavaScript with UTF-8 charset.');
	wpts_leaflet_test_expect(($headers['content-length'] ?? '') === (string)strlen($response['body']),
		'Content-Length must equal the response length in bytes.');
}

function wpts_leaflet_test_expected_script(string $source): string {
	$bom = pack('H*','EFBBBF');
	$source = wpts_leaflet_normalize_new_lines($source);
	$source = trim(preg_replace("/^$bom/", '', $source));
	return '(function (L) {' . PHP_EOL 
		. trim($source) . PHP_EOL .
		'})(window.' . ABP01_WRAPPED_LEAFLET_CONTEXT . ');';
}

function wpts_leaflet_normalize_new_lines(string $source): string {
	return str_replace("\r\n", "\n", $source);
}

function wpts_leaflet_test_script_content(string $body, string $source): void {
	$expected = wpts_leaflet_test_expected_script($source);
	$actual = wpts_leaflet_normalize_new_lines($body);
	wpts_leaflet_test_expect($actual === $expected,
		'Wrapped content differs from the local script (expected SHA-256 ' . hash('sha256', $expected) .
		', received ' . hash('sha256', $actual) . ').');
}

function wpts_leaflet_test_etag(array $headers, string $version): void {
	$etag = $version === '' ? ABP01_VERSION : $version . '-' . ABP01_VERSION;
	// Accept the legacy bare ETag as well as a quoted HTTP entity tag.
	wpts_leaflet_test_expect(trim($headers['etag'] ?? '', '"') === $etag, 'Incorrect or missing ETag.');
}

function wpts_leaflet_test_cache_control(array $headers): void {
	$cacheControl = strtolower($headers['cache-control'] ?? '');
	wpts_leaflet_test_expect((bool)preg_match('/(?:^|,)\s*public\s*(?:,|$)/', $cacheControl)
		&& (bool)preg_match('/(?:^|,)\s*max-age=' . ABP01_WRAPPED_SCRIPT_MAX_AGE . '\s*(?:,|$)/', $cacheControl),
		'Cache-Control must be public with the configured max-age.');
}

function wpts_leaflet_test_expires(array $headers): void {
	$date = strtotime($headers['date'] ?? '');
	$expires = strtotime($headers['expires'] ?? '');
	wpts_leaflet_test_expect($date !== false && $expires !== false
		&& abs($expires - $date - ABP01_WRAPPED_SCRIPT_MAX_AGE) <= 5,
		'Expires must agree with Date and the configured max-age (within 5 seconds).');
}

function wpts_leaflet_test_denied(array $response, array $statuses = array(400, 403, 404)): void {
	wpts_leaflet_test_status($response, $statuses);
	wpts_leaflet_test_expect(!str_contains($response['body'], '(function (L) {'),
		'A rejected request must not contain a wrapped script.');
}

function wpts_leaflet_test_run(string $name, callable $test, array &$totals): void {
	try {
		$test();
		$totals['passed']++;
		wpts_tools_format_print('[PASS] ' . $name . PHP_EOL, ['green']);
	} catch (UnexpectedValueException $error) {
		$totals['failed']++;
		wpts_tools_format_print('[FAIL] ' . $name . PHP_EOL . '  ' . $error->getMessage() . PHP_EOL, ['red', 'bold']);
	}
}

function wpts_leaflet_test_suite(array $options): int {
	$leafletDirectory = 'media/js/3rdParty/leaflet-plugins/';
	$scripts = wpts_leaflet_test_script_paths($leafletDirectory);
	$sources = wpts_leaflet_test_read_sources(dirname(__DIR__, 2), $scripts);
	$totals = array('passed' => 0, 'failed' => 0);
	$modes = $options['mode'] === 'all' ? array('load', 'rewrite') : array($options['mode']);
	$script = $scripts[0];
	$source = $sources[$script];
	$missing = $script . '.missing-' . bin2hex(random_bytes(6)) . '.js';

	wpts_leaflet_test_print_suite_header($options, $modes);
	wpts_leaflet_test_verify_traversal_targets($options);
	foreach ($modes as $mode) {
		wpts_leaflet_test_run_script_cases($options, $mode, $sources, $totals);
		wpts_leaflet_test_run_path_cases($options, $mode, $script, $missing, $totals);
		wpts_leaflet_test_run_cache_cases($options, $mode, $script, $source, $missing, $totals);
		wpts_leaflet_test_run_access_cases($options, $mode, $leafletDirectory, $script, $totals);
	}
	if (in_array('load', $modes, true)) {
		wpts_leaflet_test_run_load_cases($options, $totals);
	}
	if (in_array('rewrite', $modes, true)) {
		wpts_leaflet_test_run_rewrite_cases($options, $sources, $missing, $totals);
	}
	return wpts_leaflet_test_report_totals($totals);
}

function wpts_leaflet_test_script_paths(string $leafletDirectory): array {
	return array(
		$leafletDirectory . 'leaflet-fullscreen/leaflet.fullscreen.js',
		$leafletDirectory . 'leaflet-magnifyingglass/leaflet.magnifyingglass.js',
		$leafletDirectory . 'leaflet-magnifyingglass/leaflet.magnifyingglass.button.js'
	);
}

function wpts_leaflet_test_read_sources(string $pluginDirectory, array $scripts): array {
	$sources = array();
	foreach ($scripts as $script) {
		$source = @file_get_contents($pluginDirectory . '/' . $script);
		if ($source === false) {
			throw new RuntimeException('Cannot read local reference script: ' . $script);
		}
		$sources[$script] = $source;
	}
	return $sources;
}

function wpts_leaflet_test_print_suite_header(array $options, array $modes): void {
	echo 'Target: ' . $options['host'] . $options['plugin-path'] . PHP_EOL;
	echo 'Modes: ' . implode(', ', $modes) . PHP_EOL . PHP_EOL;
}

/** Check that traversal targets exist on the host, independently of the local checkout. */
function wpts_leaflet_test_verify_traversal_targets(array $options): void {
	foreach (array($options['plugin-path'] . '/media/js/abp01-common.js',
		'/wp-includes/js/jquery/jquery.js') as $target) {
		$response = wpts_leaflet_test_request($options['host'] . $target);
		if ($response['status'] !== 200 || $response['body'] === '') {
			throw new RuntimeException('Traversal reference URL must serve an existing public script: ' .
				$response['url'] . ' (HTTP ' . $response['status'] . ').');
		}
	}
}

function wpts_leaflet_test_run_script_cases(array $options, string $mode, array $sources, array &$totals): void {
	foreach ($sources as $path => $contents) {
		foreach (array('', '1.2.3-test_4') as $version) {
			$name = $mode . ': ' . basename($path) . ($version === '' ? '' : ' with ver');
			wpts_leaflet_test_run($name, static function() use ($options, $mode, $path, $contents, $version) {
				$query = $version === '' ? array() : array('ver' => $version);
				$response = wpts_leaflet_test_request(wpts_leaflet_test_url($options, $mode, $path, $query));
				wpts_leaflet_test_script($response, $contents, $version);
			}, $totals);
		}
	}
}

function wpts_leaflet_test_run_path_cases(array $options, string $mode, string $script,
	string $missing, array &$totals): void {
	// The suffix still matches the real rewrite rule, so Apache invokes the
	// wrapper even though the requested file does not exist.
	wpts_leaflet_test_run($mode . ': missing script', static function() use ($options, $mode, $missing) {
		wpts_leaflet_test_denied(wpts_leaflet_test_request(wpts_leaflet_test_url($options, $mode, $missing)), array(404));
	}, $totals);
	wpts_leaflet_test_run($mode . ': non-JavaScript extension', static function() use ($options, $mode, $script) {
		wpts_leaflet_test_denied(wpts_leaflet_test_request(wpts_leaflet_test_url($options, $mode, $script . '.css')));
	}, $totals);
}

function wpts_leaflet_test_run_cache_cases(array $options, string $mode, string $script,
	string $source, string $missing, array &$totals): void {
	$cacheUrl = wpts_leaflet_test_url($options, $mode, $script, array('ver' => 'cache-test.1'));
	$cached = wpts_leaflet_test_request($cacheUrl);
	wpts_leaflet_test_run($mode . ': cache headers', static function() use ($cached, $source) {
		wpts_leaflet_test_script($cached, $source, 'cache-test.1');
	}, $totals);
	$cacheCases = array('repeat', 'matching ETag', 'stale ETag', 'changed version', 'missing with ETag');
	foreach ($cacheCases as $cacheCase) {
		wpts_leaflet_test_run($mode . ': ' . $cacheCase, static function() use ($options, $mode,
			$script, $source, $missing, $cached, $cacheCase) {
			$etag = wpts_leaflet_test_cached_etag($cached);
			$response = wpts_leaflet_test_cache_case_request($options, $mode, $script, $missing, $cacheCase, $etag);
			wpts_leaflet_test_cache_case_response($response, $cached, $source, $cacheCase);
		}, $totals);
	}
}

function wpts_leaflet_test_cached_etag(array $cached): string {
	wpts_leaflet_test_status($cached, array(200));
	$etag = $cached['headers']['etag'] ?? '';
	wpts_leaflet_test_expect($etag !== '', 'Cannot test cache revalidation without an ETag.');
	return $etag;
}

function wpts_leaflet_test_cache_case_request(array $options, string $mode, string $script,
	string $missing, string $cacheCase, string $etag): array {
	$url = wpts_leaflet_test_url($options, $mode, $script, array('ver' => 'cache-test.1'));
	$headers = array();
	if ($cacheCase !== 'repeat') {
		$headers[] = 'If-None-Match: ' . ($cacheCase === 'stale ETag' ? '"wpts-stale-etag"' : $etag);
	}
	if ($cacheCase === 'changed version') {
		$url = wpts_leaflet_test_url($options, $mode, $script, array('ver' => 'cache-test.2'));
	} else if ($cacheCase === 'missing with ETag') {
		$url = wpts_leaflet_test_url($options, $mode, $missing, array('ver' => 'cache-test.1'));
	}
	return wpts_leaflet_test_request($url, $headers);
}

function wpts_leaflet_test_cache_case_response(array $response, array $cached, string $source, string $cacheCase): void {
	if ($cacheCase === 'matching ETag') {
		wpts_leaflet_test_status($response, array(304));
		wpts_leaflet_test_expect($response['body'] === '', 'A 304 response must have no body.');
	} else if ($cacheCase === 'missing with ETag') {
		wpts_leaflet_test_denied($response, array(404));
	} else {
		$version = $cacheCase === 'changed version' ? 'cache-test.2' : 'cache-test.1';
		wpts_leaflet_test_script($response, $source, $version);
		wpts_leaflet_test_expect($response['body'] === $cached['body'], 'Cache requests changed script content.');
		wpts_leaflet_test_expect(($response['headers']['etag'] === $cached['headers']['etag']) === ($cacheCase !== 'changed version'),
			'ETag must remain stable unless ver changes.');
	}
}

function wpts_leaflet_test_invalid_loads(array $options, string $leafletDirectory, string $script): array {
	$outsideLeaflet = $leafletDirectory . '../../abp01-common.js';
	$outsidePlugin = $leafletDirectory . str_repeat('../', 7) . 'wp-includes/js/jquery/jquery.js';
	return array(
		'directory' => $leafletDirectory . 'leaflet-fullscreen/',
		'existing CSS file' => $leafletDirectory . 'leaflet-fullscreen/leaflet.fullscreen.css',
		'existing script outside Leaflet' => 'media/js/abp01-common.js',
		'existing script outside plugin' => '/wp-includes/js/jquery/jquery.js',
		'relative traversal outside Leaflet' => $outsideLeaflet,
		'root-relative traversal outside Leaflet' => $options['plugin-path'] . '/' . $outsideLeaflet,
		'relative traversal outside plugin' => $outsidePlugin,
		'root-relative traversal outside plugin' => $options['plugin-path'] . '/' . $outsidePlugin,
		'double-encoded traversal' => str_replace('../', '%2e%2e%2f', $outsidePlugin),
		'backslash traversal' => str_replace('/', '\\', $outsidePlugin),
		'external URL' => 'https://example.invalid/' . $script,
		'null byte' => $script . "\0",
		'array load parameter' => array($script)
	);
}

function wpts_leaflet_test_run_access_cases(array $options, string $mode, string $leafletDirectory,
	string $script, array &$totals): void {
	// On a rewritten URL, load must take precedence over REQUEST_URI.
	// This also routes invalid input to PHP instead of testing Apache's
	// handling of arbitrary static URLs outside the wrapper's rewrite rules.
	$invalidLoads = wpts_leaflet_test_invalid_loads($options, $leafletDirectory, $script);
	foreach ($invalidLoads as $label => $load) {
		wpts_leaflet_test_run($mode . ': reject ' . $label, static function() use ($options, $mode, $script, $load) {
			$url = wpts_leaflet_test_url($options, $mode, $script, array('load' => $load));
			wpts_leaflet_test_denied(wpts_leaflet_test_request($url));
		}, $totals);
	}
	$outsideLeaflet = $invalidLoads['root-relative traversal outside Leaflet'];
	wpts_leaflet_test_run($mode . ': invalid path with matching ETag', static function() use ($options,
		$mode, $script, $outsideLeaflet) {
		$url = wpts_leaflet_test_url($options, $mode, $script, array('load' => $outsideLeaflet));
		wpts_leaflet_test_denied(wpts_leaflet_test_request($url, array('If-None-Match: ' . ABP01_VERSION)));
	}, $totals);
}

function wpts_leaflet_test_run_load_cases(array $options, array &$totals): void {
	foreach (array('missing load' => array(), 'empty load' => array('load' => '')) as $label => $query) {
		wpts_leaflet_test_run('load: ' . $label, static function() use ($options, $query) {
			$url = wpts_leaflet_test_url($options, 'rewrite', 'abp01-plugin-leaflet-plugins-wrapper.php', $query);
			wpts_leaflet_test_denied(wpts_leaflet_test_request($url), array(404));
		}, $totals);
	}
}

function wpts_leaflet_test_run_rewrite_cases(array $options, array $sources, string $missing, array &$totals): void {
	$scripts = array_keys($sources);
	$script = $scripts[0];
	$source = $sources[$script];
	foreach (array('empty load falls back to REQUEST_URI' => array('load' => ''),
		'unrelated query parameters' => array('test' => 'ignored')) as $label => $query) {
		wpts_leaflet_test_run('rewrite: ' . $label, static function() use ($options, $script, $source, $query) {
			$response = wpts_leaflet_test_request(wpts_leaflet_test_url($options, 'rewrite', $script, $query));
			wpts_leaflet_test_script($response, $source);
		}, $totals);
	}
	wpts_leaflet_test_run('rewrite: load takes precedence over REQUEST_URI', static function() use ($options, $scripts, $sources) {
		$url = wpts_leaflet_test_url($options, 'rewrite', $scripts[0], array('load' => $scripts[1]));
		wpts_leaflet_test_script(wpts_leaflet_test_request($url), $sources[$scripts[1]]);
	}, $totals);
	wpts_leaflet_test_run('rewrite: nonexistent load does not fall back to REQUEST_URI', static function() use ($options, $script, $missing) {
		$url = wpts_leaflet_test_url($options, 'rewrite', $script, array('load' => $missing));
		wpts_leaflet_test_denied(wpts_leaflet_test_request($url), array(404));
	}, $totals);
}

function wpts_leaflet_test_report_totals(array $totals): int {
	echo PHP_EOL . sprintf('%d passed, %d failed.', $totals['passed'], $totals['failed']) . PHP_EOL;
	return $totals['failed'] === 0 ? 0 : 1;
}

function wpts_leaflet_test_bootstrap(): void {
	if (!extension_loaded('curl')) {
		throw new RuntimeException('The PHP cURL extension is required.');
	}
	// Only load constants; including the wrapper itself would serve and exit.
	define('ABSPATH', dirname(__DIR__, 5) . '/');
	require dirname(__DIR__, 2) . '/abp01-plugin-wpshim.php';
	require dirname(__DIR__, 2) . '/abp01-plugin-header.php';
}

function wpts_leaflet_test_main(array $arguments): int {
	if (in_array('--help', $arguments, true)) {
		echo wpts_leaflet_test_usage();
		return 0;
	}
	try {
		$options = wpts_leaflet_test_options($arguments);
		wpts_leaflet_test_bootstrap();
		return wpts_leaflet_test_suite($options);
	} catch (InvalidArgumentException $error) {
		fwrite(STDERR, $error->getMessage() . PHP_EOL . PHP_EOL . wpts_leaflet_test_usage());
		return 2;
	} catch (Throwable $error) {
		fwrite(STDERR, '[ERROR] ' . $error->getMessage() . PHP_EOL);
		return 2;
	}
}

exit(wpts_leaflet_test_main(array_slice($argv, 1)));
