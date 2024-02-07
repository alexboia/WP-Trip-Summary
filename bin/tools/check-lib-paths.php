<?php
class DirectoryRecord {
	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var bool[]
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

function _abp01_format_print(string $text = '', array $format = []) {
	//Courtesy of: https://stackoverflow.com/a/69580828/255656
	$codes=[
		'bold' => 1,
		'italic' => 3, 'underline' => 4, 'strikethrough' => 9,
		'black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33,'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37,
		'blackbg' => 40, 'redbg' => 41, 'greenbg' => 42, 'yellowbg' => 44,'bluebg' => 44, 'magentabg' => 45, 'cyanbg' => 46, 'lightgreybg' => 47
	];

	$formatMap = array_map(function ($v) use ($codes) { 
		return $codes[$v]; 
	}, $format);

	echo "\e[".implode(';',$formatMap).'m'.$text."\e[0m";
}

function abp01_scan_directory(string $directory, string $prefix = 'Abp01_'): DirectoryRecord {
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
		if (is_file($entryPath) && stripos($entry, '.php') !== false) {
			$expectedArtefactName = $prefix . str_ireplace('.php', '', $entry);
			$searchClassDefinition = sprintf('class %s', $expectedArtefactName);
			$searchInterfaceDefinition = sprintf('interface %s', $expectedArtefactName);
			$searchTraitDefinition = sprintf('interface %s', $expectedArtefactName);
			
			$entryContents = file_get_contents($entryPath);
			$record->files[$entryPath] = array(
				'isEmpty' => empty(trim($entryContents)),
				'expectedArtefactName' => $expectedArtefactName,
				'expectedArtefactExists' => 
					strpos($entryContents, $searchClassDefinition) !== false ||
					strpos($entryContents, $searchInterfaceDefinition) !== false ||
					strpos($entryContents, $searchTraitDefinition) !== false
			);
		} else if (is_dir($entryPath)) {
			$entryPrefix = sprintf('%s%s_', $prefix, ucfirst($entry));
			$record->directories[] = abp01_scan_directory($entryPath, $entryPrefix);
		}
	}
	
	return $record;
}

function abp01_analyze_directory(DirectoryRecord $record): bool {
	$ok = true;
	
	if (!$record->isCorrectName()) {
		_abp01_format_print(
			sprintf('Directory %s name is does not start with lowercase letter.', $record->path) . PHP_EOL, 
			array('red')
		);
		$ok = false;
	}

	foreach ($record->files as $fileName => $fileInfo) {
		if ($fileInfo['isEmpty']) {
			_abp01_format_print(
				sprintf('File %s is empty. This will not cause build to fail.', $fileName) . PHP_EOL, 
				array('yellow')
			);
			continue;
		}

		if (!$fileInfo['expectedArtefactExists']) {
			_abp01_format_print(
				sprintf('File %s does not contain expected class/trait/interface %s.', $fileName, $fileInfo['expectedArtefactName']) . PHP_EOL, 
				array('yellow')
			);
			$ok = false;
		}
	}

	foreach ($record->directories as $subRecord) {
		if (!abp01_analyze_directory($subRecord)) {
			$ok = false;
		}
	}

	return $ok;
}

function abp01_run_lib_check($directory) {
	echo sprintf('Scanning directory: %s...' . PHP_EOL, $directory);
	$record = abp01_scan_directory($directory);

	echo sprintf('Analyzing directory contents...' . PHP_EOL);
	if (abp01_analyze_directory($record)) {
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

abp01_run_lib_check($directory);