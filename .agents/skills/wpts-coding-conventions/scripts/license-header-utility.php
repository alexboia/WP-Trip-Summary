<?php
declare(strict_types=1);

require_once __DIR__ . '/LicenseHeader.php';
require_once __DIR__ . '/LicenseHeaderReader.php';
require_once __DIR__ . '/LicenseHeaderGenerator.php';
require_once __DIR__ . '/LicenseHeaderUpdater.php';

use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderReader;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderUpdater;

function wptsLicenseHeaderUtilityUsage(int $exitCode = 2): never {
	$stream = $exitCode === 0 ? STDOUT : STDERR;
	fwrite($stream, "Usage: php license-header-utility.php <file> [--read [--full|--json] | --check | --update [--dry-run]] [--year=YYYY]\n" .
		"Default action: --read. Default year: current year. --year applies to --check and --update.\n" .
		"--check exits 1 for a missing/outdated header; --dry-run prints updated source without writing.\n" .
		"--update supports PHP/PHTML, JS, TS and CSS files and never decreases the existing year.\n");
	exit($exitCode);
}

$file = null;
$full = false;
$json = false;
$dryRun = false;
$action = null;
$year = null;

foreach (array_slice($argv, 1) as $argument) {
	if ($argument === '--help') {
		wptsLicenseHeaderUtilityUsage(0);
	} else if ($argument === '--full') {
		$full = true;
	} else if ($argument === '--json') {
		$json = true;
	} else if ($argument === '--dry-run') {
		$dryRun = true;
	} else if (in_array($argument, array('--read', '--check', '--update'), true)) {
		if ($action !== null) {
			wptsLicenseHeaderUtilityUsage();
		}
		$action = substr($argument, 2);
	} else if (preg_match('/^--year=(\d{4})$/', $argument, $matches)) {
		$year = intval($matches[1]);
		if ($year < 2014) {
			wptsLicenseHeaderUtilityUsage();
		}
	} else if (!str_starts_with($argument, '--') && $file === null) {
		$file = $argument;
	} else {
		wptsLicenseHeaderUtilityUsage();
	}
}

$action = $action ?? 'read';
if (empty($file)
	|| ($dryRun && $action !== 'update')
	|| (($full || $json) && $action !== 'read')
	|| ($full && $json)
	|| ($year !== null && $action === 'read')) {
	wptsLicenseHeaderUtilityUsage();
}
$year = $year ?? intval(date('Y'));

try {
	if ($action === 'update') {
		$updater = new LicenseHeaderUpdater($file);
		if ($dryRun) {
			echo $updater->preview($year);
		} else {
			$changed = $updater->update($year);
			echo ($changed ? 'Updated' : 'Unchanged') . " license header: $file\n";
		}
		exit(0);
	}

	$reader = new LicenseHeaderReader($file);
	$header = $reader->read();
	if ($action === 'check') {
		$current = $header !== null && $header->year >= $year;
		echo ($current ? 'Current' : 'Missing or outdated') . " license header: $file\n";
		exit($current ? 0 : 1);
	}

	if ($json) {
		echo ($header !== null ? $header->toJson() : 'null') . "\n";
	} else if ($header === null) {
		echo "No license header found in file: $file\n";
	} else {
		echo "File $file has license header at offset $header->offset for year $header->year.\n";
		if ($full) {
			echo $header->normalizedHeader() . "\n";
		}
	}
} catch (Throwable $error) {
	fwrite(STDERR, $error->getMessage() . "\n");
	exit(1);
}