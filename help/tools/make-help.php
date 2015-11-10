<?php
require_once dirname(__FILE__) 
	. '/parsedown/Parsedown.php';
require_once dirname(__FILE__) 
	. '/parsedown-extra/ParsedownExtra.php';

function abp01_scan_source_directory() {
	$langDirs = array();
	$sourceRootDirectory = dir(realpath(dirname(__FILE__) . '/../src'));
	if (!$sourceRootDirectory) {
		return null;
	}
	return $langDirs;
}

$parser = new ParsedownExtra();
$content = file_get_contents(realpath(dirname(__FILE__) . '/../src/ro_RO/index.md'));
$content = mb_convert_encoding($content, 'UTF-8');

$translated = $parser->parse($content);
$translated = mb_convert_encoding($translated, 'UTF-8');

file_put_contents('./test.html', $translated);