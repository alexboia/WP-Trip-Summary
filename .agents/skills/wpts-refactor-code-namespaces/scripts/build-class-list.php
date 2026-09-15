<?php
declare(strict_types=1);

if ($argc < 2) {
	fwrite(STDERR, "Usage: php build-class-list.php <plugin-root> [output-file]\n");
	exit(1);
}

function getValidPluginRootOrExit(array $argv): string {
	$pluginRoot = realpath($argv[1]);
	if ($pluginRoot === false || !is_dir($pluginRoot . DIRECTORY_SEPARATOR . 'lib')) {
		fwrite(STDERR, "The supplied path is not a WPTS plugin root.\n");
		exit(1);
	}
	return $pluginRoot;
}

function getOutputFile(array $argv): string {
	return $argv[2] ?? dirname(__DIR__) 
		. DIRECTORY_SEPARATOR 
		. 'class-list.json';
}

function readExistingJson(string $outputFile): array {
	$existing = is_file($outputFile)
		? json_decode((string) file_get_contents($outputFile), true)
		: array();
	
	$existing = is_array($existing) 
		? $existing 
		: array();

	return $existing;
}

function getInjectableFilesSpecString(string $libRoot): string {
	$injectablesFile = $libRoot . DIRECTORY_SEPARATOR 
		. 'pluginModules' 
		. DIRECTORY_SEPARATOR 
		. 'PluginModuleHost.php';

	$injectables = is_file($injectablesFile) 
		? (string) file_get_contents($injectablesFile) 
		: '';	

	return $injectables;
}

function computeRelativePath(string $absolutePath, string $pluginRoot): string {
	return str_replace('\\', 
		'/', 
		substr($absolutePath, 
			strlen($pluginRoot) + 1)
	);
}

function shouldIgnorePath(string $relativePath): bool {
	$ignorePaths = array(
		'lib/3rdParty/'
	);

	foreach ($ignorePaths as $ignorePath) {
		if (str_starts_with($relativePath, $ignorePath)) {
			return true;
		}
	}

	return false;
}

function findReplaceableSymbols(string $pluginRoot): array {
	$symbols = array();

	$libRoot = $pluginRoot . DIRECTORY_SEPARATOR . 'lib';
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$libRoot, 
			FilesystemIterator::SKIP_DOTS
		)
	);

	$injectables = getInjectableFilesSpecString($libRoot);

	foreach ($iterator as $file) {
		if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
			continue;
		}

		$absolutePath = $file->getPathname();
		$relativePath = computeRelativePath($absolutePath, $pluginRoot);

		if (shouldIgnorePath($relativePath)) {
			continue;
		}

		$contents = (string) file_get_contents($absolutePath);
		if (!preg_match('/^(?:(abstract|final)\s+)?(class|interface|trait)\s+(Abp01_[A-Za-z0-9_]+)/m', $contents, $match)) {
			continue;
		}

		$modifier = $match[1] ?? '';
		$kind = $match[2];
		$legacyName = $match[3];
		$nameParts = explode('_', substr($legacyName, strlen('Abp01_')));
		$newClass = 'WpTripSummary\\' . implode('\\', $nameParts);
		$issues = array();

		if ($kind === 'interface') {
			$issues[] = 'interface: migrate before implementations';
		} elseif ($modifier === 'abstract') {
			$issues[] = 'abstract base type: migrate before subclasses';
		}
		if (str_contains($injectables, $legacyName . '::class')) {
			$issues[] = 'dependency-injection participant';
		}

		$previous = $existing[$legacyName] ?? array();
		$symbols[$legacyName] = array(
			'newClass' => $newClass,
			'filePath' => $relativePath,
			'status' => $previous['status'] ?? 'pending',
			'potentialIssues' => array_values(array_unique(array_merge(
				$previous['potentialIssues'] ?? array(),
				$issues
			))),
			'_priority' => $kind === 'interface' ? 0 : ($modifier === 'abstract' ? 1 : 2),
		);
	}

	return $symbols;
}

$pluginRoot = getValidPluginRootOrExit($argv);
$outputFile = getOutputFile($argv);

$existing = readExistingJson($outputFile);
$symbols = findReplaceableSymbols($pluginRoot);

foreach ($existing as $legacyName => $previous) {
	if (!isset($symbols[$legacyName]) && ($previous['status'] ?? null) === 'complete') {
		$previous['_priority'] = 3;
		$symbols[$legacyName] = $previous;
	}
}

uasort($symbols, static function(array $left, array $right): int {
	return array($left['_priority'], substr_count($left['filePath'], '/'), $left['filePath'])
		<=> array($right['_priority'], substr_count($right['filePath'], '/'), $right['filePath']);
});

foreach ($symbols as &$symbol) {
	unset($symbol['_priority']);
}
unset($symbol);

$json = json_encode($symbols, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false || file_put_contents($outputFile, $json . PHP_EOL) === false) {
	fwrite(STDERR, "Could not write class inventory.\n");
	exit(1);
}

printf("Wrote %d candidates to %s\n", count($symbols), $outputFile);
