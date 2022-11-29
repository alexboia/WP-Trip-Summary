<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(__FILE__) 
	. '/parsedown/Parsedown.php';
require_once dirname(__FILE__) 
	. '/parsedown-extra/ParsedownExtra.php';

function abp01_get_assets_source_directory() {
	return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR 
		. '..' . DIRECTORY_SEPARATOR 
		. '..' . DIRECTORY_SEPARATOR
		. 'assets');
}

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

function abp01_scan_assets_lang_directory($langDirPath) {
	return glob($langDirPath . DIRECTORY_SEPARATOR . '*.png');
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

function abp01_scan_assets_source_directory() {
	$copyFiles = array();
	$scanDir = abp01_get_assets_source_directory();
	
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
		
		$convertLangFiles = abp01_scan_assets_lang_directory($langDir);		
		if (!empty($convertLangFiles) && is_array($convertLangFiles)) {
			$copyFiles[$entry] = $convertLangFiles;
		}
	}

	$sourceRootDirectory->close();	
	return $copyFiles;
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

function abp01_copy_screenshots($destinationBase, $lang, $files) {
	$destination = $destinationBase 
		. DIRECTORY_SEPARATOR . $lang 
		. DIRECTORY_SEPARATOR . 'screenshots';

	if (!is_dir($destination)) {
		@mkdir($destination);
		if (!is_dir($destination)) {
			return false;
		}
	} else {
		$rmFiles = glob($destination . DIRECTORY_SEPARATOR . '*');
		foreach($rmFiles as $rmFile) {
			if (is_file($rmFile)) {
				@unlink($rmFile);
			}
		}
	}

	foreach ($files as $file) {
		$destinationFile = $destination . DIRECTORY_SEPARATOR . basename($file);
		@copy($file, $destinationFile);
	}
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

function abp01_sync_screenshots() {
	$screenshots = abp01_scan_assets_source_directory();
	$destinationBase = abp01_get_help_files_destination();

	if (!is_array($screenshots)) {
		return 'Screenshots source directory could not be read';
	}
	
	if (empty($screenshots)) {
		return 'No source screenshots found';
	}

	foreach ($screenshots as $lang => $langScrenshots) {
		abp01_copy_screenshots($destinationBase, $lang, $langScrenshots);
	}

	return '';
}

if ($argc > 1) {
	$wrapInHtmlBody = $argv[1] === '--wrap';
} else {
	$wrapInHtmlBody = false;
}

$conversionResult = abp01_run_conversion($wrapInHtmlBody);
if (empty($conversionResult)) {
	echo 'Conversion successful. Syncing screenshots...' . PHP_EOL;
	$copyResult = abp01_sync_screenshots();
	if (empty($copyResult)) {
		echo 'Screnshots synced successfully.' . PHP_EOL;
	} else {
		echo 'Failed to sync screenshots.' . PHP_EOL;
	}
} else {
	echo $conversionResult . PHP_EOL;
}
