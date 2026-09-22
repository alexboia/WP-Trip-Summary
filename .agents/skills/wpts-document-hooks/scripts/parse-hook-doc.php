<?php

declare(strict_types=1);

use WpTripSummary\Skills\WpDocumentHooks\HookDoc;
use WpTripSummary\Skills\WpDocumentHooks\HookDocParser;

require_once __DIR__ . '/HookDoc.php';
require_once __DIR__ . '/HookDocParser.php';

function wptsHookDocUsage(): void {
	fwrite(
		STDERR,
		"Usage: php parse-hook-doc.php [hooks-inventory.json] [--hook=<name>] [--output=<file>] [--pretty]\n"
	);
	exit(2);
}

function wptsHookDocNormalizePath(string $path): string {
	return str_replace('\\', '/', $path);
}

function wptsHookDocReadInventory(string $inventoryFile): array {
	if (!is_readable($inventoryFile)) {
		throw new RuntimeException("Hook inventory is not readable: {$inventoryFile}");
	}

	$contents = file_get_contents($inventoryFile);
	if ($contents === false) {
		throw new RuntimeException("Could not read hook inventory: {$inventoryFile}");
	}

	$inventory = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
	if (!is_array($inventory)) {
		throw new RuntimeException('Hook inventory root must be a JSON object.');
	}

	return $inventory;
}

/**
 * @return array<int, array<string, mixed>>
 */
function wptsHookDocOccurrences(array $inventory): array {
	$occurrences = array();
	foreach (array('documentedHooks', 'undocumentedHooks', 'unresolvedDispatchers') as $section) {
		if (!isset($inventory[$section])) {
			continue;
		}
		if (!is_array($inventory[$section])) {
			throw new RuntimeException("Inventory section {$section} must be an array.");
		}

		foreach ($inventory[$section] as $occurrence) {
			if (!is_array($occurrence)) {
				throw new RuntimeException("Every occurrence in {$section} must be a JSON object.");
			}
			$occurrence['inventorySection'] = $section;
			$occurrences[] = $occurrence;
		}
	}

	usort($occurrences, static function(array $left, array $right): int {
		return array(
			(string)($left['file'] ?? ''),
			(int)($left['line'] ?? 0),
			(string)($left['hookName'] ?? $left['nameExpression'] ?? '')
		) <=> array(
			(string)($right['file'] ?? ''),
			(int)($right['line'] ?? 0),
			(string)($right['hookName'] ?? $right['nameExpression'] ?? '')
		);
	});

	return $occurrences;
}

function wptsHookDocOccurrenceName(array $occurrence): string {
	$hookName = $occurrence['hookName'] ?? null;
	if (is_string($hookName) && $hookName !== '') {
		return $hookName;
	}

	$nameExpression = $occurrence['nameExpression'] ?? null;
	return is_string($nameExpression) && $nameExpression !== ''
		? $nameExpression
		: '<unresolved-hook>';
}

function wptsHookDocProvenance(array $occurrence): array {
	return array(
		'hookName' => $occurrence['hookName'] ?? null,
		'kind' => $occurrence['kind'] ?? null,
		'dispatcher' => $occurrence['dispatcher'] ?? null,
		'resolution' => $occurrence['resolution'] ?? null,
		'nameExpression' => $occurrence['nameExpression'] ?? null,
		'arguments' => is_array($occurrence['arguments'] ?? null)
			? array_values($occurrence['arguments'])
			: array(),
		'file' => $occurrence['file'] ?? null,
		'line' => $occurrence['line'] ?? null,
		'enclosingClass' => $occurrence['enclosingClass'] ?? null,
		'enclosingFunction' => $occurrence['enclosingFunction'] ?? null,
		'documentationStatus' => $occurrence['documentationStatus'] ?? null,
		'docCommentLine' => $occurrence['docCommentLine'] ?? null,
		'inventorySection' => $occurrence['inventorySection'] ?? null,
	);
}

/**
 * @return string[]
 */
function wptsHookDocWarnings(HookDoc $doc, array $occurrence): array {
	$warnings = array();
	if ($doc->description === '') {
		$warnings[] = 'missing-description';
	}
	if ($doc->category === null || $doc->category === '') {
		$warnings[] = 'missing-category';
	}
	if ($doc->since === null || $doc->since === '') {
		$warnings[] = 'missing-since';
	}

	$argumentCount = is_array($occurrence['arguments'] ?? null)
		? count($occurrence['arguments'])
		: 0;
	$parameterCount = count($doc->parameters);
	if ($argumentCount !== $parameterCount) {
		$warnings[] = sprintf(
			'parameter-count-mismatch: dispatcher=%d, documented=%d',
			$argumentCount,
			$parameterCount
		);
	}

	return $warnings;
}

function wptsHookDocToArray(HookDoc $doc): array {
	return array(
		'name' => $doc->name,
		'description' => $doc->description,
		'category' => $doc->category,
		'since' => $doc->since,
		'isUnstable' => $doc->isUnstable(),
		'unstable' => $doc->unstable,
		'see' => $doc->see,
		'parameters' => $doc->parameters,
	);
}

$inventoryFile = './hook-docs/hooks-inventory.json';
$inventoryFileProvided = false;
$hookFilter = null;
$outputFile = null;
$pretty = false;

foreach (array_slice($argv, 1) as $argument) {
	if ($argument === '--pretty') {
		$pretty = true;
	} elseif (str_starts_with($argument, '--hook=')) {
		$hookFilter = substr($argument, strlen('--hook='));
		if ($hookFilter === '') {
			wptsHookDocUsage();
		}
	} elseif (str_starts_with($argument, '--output=')) {
		$outputFile = substr($argument, strlen('--output='));
		if ($outputFile === '') {
			wptsHookDocUsage();
		}
	} elseif (str_starts_with($argument, '--')) {
		wptsHookDocUsage();
	} elseif (!$inventoryFileProvided) {
		$inventoryFile = $argument;
		$inventoryFileProvided = true;
	} else {
		wptsHookDocUsage();
	}
}

try {
	$inventory = wptsHookDocReadInventory($inventoryFile);
	$parsedHooks = array();
	$unparsedHooks = array();
	$matchedOccurrences = 0;
	$withDocComments = 0;

	foreach (wptsHookDocOccurrences($inventory) as $occurrence) {
		$occurrenceName = wptsHookDocOccurrenceName($occurrence);
		if ($hookFilter !== null && $occurrenceName !== $hookFilter) {
			continue;
		}

		$matchedOccurrences++;
		$docComment = $occurrence['docComment'] ?? null;
		if (!is_string($docComment) || trim($docComment) === '') {
			$unparsedHooks[] = wptsHookDocProvenance($occurrence) + array(
				'reason' => 'missing-doc-comment'
			);
			continue;
		}

		$withDocComments++;
		try {
			$doc = (new HookDocParser($occurrenceName, $docComment))->parse();
			if ($doc === null) {
				$unparsedHooks[] = wptsHookDocProvenance($occurrence) + array(
					'reason' => 'parser-returned-null'
				);
				continue;
			}

			$parsedHooks[] = wptsHookDocProvenance($occurrence) + array(
				'doc' => wptsHookDocToArray($doc),
				'warnings' => wptsHookDocWarnings($doc, $occurrence),
			);
		} catch (Throwable $error) {
			$unparsedHooks[] = wptsHookDocProvenance($occurrence) + array(
				'reason' => 'parser-error',
				'message' => $error->getMessage(),
			);
		}
	}

	if ($hookFilter !== null && $matchedOccurrences === 0) {
		throw new RuntimeException("Hook not found in inventory: {$hookFilter}");
	}

	$report = array(
		'schemaVersion' => 1,
		'sourceInventory' => wptsHookDocNormalizePath($inventoryFile),
		'hookFilter' => $hookFilter,
		'summary' => array(
			'matchedOccurrences' => $matchedOccurrences,
			'withDocComments' => $withDocComments,
			'parsedDocComments' => count($parsedHooks),
			'unparsedOccurrences' => count($unparsedHooks),
		),
		'hooks' => $parsedHooks,
		'unparsed' => $unparsedHooks,
	);

	$options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
	if ($pretty) {
		$options |= JSON_PRETTY_PRINT;
	}
	$json = json_encode($report, $options) . "\n";

	if ($outputFile !== null) {
		if (file_put_contents($outputFile, $json) === false) {
			throw new RuntimeException("Could not write parsed hook documentation to {$outputFile}");
		}
	} else {
		echo $json;
	}
} catch (Throwable $error) {
	fwrite(STDERR, $error->getMessage() . "\n");
	exit(1);
}
