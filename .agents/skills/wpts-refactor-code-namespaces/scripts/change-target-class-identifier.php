<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/cli.php';

const EXIT_AMBIGUOUS_TARGET = 3;
const EXIT_WRITE_FAILURE = 4;

function usage(): never {
	fail(
		'Usage: php change-target-class-identifier.php <file> <legacy-name> [--dry-run]',
		EXIT_USAGE
	);
}

function assertIdentifier(string $identifier, string $label): void {
	if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $identifier)) {
		fail("Invalid {$label}: {$identifier}", EXIT_INVALID_INPUT);
	}
}

function isFileValid(string|bool $file): bool {
	return $file !== false 
		&& is_file($file) 
		&& strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php';
}

function deriveFqcn(string $legacyName): string {
	$legacyPrefix = 'Abp01_';
	if (!str_starts_with($legacyName, $legacyPrefix)) {
		fail("Legacy identifier must begin with {$legacyPrefix}: {$legacyName}", EXIT_INVALID_INPUT);
	}

	$nameParts = explode('_', substr($legacyName, strlen($legacyPrefix)));
	if (in_array('', $nameParts, true)) {
		fail("Legacy identifier contains an empty name segment: {$legacyName}", EXIT_INVALID_INPUT);
	}

	return 'WpTripSummary\\' . implode('\\', $nameParts);
}

function lineEnding(string $source): string {
	//Determines line ending for original source file
	return str_contains($source, "\r\n") 
		? "\r\n" 
		: "\n";
}

function tokenOffsets(string $source): array {
	$offset = 0;
	$result = array();
	foreach (token_get_all($source) as $token) {
		$text = is_array($token) ? $token[1] : $token;
		$result[] = array(
			'id' => is_array($token) ? $token[0] : null,
			'text' => $text,
			'offset' => $offset,
		);
		$offset += strlen($text);
	}
	return $result;
}

function isIgnoredToken(array $token): bool {
	$ignored = array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT);
	return in_array($token['id'], 
		$ignored, 
		true);
}

function nextMeaningfulTokenIndex(array $tokens, int $start): ?int {
	//Simply look for the next non-trivial token: 
	// not whitespace, not comment, not doc comment.
	$count = count($tokens);
	for ($index = $start; $index < $count; $index++) {
		if (!isIgnoredToken($tokens[$index])) {
			return $index;
		}
	}
	return null;
}

function buildReplacements(array $tokens, string $legacyName, string $shortName): array {
	//Find references to legacy name and register a replacement 
	//	for each (tailored for substr_replace).
	// A replacement item holds: 
	//	- the original offset, 
	//	- original symbol length 
	//	- and new value, i.e. the new short name
	$replacements = array();
	foreach ($tokens as $token) {
		if ($token['id'] === T_STRING && $token['text'] === $legacyName) {
			$replacements[] = array(
				'offset' => $token['offset'],
				'length' => strlen($legacyName),
				'value' => $shortName,
			);
		}
	}

	return $replacements;
}

function applyReplacements(string $source, array $replacements): string {
	// Spaceship operator:
	//	- returns 0 if values on either side are equal
	// 	- returns 1 if the value on the left is greater
	// 	- returns -1 if the value on the right is greater
	usort($replacements, 
		static fn(array $left, array $right): int 
			=> $right['offset'] <=> $left['offset']
		);

	foreach ($replacements as $replacement) {
		$source = substr_replace(
			$source,
			$replacement['value'],
			$replacement['offset'],
			$replacement['length']
		);
	}
	return $source;
}

function indentBlock(string $source, string $eol): string {
	$lines = preg_split('/\r\n|\n|\r/', $source);
	if ($lines === false) {
		return $source;
	}
	return implode($eol, array_map(
		static fn(string $line): string => $line === '' ? '' : "\t" . $line,
		$lines
	));
}

function hasNamespaceDeclaration(array $tokens): bool {
	$nsTokens = array_filter($tokens, 
		static fn(array $token): bool 
			=> $token['id'] === T_NAMESPACE);

	return !empty($nsTokens);
}

function getSymbolTokenIds(): array {
	//Looking for: class defintions, interface definitions, trait definitions
	//	and, if supported, enum definitions 
	//	(which is not the case here, but anyway)
	$symbolTokenIds = array(T_CLASS, T_INTERFACE, T_TRAIT);
	if (defined('T_ENUM')) {
		$symbolTokenIds[] = T_ENUM;
	}

	return $symbolTokenIds;
}

function findDeclarationNameIndexes(array $tokens, string $legacyName): array {
	$declarationNameIndexes = array();
	$symbolTokenIds = getSymbolTokenIds();

	foreach ($tokens as $index => $token) {
		if (!in_array($token['id'], $symbolTokenIds, true)) {
			continue;
		}
		$nameIndex = nextMeaningfulTokenIndex($tokens, $index + 1);
		if ($nameIndex !== null
			&& $tokens[$nameIndex]['id'] === T_STRING
			&& $tokens[$nameIndex]['text'] === $legacyName) {
			$declarationNameIndexes[] = $nameIndex;
		}
	}

	return $declarationNameIndexes;
}

function getExistingStrictTypesDeclarationOffsetEnd(string $converted): ?int {
	$insertOffset = null;
	$strictMatch = array();

	$hasStrictTypes = (bool) preg_match(
		'/\bdeclare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/i',
		$converted,
		$strictMatch,
		PREG_OFFSET_CAPTURE
	);

	if ($hasStrictTypes) {
		$insertOffset = $strictMatch[0][1] + strlen($strictMatch[0][0]);
	}

	return $insertOffset;
}

function isClassNameToken(array $token): bool {
	$nameTokenIds = array(T_STRING);
	foreach (array('T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED') as $tokenConstant) {
		if (defined($tokenConstant)) {
			$nameTokenIds[] = constant($tokenConstant);
		}
	}
	return in_array($token['id'], $nameTokenIds, true);
}

function normalizeClassDependency(string $dependency, string $legacyName): ?string {
	$dependency = ltrim($dependency, '\\');
	$notExplicitClassNames = array('self', 'static', 'parent');

	if ($dependency === ''
		|| $dependency === $legacyName
		|| in_array(strtolower($dependency), 
			$notExplicitClassNames, 
			true)) {
		return null;
	}
	return $dependency;
}

function addClassDependency(array &$dependencies, string $dependency, string $legacyName): void {
	$dependency = normalizeClassDependency($dependency, $legacyName);
	if ($dependency !== null) {
		$dependencies[$dependency] = true;
	}
}

function determineClassDependencies(array $tokens, string $legacyName): array {
	$dependencies = array();
	$previousMeaningfulToken = null;
	$expectSingleClass = false;
	$inImplementsList = false;
	$inCatchType = false;
	$waitingForCatchParenthesis = false;

	$classNamesAfter = array(T_NEW, 
		T_INSTANCEOF, 
		T_EXTENDS);

	foreach ($tokens as $token) {
		if (isIgnoredToken($token)) {
			continue;
		}

		if (in_array($token['id'], $classNamesAfter, true)) {
			$expectSingleClass = true;
			$previousMeaningfulToken = $token;
			continue;
		}

		if ($token['id'] === T_IMPLEMENTS) {
			$inImplementsList = true;
			$previousMeaningfulToken = $token;
			continue;
		}

		if ($token['id'] === T_CATCH) {
			$waitingForCatchParenthesis = true;
			$previousMeaningfulToken = $token;
			continue;
		}

		if ($waitingForCatchParenthesis && $token['text'] === '(') {
			$waitingForCatchParenthesis = false;
			$inCatchType = true;
			$previousMeaningfulToken = $token;
			continue;
		}

		if (isClassNameToken($token)) {
			if ($expectSingleClass || $inImplementsList || $inCatchType) {
				addClassDependency($dependencies, 
					$token['text'], 
					$legacyName);

				$expectSingleClass = false;
			} elseif ($previousMeaningfulToken !== null
				&& $previousMeaningfulToken['id'] === T_DOUBLE_COLON) {
				// The class name is before ::, so it is handled when :: is reached.
			} 
		}

		if ($token['id'] === T_DOUBLE_COLON
			&& $previousMeaningfulToken !== null
			&& isClassNameToken($previousMeaningfulToken)) {
			addClassDependency($dependencies, 
				$previousMeaningfulToken['text'], 
				$legacyName);
		}

		if ($inImplementsList
			&& in_array($token['text'], array('{', ';'), true)) {
			$inImplementsList = false;
		}
		if ($inCatchType
			&& ($token['id'] === T_VARIABLE || $token['text'] === ')')) {
			$inCatchType = false;
		}
		if ($expectSingleClass && ($token['id'] === T_VARIABLE || $token['id'] === T_CLASS)) {
			// Dynamic instantiation or an anonymous class cannot be imported.
			$expectSingleClass = false;
		}

		$previousMeaningfulToken = $token;
	}

	return array_keys($dependencies);
}

function hasTopLevelUseDeclaration(array $tokens): bool {
	$braceDepth = 0;
	$previousMeaningfulToken = null;
	foreach ($tokens as $token) {
		if (isIgnoredToken($token)) {
			continue;
		}
		if ($token['text'] === '{') {
			$braceDepth++;
		} elseif ($token['text'] === '}') {
			$braceDepth = max(0, $braceDepth - 1);
		} elseif ($token['id'] === T_USE
			&& $braceDepth === 0
			&& ($previousMeaningfulToken === null || $previousMeaningfulToken['text'] !== ')')) {
			return true;
		}
		$previousMeaningfulToken = $token;
	}
	return false;
}

function writeChanges(string $converted, string $file): bool {
	$temporaryFile = $file . '.wpts-namespace-' 
		. bin2hex(random_bytes(6)) 
		. '.tmp';

	if (file_put_contents($temporaryFile, $converted) === false 
		|| !rename($temporaryFile, $file)) {

		if (is_file($temporaryFile)) {
			unlink($temporaryFile);
		}
		return false;
	}

	return true;
}

if ($argc < 3 || $argc > 4) {
	usage();
}

$file = realpath($argv[1]);
$legacyName = $argv[2];
$dryRun = isDryRun($argv);

if ($argc === 4 && !$dryRun) {
	usage();
}

if (!isFileValid($file)) {
	fail('Target must be an existing PHP file.', EXIT_INVALID_INPUT);
}

assertIdentifier($legacyName, 'legacy identifier');
$newFqcn = deriveFqcn($legacyName);

$newParts = explode('\\', $newFqcn);
$shortName = array_pop($newParts);
$namespace = implode('\\', $newParts);
$source = file_get_contents($file);

if ($source === false) {
	fail(
		"Could not read target file: {$file}", 
		EXIT_INVALID_INPUT
	);
}

//Tokenize file and quickly check that it does 
//	not already contain a namespace declaration, 
//	in which case exit with failure.
$tokens = tokenOffsets($source);
if (hasNamespaceDeclaration($tokens)) {
	fail(
		'Target already contains a namespace declaration; refusing an ambiguous conversion.', 
		EXIT_AMBIGUOUS_TARGET
	);
}

//Look for class declarations - there can be only one
$declarationNameIndexes = findDeclarationNameIndexes($tokens, 
	$legacyName);

if (count($declarationNameIndexes) !== 1) {
	fail(
		sprintf(
			'Expected exactly one declaration of %s; found %d.', 
			$legacyName, 
			count($declarationNameIndexes)),
		EXIT_AMBIGUOUS_TARGET
	);
}

$dependencies = determineClassDependencies($tokens, 
	$legacyName);

$replacements = buildReplacements($tokens, 
	$legacyName, 
	$shortName);

$converted = applyReplacements($source, 
	$replacements);

function findOpenTagIndex(array $tokens): int|null {
	$openTagIndex = null;
	foreach ($tokens as $index => $token) {
		if ($token['id'] === T_OPEN_TAG) {
			$openTagIndex = $index;
			break;
		}
	}

	return $openTagIndex;
}

$convertedTokens = tokenOffsets($converted);
$openTagIndex = findOpenTagIndex($convertedTokens);

if ($openTagIndex === null) {
	fail(
		'Target does not contain a PHP opening tag.', 
		EXIT_INVALID_INPUT
	);
}

//Determine the point where we need to insert the namespace declaration
//	What's before that is maintained.
//	What's after that is inserted between the namespace braces.
$insertIndex = $openTagIndex + 1;
$convertedCount = count($convertedTokens);
while ($insertIndex < $convertedCount
	&& isIgnoredToken($convertedTokens[$insertIndex])) {
	$insertIndex++;
}

$eol = lineEnding($converted);
$insertOffset = $convertedTokens[$insertIndex]['offset'] 
	?? strlen($converted);

$hasStrictTypes = false;
$strictTypesOffsetEnd = getExistingStrictTypesDeclarationOffsetEnd($converted);
if ($strictTypesOffsetEnd !== null && $strictTypesOffsetEnd >= 0) {
	$insertOffset = $strictTypesOffsetEnd;
	$hasStrictTypes = true;
}

//Make sure we maintain file header: license header and strict_types declaration, if any.
$beforeNamespace = rtrim(substr($converted, 0, $insertOffset));

//Namespace body is everything beyond that
$namespaceBody = trim(substr($converted, $insertOffset));
$hasExistingUsings = hasTopLevelUseDeclaration($tokens);
$newUsings = !$hasExistingUsings && !empty($dependencies)
	? $eol . join($eol, 
		array_map(
			static fn(string $dep): string => "use \\{$dep};", 
			$dependencies
		)) . $eol . $eol
	: '';

$namespaceBody = $newUsings . $namespaceBody;

$converted = $beforeNamespace
	. ($hasStrictTypes ? '' : $eol . $eol . 'declare(strict_types=1);')
	. $eol . $eol
	. 'namespace ' . $namespace . ' {' . $eol
	. indentBlock($namespaceBody, $eol) . $eol
	. '}' . $eol;

if ($dryRun) {
	fwrite(STDOUT, $converted);
	exit(0);
}

if (!writeChanges($converted, $file)) {
	fail(
		"Could not replace target file: {$file}", 
		EXIT_WRITE_FAILURE
	);
}

printf("Converted %s to %s in %s%s", 
	$legacyName, 
	$newFqcn, 
	$file, 
	$eol);
