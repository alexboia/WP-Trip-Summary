<?php

declare(strict_types=1);

const WPTS_HOOK_DISPATCHERS = array(
	'apply_filters' => 'filter',
	'apply_filters_ref_array' => 'filter',
	'apply_filters_deprecated' => 'filter',
	'do_action' => 'action',
	'do_action_ref_array' => 'action',
	'do_action_deprecated' => 'action'
);

const WPTS_HOOK_CLASS_WRAPPED_DISPATCHERS = array(
	'Abp01_Installer_Service_RunInstallHook' => 'action'
);

function wptsHooksUsage(): void {
	fwrite(STDERR, "Usage: php extract-hooks.php <plugin-root> [--prefix=abp01_] [--output=<file>] [--pretty]\n");
	exit(2);
}

function wptsHooksNormalizePath(string $path): string {
	return str_replace('\\', '/', $path);
}

function wptsHooksRelativePath(string $root, string $path): string {
	$root = rtrim(wptsHooksNormalizePath($root), '/');
	$path = wptsHooksNormalizePath($path);
	return ltrim(substr($path, strlen($root)), '/');
}

/**
 * @return string[]
 */
function wptsHooksProductionFiles(string $root): array {
	$files = array();
	foreach (glob($root . DIRECTORY_SEPARATOR . '*.php') ?: array() as $file) {
		$files[] = realpath($file) ?: $file;
	}

	foreach (array('lib', 'views') as $relativeRoot) {
		$scanRoot = $root . DIRECTORY_SEPARATOR . $relativeRoot;
		if (!is_dir($scanRoot)) {
			continue;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$scanRoot, 
				FilesystemIterator::SKIP_DOTS
			)
		);
		foreach ($iterator as $fileInfo) {
			if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
				continue;
			}

			$path = $fileInfo->getPathname();
			$relativePath = wptsHooksRelativePath($root, $path);
			if (str_starts_with($relativePath, 'lib/3rdParty/')) {
				continue;
			}

			$files[] = realpath($path) ?: $path;
		}
	}

	$files = array_values(array_unique($files));
	usort($files, static function(string $left, string $right) use ($root): int {
		return strcmp(wptsHooksRelativePath($root, $left), 
			wptsHooksRelativePath($root, $right));
	});
	return $files;
}

/**
 * @return array<int, array{id: int|null, text: string, line: int}>
 */
function wptsHooksTokens(string $source): array {
	$tokens = array();
	$currentLine = 1;
	foreach (token_get_all($source) as $token) {
		if (is_array($token)) {
			$tokens[] = array(
				'id' => $token[0], 
				'text' => $token[1], 
				'line' => $token[2]
			);
			$currentLine = $token[2] + substr_count($token[1], "\n");
		} else {
			$tokens[] = array(
				'id' => null, 
				'text' => $token, 
				'line' => $currentLine
			);
			$currentLine += substr_count($token, "\n");
		}
	}
	return $tokens;
}

function wptsHooksIsTrivia(array $token): bool {
	return in_array($token['id'], array(
		T_WHITESPACE, 
		T_COMMENT, 
		T_DOC_COMMENT
	), true);
}

function wptsHooksPreviousMeaningfulIndex(array $tokens, int $index): ?int {
	for ($i = $index - 1; $i >= 0; $i--) {
		if (!wptsHooksIsTrivia($tokens[$i])) {
			return $i;
		}
	}
	return null;
}

function wptsHooksNextMeaningfulIndex(array $tokens, int $index): ?int {
	$count = count($tokens);
	for ($i = $index + 1; $i < $count; $i++) {
		if (!wptsHooksIsTrivia($tokens[$i])) {
			return $i;
		}
	}
	return null;
}

function wptsHooksDecodeStringLiteral(string $literal): ?string {
	if (strlen($literal) < 2) {
		return null;
	}

	$quote = $literal[0];
	if (($quote !== "'" && $quote !== '"') || $literal[strlen($literal) - 1] !== $quote) {
		return null;
	}

	$value = substr($literal, 1, -1);
	if ($quote === "'") {
		return str_replace(array('\\\\', "\\'"), array('\\', "'"), $value);
	}
	return stripcslashes($value);
}

/**
 * Maps const-based constant definitions to their values, 
 * 	for subsequent resolving hook invocations that use constant names 
 * 	rather than string literals
 * @return array<string, string>
 */
function wptsHooksStringConstants(array $tokens): array {
	$constants = array();
	$count = count($tokens);
	for ($i = 0; $i < $count; $i++) {
		if ($tokens[$i]['id'] !== T_CONST) {
			continue;
		}

		$nameIndex = wptsHooksNextMeaningfulIndex($tokens, $i);
		$equalsIndex = $nameIndex !== null 
			? wptsHooksNextMeaningfulIndex($tokens, $nameIndex) 
			: null;
		$valueIndex = $equalsIndex !== null 
			? wptsHooksNextMeaningfulIndex($tokens, $equalsIndex) 
			: null;

		if ($nameIndex === null || $equalsIndex === null || $valueIndex === null
			|| $tokens[$nameIndex]['id'] !== T_STRING
			|| $tokens[$equalsIndex]['text'] !== '='
			|| $tokens[$valueIndex]['id'] !== T_CONSTANT_ENCAPSED_STRING) {
			continue;
		}

		$value = wptsHooksDecodeStringLiteral($tokens[$valueIndex]['text']);
		if ($value !== null) {
			$constants[$tokens[$nameIndex]['text']] = $value;
		}
	}
	return $constants;
}

/**
 * @return array{arguments: string[], closeIndex: int}|null
 */
function wptsHooksArguments(array $tokens, int $openIndex): ?array {
	$arguments = array();
	$current = '';
	$parentheses = 1;
	$brackets = 0;
	$braces = 0;
	$count = count($tokens);

	for ($i = $openIndex + 1; $i < $count; $i++) {
		$text = $tokens[$i]['text'];
		if ($text === '(') {
			$parentheses++;
		} elseif ($text === ')') {
			$parentheses--;
			if ($parentheses === 0) {
				if (trim($current) !== '' || !empty($arguments)) {
					$arguments[] = trim($current);
				}
				return array('arguments' => $arguments, 'closeIndex' => $i);
			}
		} elseif ($text === '[') {
			$brackets++;
		} elseif ($text === ']') {
			$brackets--;
		} elseif ($text === '{') {
			$braces++;
		} elseif ($text === '}') {
			$braces--;
		}

		if ($text === ',' && $parentheses === 1 && $brackets === 0 && $braces === 0) {
			$arguments[] = trim($current);
			$current = '';
			continue;
		}
		$current .= $text;
	}
	return null;
}

/**
 * @return array{comment: string, line: int}|null
 */
function wptsHooksAssociatedDocComment(array $tokens, int $callIndex): ?array {
	for ($i = $callIndex - 1; $i >= 0; $i--) {
		$token = $tokens[$i];
		if ($token['id'] === T_DOC_COMMENT) {
			return array('comment' => $token['text'], 'line' => $token['line']);
		}
		if (in_array($token['text'], array(';', '{', '}'), true) || $token['id'] === T_OPEN_TAG) {
			return null;
		}
	}
	return null;
}

function wptsHooksResolveName(string $expression, array $constants): ?string {
	$expression = trim($expression);
	$expressionTokens = wptsHooksTokens('<?php ' . $expression . ';');
	$meaningful = array_values(array_filter($expressionTokens, 
		static function(array $token): bool {
			return !wptsHooksIsTrivia($token) 
				&& $token['id'] !== T_OPEN_TAG 
				&& $token['text'] !== ';';
		}
	));

	//Hook name is string literal
	if (count($meaningful) === 1 && $meaningful[0]['id'] === T_CONSTANT_ENCAPSED_STRING) {
		return wptsHooksDecodeStringLiteral($meaningful[0]['text']);
	}

	//Hook name is constant reference
	if (preg_match('/^(?:self|static|[A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::([A-Za-z_][A-Za-z0-9_]*)$/D', $expression, $matches)) {
		return $constants[$matches[1]] ?? null;
	}

	//This would decode concatenated strings, 
	//	but dynamic dispatch names will yield null
	//	and they will end up in the unresolvedDispatchers 
	//	collection for manual processing
	$expectString = true;
	$result = '';
	foreach ($meaningful as $token) {
		if ($expectString && $token['id'] === T_CONSTANT_ENCAPSED_STRING) {
			$value = wptsHooksDecodeStringLiteral($token['text']);
			if ($value === null) {
				return null;
			}
			$result .= $value;
			$expectString = false;
		} elseif (!$expectString && $token['text'] === '.') {
			$expectString = true;
		} else {
			return null;
		}
	}
	return !$expectString && $result !== '' ? $result : null;
}

function wptsHooksCurrentScope(array $scopeStack, string $kind): ?string {
	for ($i = count($scopeStack) - 1; $i >= 0; $i--) {
		if ($scopeStack[$i]['kind'] === $kind && $scopeStack[$i]['name'] !== null) {
			return $scopeStack[$i]['name'];
		}
	}
	return null;
}

/**
 * @return array<int, array<string, mixed>>
 */
function wptsHooksCallsInFile(string $root, string $file, string $prefix): array {
	$source = file_get_contents($file);
	if ($source === false) {
		throw new RuntimeException("Could not read {$file}");
	}

	$tokens = wptsHooksTokens($source);
	$constants = wptsHooksStringConstants($tokens);
	$scopeStack = array();
	$pendingScope = null;
	$calls = array();
	$count = count($tokens);

	$classTokens = array(
		T_CLASS, 
		T_INTERFACE, 
		T_TRAIT
	);

	$dispatcherDisqualifierTokens = array(
		T_OBJECT_OPERATOR, 
		T_NULLSAFE_OBJECT_OPERATOR, 
		T_DOUBLE_COLON, 
		T_FUNCTION
	);

	for ($i = 0; $i < $count; $i++) {
		$token = $tokens[$i];
		$previousIndex = wptsHooksPreviousMeaningfulIndex($tokens, $i);
		$previous = $previousIndex !== null ? $tokens[$previousIndex] : null;

		if (in_array($token['id'], $classTokens, true)
			&& ($previous === null || $previous['id'] !== T_DOUBLE_COLON)) {
			$nameIndex = wptsHooksNextMeaningfulIndex($tokens, $i);
			$name = $nameIndex !== null && $tokens[$nameIndex]['id'] === T_STRING
				? $tokens[$nameIndex]['text']
				: null;
			$pendingScope = array(
				'kind' => 'class', 
				'name' => $name
			);
		} elseif ($token['id'] === T_FUNCTION) {
			$name = null;
			for ($j = $i + 1; $j < $count; $j++) {
				if (wptsHooksIsTrivia($tokens[$j]) || $tokens[$j]['text'] === '&') {
					continue;
				}
				if ($tokens[$j]['id'] === T_STRING) {
					$name = $tokens[$j]['text'];
				}
				break;
			}
			$pendingScope = array(
				'kind' => 'function', 
				'name' => $name
			);
		}

		if ($token['text'] === '{') {
			$scopeStack[] = $pendingScope ?? array(
				'kind' => 'block', 
				'name' => null
			);
			$pendingScope = null;
			continue;
		}
		if ($token['text'] === '}') {
			array_pop($scopeStack);
			continue;
		}

		$callName = ltrim($token['text'], '\\');
		$isHookDispacher = isset(WPTS_HOOK_DISPATCHERS[$callName]);
		$isClassWrappedHookDispatcher = isset(WPTS_HOOK_CLASS_WRAPPED_DISPATCHERS[$callName]);

		if (!$isHookDispacher && !$isClassWrappedHookDispatcher) {
			continue;
		}

		if ($previous !== null 
			&& $isHookDispacher 
			&& in_array($previous['id'], 
				$dispatcherDisqualifierTokens, 
				true)) {
			continue;
		}

		if ($previous !== null 
			&& $isClassWrappedHookDispatcher 
			&& $previous['id'] !== T_NEW) {
			continue;
		}

		$openIndex = wptsHooksNextMeaningfulIndex($tokens, $i);
		if ($openIndex === null || $tokens[$openIndex]['text'] !== '(') {
			continue;
		}

		$parsed = wptsHooksArguments($tokens, $openIndex);
		if ($parsed === null || empty($parsed['arguments'])) {
			continue;
		}

		$nameExpression = $parsed['arguments'][0];
		$hookName = wptsHooksResolveName($nameExpression, $constants);
		if ($hookName !== null && !str_starts_with($hookName, $prefix)) {
			continue;
		}

		$enclosingClass = wptsHooksCurrentScope($scopeStack, 'class');
		if ($hookName === null 
			&& $isHookDispacher 
			&& isset(WPTS_HOOK_CLASS_WRAPPED_DISPATCHERS[$enclosingClass])) {
			continue;
		}

		$kind = null;
		if ($isHookDispacher) {
			$kind = WPTS_HOOK_DISPATCHERS[$callName];
		} elseif ($isClassWrappedHookDispatcher) {
			$kind = WPTS_HOOK_CLASS_WRAPPED_DISPATCHERS[$callName];
		}

		$doc = wptsHooksAssociatedDocComment($tokens, $i);
		$calls[] = array(
			'hookName' => $hookName,
			'kind' => $kind,
			'dispatcher' => $callName,
			'resolution' => $hookName === null ? 'dynamic' : 'static',
			'nameExpression' => $nameExpression,
			'arguments' => array_slice($parsed['arguments'], 1),
			'file' => wptsHooksRelativePath($root, $file),
			'line' => $token['line'],
			'enclosingClass' => wptsHooksCurrentScope($scopeStack, 'class'),
			'enclosingFunction' => wptsHooksCurrentScope($scopeStack, 'function'),
			'documentationStatus' => $doc === null ? 'missing' : 'documented',
			'docCommentLine' => $doc['line'] ?? null,
			'docComment' => $doc['comment'] ?? null,
			'prefixEvidence' => $hookName !== null || str_contains($nameExpression, $prefix),
		);
	}
	return $calls;
}

function wptsHooksSortCalls(array &$calls): void {
	usort($calls, static function(array $left, array $right): int {
		return array($left['file'], $left['line'], $left['dispatcher'])
			<=> array($right['file'], $right['line'], $right['dispatcher']);
	});
}

$root = null;
$prefix = 'abp01_';
$outputFile = null;
$pretty = false;
foreach (array_slice($argv, 1) as $argument) {
	if ($argument === '--pretty') {
		$pretty = true;
	} elseif (str_starts_with($argument, '--prefix=')) {
		$prefix = substr($argument, strlen('--prefix='));
	} elseif (str_starts_with($argument, '--output=')) {
		$outputFile = substr($argument, strlen('--output='));
	} elseif (str_starts_with($argument, '--')) {
		wptsHooksUsage();
	} elseif ($root === null) {
		$root = $argument;
	} else {
		wptsHooksUsage();
	}
}

if ($root === null || $prefix === '') {
	wptsHooksUsage();
}
$root = realpath($root);
if ($root === false || !is_dir($root)) {
	fwrite(STDERR, "Plugin root is not a readable directory.\n");
	exit(2);
}

try {
	$documented = array();
	$undocumented = array();
	$unresolved = array();

	foreach (wptsHooksProductionFiles($root) as $file) {
		foreach (wptsHooksCallsInFile($root, $file, $prefix) as $call) {
			if ($call['resolution'] === 'dynamic') {
				$unresolved[] = $call;
			} elseif ($call['documentationStatus'] === 'documented') {
				$documented[] = $call;
			} else {
				$undocumented[] = $call;
			}
		}
	}

	wptsHooksSortCalls($documented);
	wptsHooksSortCalls($undocumented);
	wptsHooksSortCalls($unresolved);

	$report = array(
		'schemaVersion' => 1,
		'prefix' => $prefix,
		'scanScope' => array('*.php', 'lib/**/*.php', 'views/**/*.php'),
		'summary' => array(
			'totalDispatches' => count($documented) 
				+ count($undocumented) 
				+ count($unresolved),
			'documented' => count($documented),
			'undocumented' => count($undocumented),
			'unresolvedDynamic' => count($unresolved),
		),
		'documentedHooks' => $documented,
		'undocumentedHooks' => $undocumented,
		'unresolvedDispatchers' => $unresolved,
	);

	$options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
	if ($pretty) {
		$options |= JSON_PRETTY_PRINT;
	}

	$json = json_encode($report, $options) . "\n";
	if ($outputFile !== null) {
		if (file_put_contents($outputFile, $json) === false) {
			throw new RuntimeException("Could not write report to {$outputFile}");
		}
	} else {
		echo $json;
	}
} catch (Throwable $error) {
	fwrite(STDERR, $error->getMessage() . "\n");
	exit(1);
}
