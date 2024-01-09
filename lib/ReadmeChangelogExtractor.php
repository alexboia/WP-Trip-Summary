<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_ReadmeChangelogExtractor {
	const README_BEGIN_MARKER_PATTERN = '/==([\s]*)Changelog([\s]*)==/i';

	const README_END_MAKER_PATTERN = '/==([^=]+)==/';

	const README_VERSION_PATTERN = '/=([^=]+)=/';

	private $_readmeFilePath;

	private $_readmeFilePointer = null;

	private $_changelog = array();

	private $_currentlyProcessedVersion = null;

	public function __construct($readmeFilePath) {
		if (empty($readmeFilePath)) {
			throw new InvalidArgumentException('Readme file path may not be empty!');
		}

		if (!is_readable($readmeFilePath)) {
			throw new InvalidArgumentException('Readme file not found!');
		}

		$this->_readmeFilePath = $readmeFilePath;
	}

	public function extractChangeLog() {
		$this->_resetChangeLogData();
		$filePointer = $this->_openReadmeFileForReading();

		if ($filePointer) {
			$this->_readmeFilePointer = $filePointer;
		} else {
			throw new Abp01_Exception('Readme file could not be open.');
		}

		while (($readmeLine = $this->_readCurrentLine()) !== false) {
			$readmeLine = trim($readmeLine);
			if (empty($readmeLine)) {
				continue;
			}

			if ($this->_isChangeLogBeginLine($readmeLine)) {
				$this->_processChangelogSection();
				break;
			}
		}

		fclose($this->_readmeFilePointer);

		$this->_currentlyProcessedVersion = null;
		$this->_readmeFilePointer = null;

		return $this->_changelog;
	}

	private function _resetChangeLogData() {
		$this->_changelog = array();
		$this->_currentlyProcessedVersion = null;
	}

	private function _openReadmeFileForReading() {
		return fopen($this->_readmeFilePath, 'r');
	}

	private function _readCurrentLine() {
		return fgets($this->_readmeFilePointer);
	}

	private function _isChangeLogBeginLine($line) {
		return preg_match(self::README_BEGIN_MARKER_PATTERN, $line) === 1;
	}

	private function _processChangelogSection() {
		while (($readmeLine = $this->_readCurrentLine()) !== false) {
			$readmeLine = trim($readmeLine);
			if (empty($readmeLine)) {
				continue;
			}

			if ($this->_isChangeLogEndLine($readmeLine)) {
				break;
			}

			$maybeVersion = null;
			if ($this->_isChangeLogVersionLine($readmeLine, $maybeVersion)) {
				if (!$this->_isCurrentlyProcessedChangeLogVersion($maybeVersion)) {
					$this->_initializeNewChangeLogVersionIfNeeded($maybeVersion);
					continue;
				}
			} else if ($this->_isProcessingChangeLogVersion()) {
				$this->_appendChangeLogVersionItem($readmeLine);
			}
		}
	}

	private function _isChangeLogEndLine($line) {
		return preg_match(self::README_END_MAKER_PATTERN, $line) === 1;
	}

	private function _isChangeLogVersionLine($readmeLine, &$version) {
		$matches = array();
		$isMatch = preg_match(self::README_VERSION_PATTERN, $readmeLine, $matches);
		if ($isMatch) {
			$version = trim($matches[1]);
		} else {
			$version = null;
		}
		return $isMatch;
	}

	private function _isCurrentlyProcessedChangeLogVersion($version) {
		return $version == $this->_currentlyProcessedVersion;
	}

	private function _initializeNewChangeLogVersionIfNeeded($version) {
		if (!isset($this->_changelog[$version])) {
			$this->_changelog[$version] = array();
		}
		$this->_currentlyProcessedVersion = $version;
	}

	private function _isProcessingChangeLogVersion() {
		return !empty($this->_currentlyProcessedVersion);
	}

	private function _appendChangeLogVersionItem($readmeLine) {
		$this->_changelog[$this->_currentlyProcessedVersion][] = $this->_normalizeChangeLogItemline($readmeLine);
	}

	private function _normalizeChangeLogItemline($readmeLine) {
		$readmeLine = ltrim($readmeLine, '*');
		$readmeLine = trim($readmeLine);
		$readmeLine = ltrim($readmeLine, '-');
		$readmeLine = trim($readmeLine);
		$readmeLine = ucfirst($readmeLine);
		return $readmeLine;
	}

	public function getExtractedChangeLog() {
		return $this->_changelog;
	}
}