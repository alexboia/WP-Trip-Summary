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

function wpts_copyTestFixture(string $fixtureName, string $temporaryDir): string {
	$fixture = __DIR__ . '/data/change-target-class-identifier/' . $fixtureName;
	$destination = $temporaryDir . '/' . $fixtureName;
	if (!is_file($fixture) || !copy($fixture, $destination)) {
		throw new RuntimeException('Could not copy test fixture: ' . $fixtureName);
	}
	return $destination;
}

function assertGeneratedImports(array $expected, string $converted, string $caseName): void {
	// Generated imports have one namespace indentation level; trait use
	// statements remain nested inside the converted declaration.
	preg_match_all('/^\tuse \\\\([^;\r\n]+);\r?$/m', 
		$converted, 
		$matches);
	
	$actual = $matches[1];
	
	sort($expected);
	sort($actual);

	if ($actual !== $expected) {
		throw new RuntimeException($caseName . ': incorrect generated imports.'
			. "\nExpected: " . json_encode($expected)
			. "\nActual: " . json_encode($actual));
	}
}

function getTraitDependencyCases(): array {
	return array(
		'SingleTrait' => array(
			'imports' => array('Abp01_LoggingTrait')
		),
		'MultipleTraitsAndTypes' => array(
			'imports' => array('Abp01_FirstTrait', 'Abp01_SecondTrait',
				'Abp01_ThirdTrait', 'Abp01_Input', 'Abp01_Output')
		),
		'TraitAdaptations' => array(
			'imports' => array('Abp01_FirstTrait', 'Abp01_SecondTrait', 'Abp01_ThirdTrait')
		),
		'NestedTrait' => array(
			'imports' => array('Abp01_NestedTrait')
		),
		'EnumTrait' => array(
			'imports' => array('Abp01_EnumTrait')
		),
		'AnonymousClassTrait' => array(
			'imports' => array('Abp01_AnonymousTrait')
		),
		'FullyQualifiedTrait' => array(
			'imports' => array('External\\Mixins\\Logging')
		),
		'ClosureCaptures' => array(
			'imports' => array('Abp01_Input', 'Abp01_Output', 'Abp01_LoggingTrait')
		),
		'TraitAfterInterpolatedString' => array(
			'imports' => array('Abp01_LoggingTrait')
		),
		'ExistingTraitImport' => array(
			'imports' => array()
		)
	);
}

try {
	$file = wpts_copyTestFixture('Example.php', $temporaryDir);

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
	$existingUseFile = wpts_copyTestFixture('ExistingUse.php', $temporaryDir);
	
	[, $existingUseExitCode] = runConverter($script, 
		$existingUseFile, 
		'Abp01_ExistingUse');

	$existingUseResult = (string) file_get_contents($existingUseFile);
	if ($existingUseExitCode !== 0 
		|| str_contains($existingUseResult, 'use \\Abp01_Other;')) {
		throw new RuntimeException('Existing top-level imports were not respected.');
	}

	//4. Trait dependencies must be imported without capturing method names,
	// adaptation aliases, closure variables or other non-class identifiers.
	$traitCases = getTraitDependencyCases();
	foreach ($traitCases as $caseName => $case) {
		$traitFile = wpts_copyTestFixture($caseName . '.php', $temporaryDir);
		
		$before = hash_file('sha256', $traitFile);
		[$traitPreview, $traitPreviewExitCode] = runConverter($script,
			$traitFile, 
			'Abp01_TraitConsumer', 
			true);

		if ($traitPreviewExitCode !== 0 
			|| hash_file('sha256', $traitFile) !== $before) {
			throw new RuntimeException($caseName . ': dry-run failed or changed the file.');
		}

		assertGeneratedImports($case['imports'], 
			$traitPreview, 
			$caseName);

		[, $traitExitCode] = runConverter($script, 
			$traitFile, 
			'Abp01_TraitConsumer');
		
		if ($traitExitCode !== 0) {
			throw new RuntimeException($caseName . ': conversion failed.');
		}
		
		$traitResult = (string) file_get_contents($traitFile);

		assertGeneratedImports($case['imports'], 
			$traitResult, 
			$caseName);
		
		$lintOutput = array();
		$lintExitCode = 0;

		exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($traitFile), 
			$lintOutput, 
			$lintExitCode);

		if ($lintExitCode !== 0) {
			throw new RuntimeException($caseName . ': generated PHP does not parse.'
				. "\n" . implode("\n", $lintOutput));
		}
	}

	//5. An unqualified trait reference must resolve after namespace conversion.
	$runtimeFile = wpts_copyTestFixture('TraitRuntime.php', $temporaryDir);
	$runtimeOutput = array();
	$runtimeExitCode = 0;

	exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($runtimeFile), 
		$runtimeOutput, 
		$runtimeExitCode);

	if ($runtimeExitCode !== 0 
		|| implode('', $runtimeOutput) !== 'trait-ok') {
		throw new RuntimeException('Converted class could not use its imported trait at runtime.');
	}

	echo "All change-target-class-identifier tests passed.\n";
} finally {
	removeTestDirectory($temporaryDir);
}
