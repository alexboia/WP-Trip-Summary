<?php
require_once dirname(__FILE__) 
	. '/parsedown/Parsedown.php';
require_once dirname(__FILE__) 
	. '/parsedown-extra/ParsedownExtra.php';

/**
 * Retrieves the base directory where all of the help files are stored, grouped per language subdirectory
 * @return String The base directory
 */
function abp01_get_help_files_destination() {
	return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR 
		. '..' . DIRECTORY_SEPARATOR 
		. '..' . DIRECTORY_SEPARATOR
		. 'data' . DIRECTORY_SEPARATOR 
		. 'help');
}

/**
 * Creates and configures the markdown parser used to create the HTML help files
 * @return ParsedownExtra The parser
 */
function abp01_get_markdown_parser() {
	$parser = new ParsedownExtra();
	$parser->setBreaksEnabled(true);
	$parser->setUrlsLinked(true);
	return $parser;
}

/**
 * Scans a sub-directory that contains language-specific .md help source files.
 * Each file is expressed as an absolute path
 * @param String $langDirPath The absolute path to the language-specific directory
 * @return String The list of found help source files
 */
function abp01_scan_lang_directory($langDirPath) {
	$source = $langDirPath . DIRECTORY_SEPARATOR . 'index.md';

	if (is_readable($source)) {
		return array($source);
	} else {
		return null;
	}
}

/**
 * Scans the entire top level help source directory and returns an array of source files, grouped by language.
 * Each file is expressed as an absolute path
 */
function abp01_scan_source_directory() {
	$convertFiles = array();
	$scanDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR 
		. '..' . DIRECTORY_SEPARATOR 
		. 'src');
	
	$sourceRootDirectory = dir($scanDir);	
	if (!$sourceRootDirectory) {
		return null;
	}
	
	while (($entry = $sourceRootDirectory->read()) !== false) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}
		
		$langDir = $scanDir . DIRECTORY_SEPARATOR . $entry;
		if (!is_dir($langDir)) {
			continue;			
		}
		
		$convertLangFiles = abp01_scan_lang_directory($langDir);		
		if (!empty($convertLangFiles) && is_array($convertLangFiles)) {
			$convertFiles[$entry] = $convertLangFiles;
		}
	}

	$sourceRootDirectory->close();	
	return $convertFiles;
}

/**
 * Converts the given source file from Markdown format to HTML format
 * @param String $file The absolute file path
 * @return String The converted contents
 */
function abp01_convert_source_file($file) {
	$fileContents = file_get_contents($file);
	$fileContents = mb_convert_encoding($fileContents, 'UTF-8');
	if (!empty($fileContents)) {
		$parser = abp01_get_markdown_parser();
		$fileContents = $parser->parse($fileContents);
		$fileContents = mb_convert_encoding($fileContents, 'UTF-8');
	}
	return $fileContents;
}

/**
 * Converts all the given files and stores them in the corresponding language sub-directory in the given base directory.
 * It also optionally wraps the converted file contents into a HTML body based on a template stored in ./templates/help-template.html
 * @param String $destinationBase The base directory where all help files are stored, grouped in language sub-directories
 * @param String $lang The language to which the converted files belong
 * @param Array $files The list of files to convert, each file expressed as an absolute path
 * @param Boolean $wrapInHtmlBody Whether to wrap the contents of each converted file in an HTML body or not
 * @return Boolean True if succeeded, False otherwise
 */
function abp01_convert_files($destinationBase, $lang, $files, $wrapInHtmlBody) {
	$destination = $destinationBase . DIRECTORY_SEPARATOR . $lang;
	if (!is_dir($destination)) {
		@mkdir($destination);
		if (!is_dir($destination)) {
			return false;
		}
	}
	
	foreach ($files as $file) {
		$destinationFile = $destination . DIRECTORY_SEPARATOR . basename($file);
		$destinationFile = str_replace('.md', '.html', $destinationFile);
		
		$convertedFileContents = abp01_convert_source_file($file);
		if ($wrapInHtmlBody) {
			$convertedFileContents = abp01_wrap_in_html_body('WP-Trip-Summary Help', $convertedFileContents);
		}
		
		file_put_contents($destinationFile, $convertedFileContents);
	}
	
	return true;
}

/**
 * Wraps the given contents to an HTML body with the given title
 * @param String $title The HTML document title
 * @param String $contents The contents that will be wrapped
 * @return String The wrapped contents
 */
function abp01_wrap_in_html_body($title, $contents) {
	$templatePath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR 
		. 'templates' . DIRECTORY_SEPARATOR 
		. 'help-template.html');
	
	$template = file_get_contents($templatePath);
	return str_replace(
		array('{{helpDocumentTitle}}', '{{helpDocumentContents}}'), 
		array($title, $contents), 
			$template);
}

/**
 * Runs the conversion process
 * @param Boolean $wrapInHtmlBody Whether to wrap the contents of each converted file in an HTML body or not
 * @return String An error message, or empty if the operation succeeded
 */
function abp01_run_conversion($wrapInHtmlBody) {
	$sourceFiles = abp01_scan_source_directory();
	$destinationBase = abp01_get_help_files_destination();
	
	if (!is_array($sourceFiles)) {
		return 'Source directory could not be read';
	}
	
	if (empty($sourceFiles)) {
		return 'No source files found';
	}
	
	foreach ($sourceFiles as $lang => $files) {
		abp01_convert_files($destinationBase, $lang, $files, $wrapInHtmlBody);
	}
	
	return '';
}

if ($argc > 1) {
	$wrapInHtmlBody = $argv[1] === '--wrap';
} else {
	$wrapInHtmlBody = false;
}

abp01_run_conversion($wrapInHtmlBody);