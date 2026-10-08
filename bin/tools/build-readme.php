<?php
declare(strict_types=1);

/**
 * Builds README.md and README.txt from readme/manifest.json, readme/Makefile and readme/sections.
 * Run from anywhere: php bin/tools/build-readme.php --help
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/readme/ReadmeBuildException.php';
require_once __DIR__ . '/readme/ReadmeText.php';
require_once __DIR__ . '/readme/ReadmeJsonObject.php';
require_once __DIR__ . '/readme/ReadmeManifest.php';
require_once __DIR__ . '/readme/ReadmePluginHeader.php';
require_once __DIR__ . '/readme/ReadmeChangelog.php';
require_once __DIR__ . '/readme/ReadmeMakefile.php';
require_once __DIR__ . '/readme/ReadmeBuildContext.php';
require_once __DIR__ . '/readme/ReadmeTargetRenderer.php';
require_once __DIR__ . '/readme/ReadmeMarkdownRenderer.php';
require_once __DIR__ . '/readme/ReadmeWpOrgRenderer.php';

const WPTS_README_EXIT_OK = 0;
const WPTS_README_EXIT_OUTDATED = 1;
const WPTS_README_EXIT_ERROR = 2;

function wpts_readme_usage(): string {
	return <<<USAGE
Usage: php bin/tools/build-readme.php [options]

Builds the readme files described by readme/Makefile.

Options:
  --target=NAME       Build only the named target (for example Github or WpOrg); repeatable
  --check             Do not write anything; exit with 1 if an output file is out of date
  --output-dir=DIR    Write the outputs to DIR instead of their configured locations
  --manifest=PATH     Use another manifest (default: readme/manifest.json)
  --help              Print this help and exit

Exit codes: 0 success, 1 outdated output (--check), 2 invalid arguments or configuration.

USAGE;
}

/**
 * @return array{targets: string[], check: bool, outputDir: ?string, manifest: string, help: bool}
 */
function wpts_readme_parse_options(array $arguments, string $rootDirectory): array {
	$options = array(
		'targets' => array(),
		'check' => false,
		'outputDir' => null,
		'manifest' => $rootDirectory . '/readme/manifest.json',
		'help' => false
	);

	foreach ($arguments as $argument) {
		if ($argument === '--help' || $argument === '-h') {
			$options['help'] = true;
		} else if ($argument === '--check') {
			$options['check'] = true;
		} else if (preg_match('/^--(target|output-dir|manifest)=(.+)$/', $argument, $matches) === 1) {
			if ($matches[1] === 'target') {
				$options['targets'][] = $matches[2];
			} else if ($matches[1] === 'output-dir') {
				$options['outputDir'] = $matches[2];
			} else {
				$options['manifest'] = $matches[2];
			}
		} else {
			throw new ReadmeBuildException(
				sprintf('Unknown argument "%s". Use --help for usage.', 
					$argument)
			);
		}
	}

	if ($options['check'] && $options['outputDir'] !== null) {
		throw new ReadmeBuildException(
			'--check and --output-dir cannot be combined.'
		);
	}

	return $options;
}

/**
 * @return ReadmeMakefileTarget[]
 */
function wpts_readme_select_targets(ReadmeMakefile $makefile, array $targetNames): array {
	if (empty($targetNames)) {
		return array_values($makefile->targets);
	}

	return array_map(function(string $name) use ($makefile) {
		return $makefile->getTarget($name);
	}, $targetNames);
}

function wpts_readme_output_path(ReadmeTargetRenderer $renderer, ?string $outputDir): string {
	$outputPath = $renderer->getOutputPath();
	return $outputDir === null
		? $outputPath
		: rtrim($outputDir, '/\\') . '/' . basename($outputPath);
}

function wpts_readme_is_up_to_date(string $outputPath, string $contents): bool {
	return is_file($outputPath)
		&& ReadmeText::normalizeLineEndings((string)file_get_contents($outputPath)) 
			=== $contents;
}

function wpts_readme_write(string $outputPath, string $contents): void {
	$directory = dirname($outputPath);
	if (!wpts_readme_ensure_dir($directory)) {
		throw new ReadmeBuildException(
			sprintf('Cannot create directory "%s".', 
				$directory)
		);
	}

	if (file_put_contents($outputPath, $contents) === false) {
		throw new ReadmeBuildException(
			sprintf('Cannot write "%s".', 
				$outputPath)
		);
	}
}

function wpts_readme_ensure_dir(string $directory): bool {
	if (!is_dir($directory) 
		&& !mkdir($directory, 0777, true)) {
		return false;
	} else {
		return true;
	}
}

function wpts_readme_report(string $status, array $format, string $message): void {
	wpts_tools_format_print('[' . $status . ']', $format);
	echo ' ' . $message . PHP_EOL;
}

function wpts_readme_build(array $options, string $rootDirectory): int {
	$manifest = ReadmeManifest::fromFile($options['manifest'], 
		$rootDirectory);

	$makefile = ReadmeMakefile::fromFile($manifest->makefilePath);
	$context = new ReadmeBuildContext($manifest);

	$rendered = array();
	$targets = wpts_readme_select_targets($makefile, 
		$options['targets']);

	foreach ($targets as $target) {
		$renderer = ReadmeTargetRenderer::create($target, 
			$context);

		$rendered[$target->name] = array(
			'path' => wpts_readme_output_path($renderer, 
				$options['outputDir']),
			'contents' => $renderer->render()
		);
	}

	$exitCode = WPTS_README_EXIT_OK;
	foreach ($rendered as $targetName => $output) {
		$displayPath = $context->relativeToRoot($output['path']);

		if ($options['check']) {
			if (wpts_readme_is_up_to_date($output['path'], $output['contents'])) {
				wpts_readme_report(
					'OK', 
					array('green'), 
					sprintf('%s: %s is up to date.', 
						$targetName, 
						$displayPath)
				);
			} else {
				wpts_readme_report(
					'OUTDATED', 
					array('red'), 
					sprintf('%s: %s is out of date.', 
						$targetName, 
						$displayPath)
				);
				$exitCode = WPTS_README_EXIT_OUTDATED;
			}
		} else {
			wpts_readme_write($output['path'], 
				$output['contents']);
			wpts_readme_report('BUILT', 
				array('green'), 
				sprintf('%s: %s', 
					$targetName, 
					$displayPath)
			);
		}
	}

	foreach ($context->getWarnings() as $warning) {
		wpts_readme_report(
			'WARN', 
			array('yellow'), 
			$warning
		);
	}

	return $exitCode;
}

function wpts_readme_main(array $arguments): never {
	$rootDirectory = wpts_determine_root_directory();

	try {
		$options = wpts_readme_parse_options($arguments, 
			$rootDirectory);

		if ($options['help']) {
			echo wpts_readme_usage();
			exit(WPTS_README_EXIT_OK);
		}

		exit(wpts_readme_build($options, 
			$rootDirectory));
	} catch (ReadmeBuildException $exc) {
		wpts_readme_report('ERROR', 
			array('red'), 
			$exc->getMessage());

		exit(WPTS_README_EXIT_ERROR);
	}
}

function wpts_determine_root_directory(): string {
	return str_replace('\\', '/', dirname(__DIR__, 2));
}

wpts_readme_main(array_slice($argv, 1));
