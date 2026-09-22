<?php

declare(strict_types=1);

use WpTripSummary\Skills\WpDocumentHooks\HookDocParser;

require_once __DIR__ . '/../scripts/HookDoc.php';
require_once __DIR__ . '/../scripts/HookDocParser.php';

function assertHookDocSame(mixed $expected, mixed $actual, string $message): void {
	if ($expected !== $actual) {
		throw new RuntimeException(sprintf(
			"%s\nExpected: %s\nActual: %s",
			$message,
			var_export($expected, true),
			var_export($actual, true)
		));
	}
}

function assertHookDocTrue(bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

function runHookDocCli(
	string $script,
	string $inventoryFile,
	array $arguments = array()
): string {
	$command = escapeshellarg(PHP_BINARY)
		. ' -n ' . escapeshellarg($script)
		. ' ' . escapeshellarg($inventoryFile);

	foreach ($arguments as $argument) {
		$command .= ' ' . escapeshellarg($argument);
	}

	$output = array();
	$exitCode = 0;

	exec($command, 
		$output, 
		$exitCode);
	
	assertHookDocSame(0, 
		$exitCode, 
		'Hook doc CLI should exit successfully.');

	return implode("\n", $output) . "\n";
}

$comment = <<<'DOC'
/**
 * Filters a sample value.
 *
 * The description may span multiple lines.
 *
 * @since 0.3.3
 * @category Front-end Viewer
 * @unstable Susceptible to breaking changes
 * due to a transitional class contract.
 * @see abp01_sample_dependency First reference.
 * Continued reference details.
 * @see abp01_second_dependency Second reference.
 *
 * @param array<string, string> $mapping The initial mapping.
 * Additional mapping details.
 * @param $context Context without an explicit type.
 * @param bool Enabled flag without an explicit name.
 * @param string &$referencedValue A referenced value.
 * @param int ...$variadicValues Variadic values.
 * @param FirstType&SecondType &...$referencedVariadicValues Referenced variadic values.
 * @return void
 * This unsupported tag continuation must be ignored.
 */
DOC;

$parser = new HookDocParser('abp01_sample', 
	str_replace("\n", 
		"\r\n", 
		$comment));

$doc = $parser->parse();
if ($doc === null) {
	throw new RuntimeException('Parser unexpectedly returned null.');
}

assertHookDocSame(
	'Filters a sample value. The description may span multiple lines.',
	$doc->description,
	'Parser should join description lines.'
);

assertHookDocSame('Front-end Viewer', 
	$doc->category, 
	'Parser should extract the category.');

assertHookDocSame('0.3.3', 
	$doc->since, 
	'Parser should extract the version.');

assertHookDocSame(array(
		'Susceptible to breaking changes due to a transitional class contract.'
	), 
	$doc->unstable, 
	'Parser should collect multiline unstable tags.');

assertHookDocSame(array(
		'abp01_sample_dependency First reference. Continued reference details.',
		'abp01_second_dependency Second reference.'
	), 
	$doc->see, 
	'Parser should collect every see tag and its continuation.');

assertHookDocSame(array(
		array(
			'name' => '$mapping',
			'type' => 'array<string, string>',
			'byReference' => false,
			'variadic' => false,
			'description' => 'The initial mapping. Additional mapping details.'
		),
		array(
			'name' => '$context',
			'type' => 'mixed',
			'byReference' => false,
			'variadic' => false,
			'description' => 'Context without an explicit type.'
		),
		array(
			'name' => '$__2',
			'type' => 'bool',
			'byReference' => false,
			'variadic' => false,
			'description' => 'Enabled flag without an explicit name.'
		),
		array(
			'name' => '$referencedValue',
			'type' => 'string',
			'byReference' => true,
			'variadic' => false,
			'description' => 'A referenced value.'
		),
		array(
			'name' => '$variadicValues',
			'type' => 'int',
			'byReference' => false,
			'variadic' => true,
			'description' => 'Variadic values.'
		),
		array(
			'name' => '$referencedVariadicValues',
			'type' => 'FirstType&SecondType',
			'byReference' => true,
			'variadic' => true,
			'description' => 'Referenced variadic values.'
		),
	), 
	$doc->parameters, 
	'Parser should extract parameter types, names, and descriptions.');

assertHookDocTrue($doc->isUnstable(), 
	'Parsed hook should be marked unstable.');
assertHookDocTrue($doc === $parser->parse(), 
	'Parser should cache and reuse its result.');

$singleLineDoc = (new HookDocParser(
	'abp01_single_line',
	'/** Fires a single-line action. */'
))->parse();

if ($singleLineDoc === null) {
	throw new RuntimeException('Single-line parser unexpectedly returned null.');
}

assertHookDocSame(
	'Fires a single-line action.',
	$singleLineDoc->description,
	'Parser should support a single-line doc-comment.'
);

assertHookDocSame(null, 
	(new HookDocParser('', $comment))->parse(), 
	'Empty hook names should not parse.');
assertHookDocSame(null, 
	(new HookDocParser('abp01_empty', ''))->parse(), 
	'Empty comments should not parse.');

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR 
	. 'wpts-hook-doc-parser-' . bin2hex(random_bytes(8));

$script = realpath(__DIR__ . '/../scripts/parse-hook-doc.php');

if ($script === false) {
	throw new RuntimeException('Could not resolve hook doc CLI path.');
}

try {
	if (!mkdir($testRoot, 0777, true) 
		&& !is_dir($testRoot)) {
		throw new RuntimeException('Could not create hook doc test directory.');
	}

	$inventoryFile = $testRoot . DIRECTORY_SEPARATOR . 'hooks-inventory.json';
	$inventory = array(
		'schemaVersion' => 1,
		'documentedHooks' => array(
			array(
				'hookName' => 'abp01_sample',
				'kind' => 'filter',
				'dispatcher' => 'apply_filters',
				'arguments' => array(
					'$mapping',
					'$context',
					'$enabled',
					'$referencedValue',
					'$variadicValues',
					'$referencedVariadicValues',
				),
				'file' => 'lib/Sample.php',
				'line' => 42,
				'enclosingClass' => 'Sample',
				'enclosingFunction' => 'filter',
				'docCommentLine' => 21,
				'docComment' => $comment,
			)
		),
		'undocumentedHooks' => array(
			array(
				'hookName' => 'abp01_missing',
				'kind' => 'action',
				'dispatcher' => 'do_action',
				'arguments' => array(),
				'file' => 'lib/Missing.php',
				'line' => 10,
				'docCommentLine' => null,
				'docComment' => null,
			)
		),
		'unresolvedDispatchers' => array(),
	);
	file_put_contents(
		$inventoryFile,
		json_encode($inventory, 
		JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
	);

	$firstOutput = runHookDocCli($script, 
		$inventoryFile, 
		array('--pretty'));

	$secondOutput = runHookDocCli($script, 
		$inventoryFile, 
		array('--pretty'));

	assertHookDocSame($firstOutput, 
		$secondOutput, 
		'Hook doc CLI output must be deterministic.');

	$report = json_decode($firstOutput, 
		true, 
		512, 
		JSON_THROW_ON_ERROR);
	
	assertHookDocSame(array(
			'matchedOccurrences' => 2,
			'withDocComments' => 1,
			'parsedDocComments' => 1,
			'unparsedOccurrences' => 1,
		), 
		$report['summary'], 
		'CLI summary should classify all inventory occurrences.');

	assertHookDocSame('abp01_sample', 
		$report['hooks'][0]['hookName'], 
		'CLI should preserve hook provenance.');

	assertHookDocSame(array(), 
		$report['hooks'][0]['warnings'], 
		'Matching parameter counts should not warn.');

	assertHookDocSame(
		'array<string, string>',
		$report['hooks'][0]['doc']['parameters'][0]['type'],
		'CLI should expose parsed generic parameter types.'
	);
	assertHookDocSame(
		true,
		$report['hooks'][0]['doc']['parameters'][3]['byReference'],
		'CLI should preserve pass-by-reference semantics.'
	);
	assertHookDocSame(
		true,
		$report['hooks'][0]['doc']['parameters'][5]['variadic'],
		'CLI should preserve variadic semantics.'
	);
	assertHookDocSame(
		'missing-doc-comment',
		$report['unparsed'][0]['reason'],
		'CLI should retain undocumented occurrence provenance.'
	);

	$filteredOutput = runHookDocCli(
		$script,
		$inventoryFile,
		array('--hook=abp01_sample', '--pretty')
	);

	$filteredReport = json_decode($filteredOutput, 
		true, 
		512, 
		JSON_THROW_ON_ERROR);

	assertHookDocSame(1, 
		$filteredReport['summary']['matchedOccurrences'], 
		'Hook filter should select one occurrence.');

	assertHookDocSame('abp01_sample', 
		$filteredReport['hookFilter'], 
		'CLI should report the active hook filter.');

	$outputFile = $testRoot . DIRECTORY_SEPARATOR . 'parsed.json';
	runHookDocCli($script, 
		$inventoryFile, 
		array('--pretty', '--output=' . $outputFile));
	
	$writtenOutput = file_get_contents($outputFile);
	assertHookDocSame($firstOutput, 
		$writtenOutput, 
		'Output file should contain the same deterministic report.');

	echo "Hook doc parser tests passed.\n";
} finally {
	if (isset($outputFile) && is_file($outputFile)) {
		unlink($outputFile);
	}
	if (isset($inventoryFile) && is_file($inventoryFile)) {
		unlink($inventoryFile);
	}
	if (is_dir($testRoot)) {
		rmdir($testRoot);
	}
}
