<?php
declare(strict_types=1);

const EXIT_USAGE = 1;
const EXIT_INVALID_INPUT = 2;

function fail(string $message, int $exitCode): never {
	fwrite(STDERR, $message . PHP_EOL);
	exit($exitCode);
}

function isDryRun(array $args): bool {
	return !empty($args) && ($args[3] ?? null) === '--dry-run';
}