<?php
if (getenv('WPTS_RELEASE_TEST_FAILURE') === 'readme') {
	exit(1);
}
$outputDirectory = substr($argv[1], strlen('--output-dir='));
mkdir($outputDirectory, 0777, true);
copy(__DIR__ . '/../../README.source.txt', $outputDirectory . '/README.txt');
