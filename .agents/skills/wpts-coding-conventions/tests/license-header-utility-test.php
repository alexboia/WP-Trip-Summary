<?php
declare(strict_types=1);
require_once __DIR__ . '/license-header-test-helpers.php';

use PharIo\Manifest\License;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeader;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderReader;

$licenseHeaderUtilityScript = licenseHeaderUtilityScript();

function runLicenseHeaderCli(array $arguments, int $expectedExitCode = 0): array {
	global $licenseHeaderUtilityScript;
	$result = runLicenseHeaderPhp(array_merge(
		array($licenseHeaderUtilityScript), 
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

function arrangeTestFile(string $testDirectory, 
		string $source, 
		string $testFileName): string {
	
	$testFile = $testDirectory . '/' . $testFileName;
	file_put_contents($testFile, $source);
	
	return $testFile;
}

function runWhenMissingHeaderTest(string $testFile): void {
	$missing = runLicenseHeaderCli(array($testFile, 
		'--json'));
	assertLicenseHeaderSame(null, 
		json_decode($missing['output'], true, 512, JSON_THROW_ON_ERROR),
		'JSON read should return null for a missing header.');

	runLicenseHeaderCli(array($testFile, '--check', '--year=2030'), 
		1);
}

function runDryRunDoesNotModifyTestFileTest(string $testFile, string $sourceWithoutLicenseHeader, int $toYear = 2030): array {
	$preview = runLicenseHeaderCli(array($testFile, 	
		'--update', 
		'--year=' . $toYear, 
		'--dry-run'));

	assertLicenseHeaderSame($sourceWithoutLicenseHeader, 
		file_get_contents($testFile), 
		'CLI preview must not modify the file.');

	return $preview;
}

function runUpdateAfterDrRunTest(string $testFile, array $previewModified, int $toYear = 2030) {
	runLicenseHeaderCli(array($testFile, 
		'--update', 
		'--year=' . $toYear));

	assertLicenseHeaderSame($previewModified['output'], 
		file_get_contents($testFile), 
		'CLI preview should equal the written source.');
}

function runCurrentYearVsOtherYearTest(string $testFile, int $currentYear = 2030, int $otherYear = 2031) {
	runLicenseHeaderCli(array($testFile, 
		'--check', 
		'--year=' . $currentYear
	));
	runLicenseHeaderCli(array($testFile, 
			'--check', 
			'--year=' . $otherYear
		), 
		1);
}

function runUtilityJsonReportVersusDirectReadTests(string $testFile): ?LicenseHeader {
	$report = runLicenseHeaderCli(array($testFile, 
		'--read', 
		'--json'));

	$reader = new LicenseHeaderReader($testFile);
	$header = $reader->read();

	$utilityReport = json_decode($report['output'], 
		true, 
		512, 
		JSON_THROW_ON_ERROR);

	assertLicenseHeaderSame($header->toArray(), 
		$utilityReport,
		'JSON read should return the complete header metadata.');

	return $header;
}

function runUtilityFullReadVersusExplicitHeaderTests(string $testFile, LicenseHeader $header) {
	$full = runLicenseHeaderCli(array($testFile, 
		'--full'));

	assertLicenseHeaderSame(true, 
		str_contains($full['output'], $header->normalizedHeader()),
		'Full read should include the complete comment.');
}

function runRepeatedlyUpdateTest(string $testFile, int $toYear = 2030): string {
	$before = file_get_contents($testFile);
	runLicenseHeaderCli(array($testFile, 
		'--update', 
		'--year=' . $toYear));
	assertLicenseHeaderSame($before, 
		file_get_contents($testFile), 
		'Repeated CLI update must not change the file.');

	return (string)$before;
}

function runOutdatedContentCheckThenRestoreTest(string $testFile, string $previousContents, int $toYear = 2030) {
	$outdated = str_replace('Redistribution and use in source and binary forms',
		'Outdated license wording',
		$previousContents);
	
	assertLicenseHeaderSame(false, 
		$outdated === $previousContents,
		'Test source should contain modified license text.');

	file_put_contents($testFile, $outdated);
	
	runLicenseHeaderCli(array($testFile, '--check', '--year=' . $toYear), 
		1);
	assertLicenseHeaderSame($outdated, 
		file_get_contents($testFile),
		'Checking the template must not modify the file.');

	runLicenseHeaderCli(array($testFile, '--update', '--year=' . $toYear));
	assertLicenseHeaderSame($previousContents, 
		file_get_contents($testFile),
		'Update should restore the full template even when the year is already correct.');
}

function runUpdateToCurrentYearTest(string $testDirectory, string $source) {
	$currentFile = arrangeTestFile($testDirectory, 
		$source, 
		'current.php');

	runLicenseHeaderCli(array($currentFile, 
		'--update'));

	$reader = new LicenseHeaderReader($currentFile);

	assertLicenseHeaderSame(intval(date('Y')), 
		$reader->read()?->year,
		'CLI update should default to the current year.');

	runLicenseHeaderCli(array($currentFile, 
		'--check'));
}

function runInvalidOptionsTest(string $testFile, string $previousContents) {
	$withInvalidOptions = array(
		array(),
		array($testFile, '--unknown'),
		array($testFile, '--update', '--year=invalid'),
		array($testFile, '--update', '--year=2013'),
		array($testFile, '--read', '--update'),
		array($testFile, '--dry-run'),
		array($testFile, '--update', '--json')
	);

	foreach ($withInvalidOptions as $arguments) {
		runLicenseHeaderCli($arguments, 
			2);
	}

	assertLicenseHeaderSame($previousContents, 
		file_get_contents($testFile), 
		'Invalid arguments must not modify the target.');
}

function runMissingFileTest(string $testDirectory) {
	$missingFile = runLicenseHeaderCli(array($testDirectory . '/missing.php'), 
		1);

	assertLicenseHeaderSame(true, 
		str_contains($missingFile['error'], 'source file'),
		'Missing files should produce a concise error.');
}

function runUnsupportedFileTest(string $testDirectory) {
	$unsupported = $testDirectory . '/settings.json';
	file_put_contents($unsupported, "{}\n");
	
	runLicenseHeaderCli(array($unsupported, '--update'), 
		1);
	assertLicenseHeaderSame("{}\n", 
		file_get_contents($unsupported), 
		'Unsupported files must remain untouched.');
}

withLicenseHeaderTestDirectory(function(string $testDirectory): void {
	$sourceWithoutLicenseHeader = 
		testSourceFileContents('ClassWithNoLicenseHeader.php');

	$testFile = arrangeTestFile($testDirectory, 
		$sourceWithoutLicenseHeader, 
		'class with spaces.php');

	runWhenMissingHeaderTest($testFile);

	$previewModified = runDryRunDoesNotModifyTestFileTest($testFile, 
		$sourceWithoutLicenseHeader);

	runUpdateAfterDrRunTest($testFile, 
		$previewModified);

	runCurrentYearVsOtherYearTest($testFile);

	$header = runUtilityJsonReportVersusDirectReadTests($testFile);
	runUtilityFullReadVersusExplicitHeaderTests($testFile, 
		$header);

	$contentsBeforeRepeatedUpdate = runRepeatedlyUpdateTest($testFile);

	runOutdatedContentCheckThenRestoreTest($testFile, 
		$contentsBeforeRepeatedUpdate);

	runCurrentYearVsOtherYearTest($testFile, 
		2030, 
		2029);

	runUpdateToCurrentYearTest($testDirectory, 
		$sourceWithoutLicenseHeader);

	runInvalidOptionsTest($testFile, 
		$contentsBeforeRepeatedUpdate);

	runMissingFileTest($testDirectory);

	runUnsupportedFileTest($testDirectory);

	runLicenseHeaderCli(array('--help'));
	echo "License header CLI tests passed.\n";
});
