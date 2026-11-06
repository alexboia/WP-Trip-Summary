<?php
declare(strict_types=1);

// Keep the test core download aligned with the library locked by Composer.
try {
	$lockFile = __DIR__ . '/../../composer.lock';
	if (!is_readable($lockFile)) {
		throw new RuntimeException('composer.lock is missing. Install the project dependencies first.');
	}

	$lock = json_decode(file_get_contents($lockFile), true, 512, JSON_THROW_ON_ERROR);
	foreach ($lock['packages-dev'] ?? array() as $package) {
		if ($package['name'] === 'wp-phpunit/wp-phpunit') {
			$version = ltrim($package['version'], 'v');
			if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
				throw new RuntimeException('The WordPress test library must be locked to a stable release.');
			}
			echo $version, PHP_EOL;
			exit(0);
		}
	}
	throw new RuntimeException('wp-phpunit/wp-phpunit is missing from composer.lock.');
} catch (Throwable $error) {
	fwrite(STDERR, $error->getMessage() . PHP_EOL);
	exit(1);
}
