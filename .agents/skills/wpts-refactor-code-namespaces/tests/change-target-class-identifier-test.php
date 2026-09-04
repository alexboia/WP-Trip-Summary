<?php

declare(strict_types=1);

$script = realpath(__DIR__ . '/../scripts/change-target-class-identifier.php');
if ($script === false) {
	fwrite(STDERR, "Script under test not found.\n");
	exit(1);
}

$temporaryDir = sys_get_temp_dir() . '/wpts-namespace-test-' . bin2hex(random_bytes(6));
if (!mkdir($temporaryDir)) {
	fwrite(STDERR, "Could not create temporary test directory.\n");
	exit(1);
}

function removeTestDirectory(string $directory): void {
	foreach (glob($directory . '/*') ?: array() as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}
	rmdir($directory);
}

function assertContains(string $expected, string $actual, string $message): void {
	if (!str_contains($actual, $expected)) {
		throw new RuntimeException($message . "\nMissing: " . $expected);
	}
}

function runConverter(string $script, string $file, string $legacyName, bool $dryRun = false): array {
	$command = escapeshellarg(PHP_BINARY)
		. ' ' . escapeshellarg($script)
		. ' ' . escapeshellarg($file)
		. ' ' . escapeshellarg($legacyName)
		. ($dryRun ? ' --dry-run' : '');
	$output = array();
	$exitCode = 0;
	exec($command, $output, $exitCode);
	return array(implode(PHP_EOL, $output), $exitCode);
}

function getTestSourceNoUsings(): string {
	$source = <<<'PHP'
<?php
/** The word use in a comment is not an import. */

class Abp01_Example extends Abp01_Base implements Abp01_First, Abp01_Second {
	public static function make(): Abp01_Example {
		$className = Abp01_Dynamic::class;
		$dynamic = new $className();
		new Abp01_Service();
		if ($dynamic instanceof Abp01_Contract) {
			Abp01_StaticTool::run();
		}
		try {
			$dynamicNew = new Abp01_Dynamic();
		} catch (Abp01_FirstException|Abp01_SecondException $exception) {}
		return new Abp01_Example();
	}
}
PHP;
	return $source;
}

function getTestSourceWithUsings(): string {
	$source = <<<'PHP'
<?php 
use Existing\\Dependency;
class Abp01_ExistingUse { 
	public function make() { 
		return new Abp01_Other(); 
	} 
}
PHP;
	return $source;
}

try {
	$source = getTestSourceNoUsings();
	$file = $temporaryDir . '/Example.php';
	file_put_contents($file, $source);

	//1. Dry run must not modify the file, 
	//	but preview still needs to be the correctly 
	//	converted contents
	$beforeDryRun = hash_file('sha256', $file);
	[$preview, $previewExitCode] = runConverter($script, 
		$file, 
		'Abp01_Example', 
		true);

	if ($previewExitCode !== 0 || hash_file('sha256', $file) !== $beforeDryRun) {
		throw new RuntimeException('Dry-run failed or modified the source file.');
	}

	assertContains(
		'namespace WpTripSummary {', 
		$preview, 
		'Derived namespace is missing.'
	);

	//2. Main convertion flow needs to be valid
	[, $exitCode] = runConverter($script, 
		$file, 
		'Abp01_Example');

	if ($exitCode !== 0) {
		throw new RuntimeException("Conversion failed with exit code {$exitCode}.");
	}

	$converted = (string) file_get_contents($file);

	assertContains(
		'namespace WpTripSummary {', 
		$converted, 
		'Derived namespace is missing.'
	);

	foreach (array(
		'use \\Abp01_Base;',
		'use \\Abp01_First;',
		'use \\Abp01_Second;',
		'use \\Abp01_Dynamic;',
		'use \\Abp01_Service;',
		'use \\Abp01_Contract;',
		'use \\Abp01_StaticTool;',
		'use \\Abp01_FirstException;',
		'use \\Abp01_SecondException;',
		'class Example',
		'new Example()',
	) as $expected) {
		assertContains(
			$expected, 
			$converted, 
			'Converted output is incomplete.'
		);
	}

	if (substr_count($converted, 'use \\Abp01_Dynamic;') !== 1) {
		throw new RuntimeException('Dependencies were not deduplicated.');
	}

	//3. Existing imports need to be respected and none other added
	$existingUseFile = $temporaryDir . '/ExistingUse.php';
	file_put_contents($existingUseFile, 
		getTestSourceWithUsings()
	);
	
	[, $existingUseExitCode] = runConverter($script, 
		$existingUseFile, 
		'Abp01_ExistingUse');

	$existingUseResult = (string) file_get_contents($existingUseFile);
	if ($existingUseExitCode !== 0 
		|| str_contains($existingUseResult, 'use \\Abp01_Other;')) {
		throw new RuntimeException('Existing top-level imports were not respected.');
	}

	echo "All change-target-class-identifier tests passed.\n";
} finally {
	removeTestDirectory($temporaryDir);
}
