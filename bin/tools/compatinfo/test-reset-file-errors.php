<?php
declare(strict_types=1);

/**
 * Standalone CLI regression checks using the installed PHP CompatInfo.
 * Creates an isolated reference database, cache, fixtures and reports under build/compat-info/.
 * Requires Composer development dependencies, sqlite3, pdo_sqlite and proc_open.
 */
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit;
}

function wpts_compatinfo_check_assert(bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

function wpts_compatinfo_check_run(string $root, string $testDirectory, array $environment,
	string $name, array $arguments): array {
	
	$stdout = $testDirectory . '/' . $name . '.stdout.txt';
	$stderr = $testDirectory . '/' . $name . '.stderr.txt';
	
	$command = array_merge(array(
		PHP_BINARY, '-d', 'memory_limit=1G',
		$root . '/vendor/bin/phpcompatinfo',
		'--no-interaction', '--no-ansi', '--config', __DIR__ . '/config.php'
	), $arguments);
	
	$descriptors = array(
		0 => array('pipe', 'r'),
		1 => array('file', $stdout, 'w'),
		2 => array('file', $stderr, 'w')
	);
	
	$process = proc_open($command, 
		$descriptors, 
		$pipes, 
		$root, 
		$environment);
	
	wpts_compatinfo_check_assert(is_resource($process), 
		'Could not start PHP CompatInfo.');
	
	fclose($pipes[0]);
	$exitCode = proc_close($process);

	return array($exitCode, 
		file_get_contents($stdout), 
		file_get_contents($stderr));
}

function wpts_compatinfo_check_report(array $result): void {
	wpts_compatinfo_check_assert($result[0] === 0, 
		'Analysis failed: ' . $result[1] . $result[2]);
	
	wpts_compatinfo_check_assert((bool) preg_match('/^\s*Files\s+4\s*$/m', $result[1]),
		'Expected all four fixture files to be analyzed.');
	
	wpts_compatinfo_check_assert((bool) preg_match('/^\s*Errors\s+2\s*$/m', $result[1]),
		'Expected exactly two errors, one for each invalid file.');
	
	preg_match_all('/^\s*! .* in file .*$/m', 
		$result[1], 
		$matches);

	$errors = implode("\n", $matches[0]);
	
	wpts_compatinfo_check_assert(count($matches[0]) === 2, 
		'Expected exactly two error details.');

	wpts_compatinfo_check_assert(str_contains($errors, 'File has no contents') && str_contains($errors, '00-empty.php'),
		'The empty-file error was lost.');

	wpts_compatinfo_check_assert(str_contains($errors, 'Syntax error') && str_contains($errors, '02-broken.php'),
		'The syntax error was lost.');

	wpts_compatinfo_check_assert(!str_contains($errors, '01-valid.php') && !str_contains($errors, '03-valid.php'),
		'A valid file inherited another file\'s error.');
}

$root = dirname(__DIR__, 3);
$testDirectory = $root . '/build/compat-info/file error tests-' 
	. bin2hex(random_bytes(5));

try {
	wpts_compatinfo_check_assert(is_file($root . '/vendor/bin/phpcompatinfo'),
		'Run composer install with development dependencies first.');

	$fixtures = $testDirectory . '/fixtures';
	wpts_compatinfo_check_assert(mkdir($fixtures, 0777, true), 
		'Could not create the test directory.');

	file_put_contents($fixtures . '/00-empty.php', 
		'');
	file_put_contents($fixtures . '/01-valid.php', 
		'<?php function first_valid_fixture(): int { return 1; }');
	file_put_contents($fixtures . '/02-broken.php', 
		'<?php function broken_fixture( {');
	file_put_contents($fixtures . '/03-valid.php', 
		'<?php function second_valid_fixture(): bool { return true; }');

	$environment = getenv();
	$environment['APP_ENV'] = 'prod';
	$environment['APP_DEBUG'] = '0';
	$environment['APP_CACHE_DIR'] = $testDirectory . '/cache';
	$environment['APP_LOG_DIR'] = $testDirectory . '/logs';
	$databasePath = str_replace('\\', 
		'/', 
		$testDirectory . '/reference.sqlite');
	
	if (PHP_OS_FAMILY === 'Windows') {
		$databasePath = substr($databasePath, 2);
	}
	$environment['DATABASE_URL'] = 'sqlite://' . $databasePath;

	$initialization = wpts_compatinfo_check_run($root, 
		$testDirectory, 
		$environment, 
		'database', 
		array('db:new'));
	
	wpts_compatinfo_check_assert($initialization[0] === 0,
		'Database initialization failed: ' . $initialization[1] . $initialization[2]);

	echo "[PASS] Custom configuration initializes an isolated database.\n";

	foreach (array('first-analysis', 'cached-analysis') as $name) {
		$result = wpts_compatinfo_check_run($root, 
			$testDirectory, 
			$environment, 
			$name, 
			array('analyser:run', $fixtures));

		wpts_compatinfo_check_report($result);
		
		echo '[PASS] ' . $name . ": real errors retained; valid files have no inherited errors.\n";
	}

	$valid = wpts_compatinfo_check_run($root, 
		$testDirectory, 
		$environment, 
		'valid-only',
		array('analyser:run', $fixtures . '/01-valid.php', 
			'--stop-on-failure'));
	
	wpts_compatinfo_check_assert($valid[0] === 0 && (bool) preg_match('/^\s*Errors\s+0\s*$/m', $valid[1]),
		'A valid file failed analysis.');
	
	echo "[PASS] Valid-file analysis stays successful with --stop-on-failure.\n";

	$stopped = wpts_compatinfo_check_run($root, 
		$testDirectory, 
		$environment, 
		'stop-on-failure',
		array('analyser:run', $fixtures, '--stop-on-failure'));

	wpts_compatinfo_check_assert($stopped[0] !== 0 &&
		(str_contains($stopped[1] . $stopped[2], 'File has no contents') ||
			str_contains($stopped[1] . $stopped[2], 'Syntax error')),
		'--stop-on-failure did not preserve the real error.');
	
	echo "[PASS] --stop-on-failure still fails on a real error.\n";

	$excludedFixtures = $testDirectory . '/excluded-fixtures';
	
	mkdir($excludedFixtures . '/ignored', 0777, true);
	file_put_contents($excludedFixtures . '/valid.php', '<?php function included_fixture(): int { return 1; }');
	file_put_contents($excludedFixtures . '/ignored/broken.php', '<?php function excluded_fixture( {');
	
	$excluded = wpts_compatinfo_check_run($root, 
		$testDirectory, 
		$environment, 
		'excluded-directory',
		array('analyser:run', $excludedFixtures, '--exclude', 'ignored'));

	wpts_compatinfo_check_assert($excluded[0] === 0 &&
		(bool) preg_match('/^\s*Files\s+1\s*$/m', $excluded[1]) &&
		(bool) preg_match('/^\s*Errors\s+0\s*$/m', $excluded[1]),
		'Source exclusions changed behavior.');

	echo "[PASS] --exclude still omits the requested directory.\n";

	$missing = wpts_compatinfo_check_run($root, 
		$testDirectory, 
		$environment, 
		'missing-source',
		array('analyser:run', $testDirectory . '/missing'));

	wpts_compatinfo_check_assert($missing[0] !== 0 && str_contains($missing[1] . $missing[2], 'No data source match'),
		'A missing source did not preserve the upstream failure.');
	
	echo "[PASS] Missing sources still fail with the upstream diagnostic.\n";
	echo 'Reports and fixtures: ' . $testDirectory . "\n";
} catch (Throwable $error) {
	fwrite(STDERR, '[FAIL] ' 
		. $error->getMessage() 
		. "\nReports and fixtures: " 
		. $testDirectory 
		. "\n");
	exit(1);
}
