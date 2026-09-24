<?php
declare(strict_types=1);

require_once __DIR__ . '/LicenseHeader.php';
require_once __DIR__ . '/LicenseHeaderReader.php';
require_once __DIR__ . '/LicenseHeaderGenerator.php';
require_once __DIR__ . '/LicenseHeaderUpdater.php';

const ACTION_READ = 'read';
const ACTION_UPDATE = 'update';

use WpTripSummary\Skills\WpCodingConventions\LicenseHeader;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderReader;
use WpTripSummary\Skills\WpCodingConventions\LicenseHeaderUpdater;

function wptsLicenseHeaderUtilityUsage(): never {
	fwrite(STDERR, "Usage: php license-header-utility.php <file> [--read] [--full] [--json]\n");
	exit(2);
}

function wptsExtractLicenseHeader(string $file, bool $json, bool $full): never {
	if (!is_readable($file)) {
		fwrite(STDERR, "File $file is not readable.\n");
		exit(1);
	}

	$reader = new LicenseHeaderReader($file);
	$headerInfo = $reader->read();

	if ($headerInfo !== null) {
		if (!$json) {
			if ($full) {
				echo "\nFile $file has license header " 
					. "at offset $headerInfo->offset " 
					. "for year $headerInfo->year. " 
					. "Contents: \n" . $headerInfo->normalizedHeader() . "\n";
			} else {
				echo "\nFile $file has license header " 
					. "at offset $headerInfo->offset " 
					. "for year $headerInfo->year.\n";
			}			
		}  else {
			echo "\n" . $headerInfo->toJson() . "\n";
		}
	} else {
		echo "No license header found in file: $file";
	}

	exit(0);
}

function wptsUpdateLicenseHeader(string $file, ?int $year): never {
	if (!is_readable($file)) {
		fwrite(STDERR, "File $file is not readable.\n");
		exit(1);
	}

	if ($year === null || $year <= 0) {
		$year = intval(date("Y"));
	}

	$updater = new LicenseHeaderUpdater($file);
	$updater->update($year);
	
	exit(0);
}

$file = null;
$full = false;
$json = false;
$action = ACTION_READ;
$arguments = array_slice($argv, 1);

foreach ($arguments as $argument) {
	if ($argument === '--full') {
		$full = true;
	} else if ($argument === '--json') {
		$json = true;
	} else if ($argument === '--read') {
		$action = ACTION_READ;
	} else if ($argument === '--update') {
		$action = ACTION_UPDATE;
	} else if ($file === null) {
		$file = $argument;
	} else {
		wptsLicenseHeaderUtilityUsage();
	}
}

if (empty($file)) {
	wptsLicenseHeaderUtilityUsage();
}

switch ($action) {
	case ACTION_READ:
		wptsExtractLicenseHeader($file, $json, $full);
		break;
	case ACTION_UPDATE:
		wptsUpdateLicenseHeader($file, null);
		break;
}