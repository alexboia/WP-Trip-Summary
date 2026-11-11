<?php
declare(strict_types=1);

function wpts_release_read_file(string $file): string {
	$contents = file_get_contents($file);
	if ($contents === false) {
		throw new RuntimeException('Cannot read release input: ' . $file);
	}
	return $contents;
}

function wpts_release_read_version(string $root, string $readme): string {
	$header = substr(wpts_release_read_file($root . '/abp01-plugin-main.php'), 0, 8192);
	$constants = wpts_release_read_file($root . '/abp01-plugin-header.php');
	$readmeContents = wpts_release_read_file($readme);
	if (!preg_match('/^[ \t\/*#@]*Version:[ \t]*([^\r\n]+)/mi', $header, $headerMatch)) {
		throw new RuntimeException('The plugin Version header is missing.');
	}
	$version = trim($headerMatch[1]);
	if (!preg_match('/^\d+(?:\.\d+){1,3}(?:-[A-Za-z0-9.-]+)?$/D', $version)) {
		throw new RuntimeException('Invalid release version: ' . $version);
	}
	if (!preg_match('/^[ \t]*define\(\s*([\'"])ABP01_VERSION\1\s*,\s*([\'"])([^\'"]+)\2\s*\)\s*;/m', $constants, $constantMatch)) {
		throw new RuntimeException('The literal ABP01_VERSION definition is missing.');
	}
	if ($constantMatch[3] !== $version) {
		throw new RuntimeException('The Version header and ABP01_VERSION do not match.');
	}
	if (!preg_match('/^Stable tag:[ \t]*([^\r\n]+)/mi', $readmeContents, $readmeMatch)) {
		throw new RuntimeException('The generated readme Stable tag is missing.');
	}
	if (trim($readmeMatch[1]) !== $version) {
		throw new RuntimeException('The generated readme Stable tag does not match the plugin version.');
	}
	return $version;
}

try {
	if ($argc !== 3) {
		throw new InvalidArgumentException('Usage: php version.php <plugin-root> <generated-readme>');
	}
	echo wpts_release_read_version($argv[1], $argv[2]), PHP_EOL;
} catch (Throwable $error) {
	fwrite(STDERR, $error->getMessage() . PHP_EOL);
	exit(1);
}
