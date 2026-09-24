<?php
declare(strict_types=1);

require_once __DIR__ . '/../scripts/LicenseHeader.php';
require_once __DIR__ . '/../scripts/LicenseHeaderReader.php';
require_once __DIR__ . '/../scripts/LicenseHeaderGenerator.php';
require_once __DIR__ . '/../scripts/LicenseHeaderUpdater.php';

function assertLicenseHeaderSame(mixed $expected, mixed $actual, string $message): void {
	if ($expected !== $actual) {
		throw new RuntimeException(
			$message 
			. "\nExpected: " . var_export($expected, true) 
			. "\nActual: " . var_export($actual, true)
		);
	}
}

function runLicenseHeaderPhp(array $arguments): array {
	$command = array_merge(array(PHP_BINARY), $arguments);
	$process = proc_open($command, array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w')
	), $pipes);

	if (!is_resource($process)) {
		throw new RuntimeException('Could not start PHP test process.');
	}

	fclose($pipes[0]);
	$output = stream_get_contents($pipes[1]);
	$error = stream_get_contents($pipes[2]);
	
	fclose($pipes[1]);
	fclose($pipes[2]);

	return array(
		'exitCode' => proc_close($process),
		'output' => $output,
		'error' => $error
	);
}

function withLicenseHeaderTestDirectory(callable $test): void {
	$directory = sys_get_temp_dir() 
		. '/wpts-license-header-' 
		. bin2hex(random_bytes(8));

	if (!mkdir($directory)) {
		throw new RuntimeException('Could not create temporary test directory.');
	}

	try {
		$test($directory);
	} finally {
		//Tests create only files directly inside their own temporary directory.
		foreach (glob($directory . '/*') ?: array() as $file) {
			unlink($file);
		}
		rmdir($directory);
	}
}
