<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

class DirectoryRecord {
	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var array[]
	 */
	public $files = array();

	/**
	 * @var DirectoryRecord[]
	 */
	public $directories = array();

	/**
	 * @var string
	 */
	public $name = null;

	public function isCorrectName(): bool {
		return lcfirst($this->name) === $this->name;
	}
}

function wpts_is_php_file(string $entryPath): bool {
	$entry = basename($entryPath);
	return is_file($entryPath) 
		&& stripos($entry, '.php') !== false;
}

function wpts_entry_has_expected_artefact(string $entryContents, string $expectedArtefactName): bool {
	$searchClassDefinition = sprintf('class %s', $expectedArtefactName);
	$searchInterfaceDefinition = sprintf('interface %s', $expectedArtefactName);
	$searchTraitDefinition = sprintf('interface %s', $expectedArtefactName);

	return strpos($entryContents, $searchClassDefinition) !== false ||
		strpos($entryContents, $searchInterfaceDefinition) !== false ||
		strpos($entryContents, $searchTraitDefinition) !== false;
}

function wpts_scan_directory(
	string $directory, 
	array $directoryNames,
	string $psr4NamespacePrefix = 'WpTripSummary', 
	string $psr0Prefix = 'Abp01'
): DirectoryRecord {

	if (empty($directory)) {
		throw new InvalidArgumentException('Directory may not be empty.');
	}

	if (!is_dir($directory)) {
		throw new InvalidArgumentException(sprintf('Directory %s does not exist or is not accessible.', $directory));
	}

	$record = new DirectoryRecord();
	$record->name = basename($directory);
	$record->path = $directory;

	$contents = array_filter(
		scandir($directory), 
		function($entry) {
			return $entry !== '.' 
				&& $entry !== '..' 
				&& $entry !== 'index.php'
				&& $entry !== '3rdParty'
				&& !empty($entry);
		}
	);

	foreach ($contents as $entry) {
		$entryPath = $directory . DIRECTORY_SEPARATOR . $entry;
		if (wpts_is_php_file($entryPath)) {
			$entryContents = file_get_contents($entryPath);

			$artefactNameBase = str_ireplace('.php', '', $entry);
			
			$namespaceParts = array_map(
				fn($parent) => ucfirst($parent), 
				$directoryNames
			);

			$searchNamespaceMarker = !empty($namespaceParts) 
				? sprintf('namespace %s\\%s', 
					$psr4NamespacePrefix,
					join('\\', $namespaceParts))
				: sprintf('namespace %s', 
					$psr4NamespacePrefix);

			if (stripos($entryContents, $searchNamespaceMarker) !== false) {
				$expectedArtefactName = $artefactNameBase;
			} else {
				$psr0Namespace = !empty($namespaceParts) 
					? sprintf('%s_%s', 
						$psr0Prefix, 
						join('_', $namespaceParts))
					: $psr0Prefix;

				$expectedArtefactName = sprintf('%s_%s', 
					$psr0Namespace, 
					$artefactNameBase);
			}

			$record->files[$entryPath] = array(
				'isEmpty' => empty(trim($entryContents)),
				'expectedArtefactName' => $expectedArtefactName,
				'expectedArtefactExists' => wpts_entry_has_expected_artefact($entryContents, 
					$expectedArtefactName)
			);
		} else if (is_dir($entryPath)) {
			$entryDirectoryNames = $directoryNames;
			$entryDirectoryNames[] = $entry;

			$record->directories[] = wpts_scan_directory($entryPath, 
				$entryDirectoryNames, 
				$psr4NamespacePrefix, 
				$psr0Prefix);
		}
	}
	
	return $record;
}

function wpts_analyze_directory(DirectoryRecord $record): bool {
	$ok = true;
	
	if (!$record->isCorrectName()) {
		wpts_tools_format_print(
			sprintf('Directory %s name is does not start with lowercase letter.', $record->path) . PHP_EOL, 
			array('red')
		);
		$ok = false;
	}

	foreach ($record->files as $fileName => $fileInfo) {
		if ($fileInfo['isEmpty']) {
			wpts_tools_format_print(
				sprintf('File %s is empty. This will not cause build to fail.', $fileName) . PHP_EOL, 
				array('yellow')
			);
			continue;
		}

		if (!$fileInfo['expectedArtefactExists']) {
			wpts_tools_format_print(
				sprintf('File %s does not contain expected class/trait/interface %s.', $fileName, $fileInfo['expectedArtefactName']) . PHP_EOL, 
				array('yellow')
			);
			$ok = false;
		}
	}

	foreach ($record->directories as $subRecord) {
		if (!wpts_analyze_directory($subRecord)) {
			$ok = false;
		}
	}

	return $ok;
}

function wpts_run_lib_check(string $directory): never {
	echo sprintf('Scanning directory: %s...' . PHP_EOL, $directory);
	$record = wpts_scan_directory($directory, array());

	echo sprintf('Analyzing directory contents...' . PHP_EOL);
	if (wpts_analyze_directory($record)) {
		exit(0);
	} else {
		exit(1000);
	}
}

if (!isset($argv) || count($argv) != 2) {
	$directory = '';
} else {
	$directory = $argv[1];
}

if (empty($directory)) {
	$directory = realpath(__DIR__ . '/../../lib');
}

wpts_run_lib_check($directory);