<?php

declare(strict_types=1);

function assertSameValue(mixed $expected, mixed $actual, string $message): void {
	if ($expected !== $actual) {
		throw new RuntimeException(sprintf(
			"%s\nExpected: %s\nActual: %s",
			$message,
			var_export($expected, true),
			var_export($actual, true)
		));
	}
}

function removeTestDirectory(string $directory): void {
	if (!is_dir($directory)) {
		return;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($iterator as $item) {
		if ($item->isDir()) {
			rmdir($item->getPathname());
		} else {
			unlink($item->getPathname());
		}
	}
	rmdir($directory);
}

function runExtractor(string $extractor, string $root, ?string $outputFile = null): string {
	$command = escapeshellarg(PHP_BINARY)
		. ' ' . escapeshellarg($extractor)
		. ' ' . escapeshellarg($root)
		. ' --pretty';
	if ($outputFile !== null) {
		$command .= ' ' . escapeshellarg('--output=' . $outputFile);
	}

	$output = array();
	$exitCode = 0;
	exec($command, $output, $exitCode);
	assertSameValue(0, $exitCode, 'Extractor should exit successfully.');
	if ($outputFile !== null) {
		$content = file_get_contents($outputFile);
		if ($content === false) {
			throw new RuntimeException('Extractor did not create the requested output file.');
		}
		return $content;
	}
	return implode("\n", $output) . "\n";
}

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wpts-hook-extractor-' . bin2hex(random_bytes(8));
$extractor = realpath(__DIR__ . '/../scripts/extract-hooks.php');
if ($extractor === false) {
	throw new RuntimeException('Could not resolve extractor path.');
}

try {
	mkdir($testRoot . '/lib/3rdParty', 0777, true);
	mkdir($testRoot . '/views', 0777, true);

	file_put_contents($testRoot . '/fixture.php', <<<'PHP'
<?php
function documentedHook($value) {
	/**
	 * Filters a documented value.
	 *
	 * @param string $value The value.
	 */
	return apply_filters('abp01_documented', $value, nestedCall(1, 2));
}

function ignoredHook() {
	do_action('another_prefix_ignored');
}
PHP);

	file_put_contents($testRoot . '/lib/Sample.php', <<<'PHP'
<?php
class Sample {
	const ACTION_HOOK = 'abp01_from_constant';

	public function run($context) {
		do_action(self::ACTION_HOOK, $context);

		$hook = 'abp01_dynamic_at_runtime';
		do_action($hook, $context);
	}
}
PHP);

	file_put_contents($testRoot . '/lib/3rdParty/Ignored.php', <<<'PHP'
<?php
do_action('abp01_ignored_third_party');
PHP);

	file_put_contents($testRoot . '/views/template.php', <<<'PHP'
<?php do_action('abp01_template', $data); ?>
PHP);

	$firstOutput = runExtractor($extractor, $testRoot);
	$secondOutput = runExtractor($extractor, $testRoot);
	assertSameValue($firstOutput, $secondOutput, 'Extractor output must be deterministic.');

	$report = json_decode($firstOutput, true, 512, JSON_THROW_ON_ERROR);
	assertSameValue(array(
		'totalDispatches' => 4,
		'documented' => 1,
		'undocumented' => 2,
		'unresolvedDynamic' => 1,
	), $report['summary'], 'Summary should classify every relevant dispatch.');

	assertSameValue('abp01_documented', $report['documentedHooks'][0]['hookName'], 'Literal hook name should be resolved.');
	assertSameValue('documentedHook', $report['documentedHooks'][0]['enclosingFunction'], 'Function context should be captured.');
	assertSameValue(array('$value', 'nestedCall(1, 2)'), $report['documentedHooks'][0]['arguments'], 'Nested call arguments should remain intact.');

	assertSameValue('abp01_from_constant', $report['undocumentedHooks'][0]['hookName'], 'Class constant hook name should be resolved.');
	assertSameValue('Sample', $report['undocumentedHooks'][0]['enclosingClass'], 'Class context should be captured.');
	assertSameValue('run', $report['undocumentedHooks'][0]['enclosingFunction'], 'Method context should be captured.');
	assertSameValue('abp01_template', $report['undocumentedHooks'][1]['hookName'], 'Template hook should be found.');

	assertSameValue('$hook', $report['unresolvedDispatchers'][0]['nameExpression'], 'Dynamic expression should be preserved.');
	assertSameValue('missing', $report['unresolvedDispatchers'][0]['documentationStatus'], 'Dynamic dispatcher documentation status should be reported.');

	$outputFile = $testRoot . '/report.json';
	$fileOutput = runExtractor($extractor, $testRoot, $outputFile);
	assertSameValue($firstOutput, $fileOutput, 'Explicit output file should contain the same deterministic report.');

	echo "Hook extractor tests passed.\n";
} finally {
	removeTestDirectory($testRoot);
}
