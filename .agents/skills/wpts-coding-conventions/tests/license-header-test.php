<?php
declare(strict_types=1);

require_once __DIR__ . '/license-header-test-helpers.php';

use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderGenerator;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderReader;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderUpdater;

$templateFile = __DIR__ . '/../references/.license-header';
$generator = new LicenseHeaderGenerator($templateFile);
$template = file_get_contents($templateFile);

assertLicenseHeaderSame(str_replace('$CURRENT_YEAR$', '2030', $template),
	$generator->generate(2030),
	'Generator should replace the year without changing the license text.');

assertLicenseHeaderSame($generator->generate(intval(date('Y'))),
	$generator->generateForCurrentYear(),
	'Generator should support the current year.');

function runCorePhpFileTest(string $testDirectory): void {
	$fixtures = array(
		'ClassWithLicenseHeader.php' => 2025,
		'ClassWithNoLicenseHeader.php' => null,
		'ClassWithNoLicenseHeaderPolutedOpenTag.php' => null,
		'view-file-with-license-header.php' => 2025,
		'view-file-with-no-license-header.php' => null
	);

	foreach ($fixtures as $name => $expectedYear) {
		$original = file_get_contents(__DIR__ . '/license-header-files/' . $name);
		foreach (array("\n", "\r\n") as $newLine) {
			$source = str_replace("\n", 
				$newLine, 
				str_replace("\r\n", 
					"\n", 
					$original));

			$pluginTestFile = $testDirectory . '/' . $name;
			file_put_contents($pluginTestFile, $source);
			
			$reader = new LicenseHeaderReader($pluginTestFile);
			$header = $reader->read();
			
			assertLicenseHeaderSame($expectedYear, 
				$header?->year, 
				$name . ': detect the existing header.');
			
			if ($header !== null) {
				assertLicenseHeaderSame($header->header, 
					substr($source, $header->offset, $header->length),
					$name . ': header text and byte span should agree.');

				assertLicenseHeaderSame(true, 
					str_ends_with($header->header, '*/'),
					$name . ': returned header should include its closing delimiter.');
				
				assertLicenseHeaderSame($header->toArray(), 
					json_decode($header->toJson(), true, 512, JSON_THROW_ON_ERROR),
					$name . ': JSON should preserve the header metadata.');
			}

			$updater = new LicenseHeaderUpdater($pluginTestFile);
			$preview = $updater->preview(2030);

			assertLicenseHeaderSame($source, 
				file_get_contents($pluginTestFile), 
				$name . ': preview must not write.');
			
			assertLicenseHeaderSame(true, 
				$updater->update(2030), 
				$name . ': first update should report a change.');

			$updated = file_get_contents($pluginTestFile);
			
			assertLicenseHeaderSame($preview, 
				$updated, 
				$name . ': preview should match the written result.');

			assertLicenseHeaderSame(2030, 
				(new LicenseHeaderReader($pluginTestFile))->read()?->year,
				$name . ': updated header should be readable.');

			if ($expectedYear !== null) {
				assertLicenseHeaderSame(str_replace('2014-2025', '2014-2030', $source), 
					$updated,
					$name . ': only the year should change in an existing header.');
			} else {
				$body = str_starts_with($source, '<?php') 
					? substr($source, 5) 
					: $source;

				assertLicenseHeaderSame(true, 
					str_ends_with($updated, $body),
					$name . ': insertion must preserve the original code, comments and markup.');

				assertLicenseHeaderSame(true, 
					str_starts_with($updated, '<?php' . $newLine . '/**'),
					$name . ': inserted license should follow the PHP opening tag.');
			}

			$withoutCrLf = str_replace("\r\n", '', $updated);
			assertLicenseHeaderSame(false, $newLine === "\r\n"
				? str_contains($withoutCrLf, "\n")
				: str_contains($updated, "\r"),
				$name . ': update should preserve the newline style.');

			assertLicenseHeaderSame(true, 
				$updater->update(2029), 
				$name . ': update must set the year regardless.');

			$updater->update(2030); 
			$updated = file_get_contents($pluginTestFile);

			assertLicenseHeaderSame(false, 
				$updater->update(2030), 
				$name . ': repeated update should be a no-op.');

			assertLicenseHeaderSame($updated, 
				file_get_contents($pluginTestFile), 
				$name . ': no-op must preserve every byte.');

			$lint = runLicenseHeaderPhp(array('-l', $pluginTestFile));
			assertLicenseHeaderSame(0, 
				$lint['exitCode'], 
				$name . ': updated PHP should lint. ' . $lint['error']);

			if (str_starts_with($name, 'view-file-')) {
				$originalView = $testDirectory . '/original-view.php';
				file_put_contents($originalView, $source);

				$before = runLicenseHeaderPhp(array($originalView));
				$after = runLicenseHeaderPhp(array($pluginTestFile));
				
				assertLicenseHeaderSame(0, 
					$after['exitCode'], 
					$name . ': updated view should execute.');

				assertLicenseHeaderSame($before['output'], 
					$after['output'], 
					$name . ': rendered markup must not change.');
			}
		}
	}
}

function runMainPluginFileTest(string $testDirectory): void {
	//The plugin entry point has a WordPress metadata comment before its license.
	$pluginSource = file_get_contents(__DIR__ . '/../../../../abp01-plugin-main.php');
	$pluginTestFile = $testDirectory . '/plugin.php';
	
	$source = preg_replace('/Copyright \(c\) 2014-\d{4}/', 
		'Copyright (c) 2014-2025', 
		$pluginSource, 
		1);

	file_put_contents($pluginTestFile, $source);
	
	$pluginTestFileReader = new LicenseHeaderReader($pluginTestFile);
	$pluginTestFileUpdater = new LicenseHeaderUpdater($pluginTestFile);

	assertLicenseHeaderSame(2025, 
		$pluginTestFileReader->read()?->year,
		'Reader should skip the WordPress plugin metadata comment.');

	$pluginTestFileUpdater->update(2030);
	assertLicenseHeaderSame(str_replace('2014-2025', '2014-2030', $source), 
		file_get_contents($pluginTestFile),
		'Updating the plugin entry point should preserve its metadata and all other contents.');
}

function runStaticAndTemplateFileTests(string $testDirectory): void {
	$staticAndTemplateFiles = array(
		'source.php.js' => "\"use strict\";\nvar message = 'sample';\n",
		'source.ts' => "/// <reference types=\"jquery\" />\nvar message: string = 'sample';\n",
		'source.css' => "/* Page styles */\n.sample {\n\tcolor: red;\n}\n",
		'view.phtml' => "<p><?= 'Sample HTML'; ?></p>"
	);

	foreach ($staticAndTemplateFiles as $name => $source) {
		$pluginTestFile = $testDirectory . '/' . $name;
		file_put_contents($pluginTestFile, $source);

		$reader = new LicenseHeaderReader($pluginTestFile);
		$updater = new LicenseHeaderUpdater($pluginTestFile);

		assertLicenseHeaderSame(true, 
			$updater->update(2025), 
			$name . ': insert a missing header.');

		$inserted = file_get_contents($pluginTestFile);
		
		assertLicenseHeaderSame(true, 
			str_ends_with($inserted, $source), 
			$name . ': preserve the original source.');

		assertLicenseHeaderSame(2025, 
			$reader->read()?->year, 
			$name . ': read the inserted header.');
		
		assertLicenseHeaderSame(true, 
			$updater->update(2030), 
			$name . ': update an existing header.');

		assertLicenseHeaderSame(str_replace('2014-2025', '2014-2030', $inserted), 
			file_get_contents($pluginTestFile),
			$name . ': only the year should change.');

		assertLicenseHeaderSame(false, 
			$updater->update(2030), 
			$name . ': repeated update should be a no-op.');
	}
}

withLicenseHeaderTestDirectory(function(string $testDirectory): void {
	runCorePhpFileTest($testDirectory);
	runMainPluginFileTest($testDirectory);
	runStaticAndTemplateFileTests($testDirectory);

	echo "License header reader, generator and updater tests passed.\n";
});
