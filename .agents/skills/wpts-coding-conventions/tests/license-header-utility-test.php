<?php
declare(strict_types=1);

require_once __DIR__ . '/license-header-test-helpers.php';

use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderReader;

$script = __DIR__ . '/../scripts/license-header-utility.php';

function runLicenseHeaderCli(array $arguments, int $expectedExitCode = 0): array {
	global $script;
	$result = runLicenseHeaderPhp(array_merge(
		array($script), 
		$arguments));

	assertLicenseHeaderSame($expectedExitCode, 
		$result['exitCode'],
		'Unexpected CLI status for ' 
			. implode(' ', $arguments) . "\n" 
			. $result['error']);
	
	if ($expectedExitCode === 0) {
		assertLicenseHeaderSame('', 
			$result['error'], 
			'Successful CLI calls should not report errors.');
	}

	return $result;
}

withLicenseHeaderTestDirectory(function(string $directory): void {
	$file = $directory . '/class with spaces.php';
	$source = file_get_contents(__DIR__ . '/license-header-files/ClassWithNoLicenseHeader.php');
	file_put_contents($file, $source);

	$missing = runLicenseHeaderCli(array($file, 
		'--json'));
	assertLicenseHeaderSame(null, 
		json_decode($missing['output'], true, 512, JSON_THROW_ON_ERROR),
		'JSON read should return null for a missing header.');

	runLicenseHeaderCli(array($file, '--check', '--year=2030'), 
		1);

	$preview = runLicenseHeaderCli(array($file, 	
		'--update', 
		'--year=2030', 
		'--dry-run'));

	assertLicenseHeaderSame($source, 
		file_get_contents($file), 
		'CLI preview must not modify the file.');

	runLicenseHeaderCli(array($file, 
		'--update', 
		'--year=2030'));

	assertLicenseHeaderSame($preview['output'], 
		file_get_contents($file), 
		'CLI preview should equal the written source.');

	runLicenseHeaderCli(array($file, 
		'--check', 
		'--year=2030'));
	runLicenseHeaderCli(array($file, 
			'--check', 
			'--year=2031'), 
		1);

	$report = runLicenseHeaderCli(array($file, 
		'--read', 
		'--json'));

	$reader = new LicenseHeaderReader($file);
	$header = $reader->read();

	assertLicenseHeaderSame($header->toArray(), 
		json_decode($report['output'], true, 512, JSON_THROW_ON_ERROR),
		'JSON read should return the complete header metadata.');

	$full = runLicenseHeaderCli(array($file, '--full'));
	assertLicenseHeaderSame(true, 
		str_contains($full['output'], $header->normalizedHeader()),
		'Full read should include the complete comment.');

	$before = file_get_contents($file);
	runLicenseHeaderCli(array($file, 
		'--update', 
		'--year=2030'));
	assertLicenseHeaderSame($before, 
		file_get_contents($file), 
		'Repeated CLI update must not change the file.');

	$outdated = str_replace('Redistribution and use in source and binary forms',
		'Outdated license wording',
		$before);
	
	assertLicenseHeaderSame(false, 
		$outdated === $before,
		'Test source should contain modified license text.');

	file_put_contents($file, $outdated);
	
	runLicenseHeaderCli(array($file, '--check', '--year=2030'), 
		1);
	assertLicenseHeaderSame($outdated, 
		file_get_contents($file),
		'Checking the template must not modify the file.');

	runLicenseHeaderCli(array($file, '--update', '--year=2030'));
	assertLicenseHeaderSame($before, 
		file_get_contents($file),
		'Update should restore the full template even when the year is already correct.');

	runLicenseHeaderCli(array($file, 
		'--check', 
		'--year=2030'));
	runLicenseHeaderCli(array($file, 
			'--check', 
			'--year=2029'), 
		1);

	$currentFile = $directory . '/current.php';
	file_put_contents($currentFile, $source);
	runLicenseHeaderCli(array($currentFile, '--update'));

	$reader = new LicenseHeaderReader($currentFile);

	assertLicenseHeaderSame(intval(date('Y')), 
		$reader->read()?->year,
		'CLI update should default to the current year.');
	runLicenseHeaderCli(array($currentFile, 
		'--check'));

	$withInvalidOptions = array(
		array(),
		array($file, '--unknown'),
		array($file, '--update', '--year=invalid'),
		array($file, '--update', '--year=2013'),
		array($file, '--read', '--update'),
		array($file, '--dry-run'),
		array($file, '--update', '--json')
	);

	foreach ($withInvalidOptions as $arguments) {
		runLicenseHeaderCli($arguments, 
			2);
	}

	assertLicenseHeaderSame($before, 
		file_get_contents($file), 
		'Invalid arguments must not modify the target.');

	$missingFile = runLicenseHeaderCli(array($directory . '/missing.php'), 
		1);

	assertLicenseHeaderSame(true, 
		str_contains($missingFile['error'], 'source file'),
		'Missing files should produce a concise error.');

	$unsupported = $directory . '/settings.json';
	file_put_contents($unsupported, "{}\n");
	
	runLicenseHeaderCli(array($unsupported, '--update'), 
		1);
	assertLicenseHeaderSame("{}\n", 
		file_get_contents($unsupported), 
		'Unsupported files must remain untouched.');

	runLicenseHeaderCli(array('--help'));
	echo "License header CLI tests passed.\n";
});
