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

class Abp01_Io_FileInfo {
	private $_filePath;

	/**
	 * @var SplFileInfo
	 */
	private $_fileInfo;

	public function __construct(string $filePath) {
		$this->_filePath = $filePath;
	}

	public function id(): string {
		return sha1($this->_filePath);
	}

	public function matchesId(string $id): bool {
		return $id === $this->id();
	}

	public function getFileSize(): int|bool {
		return $this->_getFileInfo()->getSize();
	}

	public function contents(): string|null {
		if (!$this->exists()) {
			return null;
		}

		$fileObject = $this->_open();
		$contents = $fileObject->fread($fileObject->getSize());

		unset($fileObject);
		$fileObject = null;

		if ($contents !== false) {
			return $contents;
		} else {
			return null;
		}
	}

	public function tail($nLines = 100): string|null {
		if (!$this->exists()) {
			return null;
		}

		if ($this->getFileSize() == 0) {
			return '';
		}

		$tailContents = '';
		$currentLineCount = 0;

		$fileObject = $this->_open();
		$fileObject->fseek(0, SEEK_END);

		while ($currentLineCount < $nLines && $fileObject->ftell() > 0) {
			$pFtell = $fileObject->ftell();
			$seekCount = min($pFtell, 512);
			$fileObject->fseek(-$seekCount, SEEK_CUR);

			$readCount = $seekCount;
			$loopContents = $fileObject->fread($readCount);

			if ($loopContents === false) {
				break;
			}

			$loopLen = function_exists('mb_strlen')
				? mb_strlen($loopContents)
				: strlen($loopContents);

			$tailContents = $loopContents . $tailContents;
			$currentLineCount += substr_count($loopContents, "\n");

			if ($loopLen < 512) {
				break;
			}

			$fileObject->fseek(-$loopLen, SEEK_CUR);
		}

		unset($fileObject);
		$fileObject = null;

		$tailLines = explode("\n", $tailContents);
		$actualTailLineCount = count($tailLines);

		if (count($tailLines) > $nLines) {
			$tailLines = array_slice($tailLines, $actualTailLineCount - $nLines);
			$tailContents = join("\n", $tailLines);
		}		

		return $tailContents;
	}

	private function _open(): SplFileObject {
		return new SplFileObject($this->_filePath, 'rb');
	}

	public function getFormattedFileSize(): string|null {
		$sizeBytes = $this->getFileSize();
		if ($sizeBytes === false) {
			return null;
		}

		return Abp01_ConversionHelper::getByteSizeDescription($sizeBytes);
	}

	public function getLastModified(): int|bool {
		return $this->_getFileInfo()->getCTime();
	}

	private function _getFileInfo(): SplFileInfo {
		if ($this->_fileInfo === null) {
			$this->_fileInfo = new SplFileInfo($this->_filePath);
		}

		return $this->_fileInfo;
	}

	public function getFileName(): string {
		return basename($this->_filePath);
	}

	public function getFilePath(): string {
		return $this->_filePath;
	}

	public function exists(): bool {
		return is_readable($this->_filePath) 
			&& is_file($this->_filePath);
	}
}