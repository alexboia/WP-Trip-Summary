<?php
namespace WpTripSummary\Skills\WpCodingConventions {

    use InvalidArgumentException;

	class LicenseHeaderReader {
		private string $_sourceFile;

		public function __construct(string $sourceFile){
			if (empty($sourceFile)) {
				throw new InvalidArgumentException('Empty source file name specified.');
			}

			if (!is_readable($sourceFile)) {
				throw new InvalidArgumentException('Invalid source file specified.');
			}

			$this->_sourceFile = $sourceFile;
		}

		private function _isPhpFile(): bool {
			return stripos($this->_sourceFile, '.php') 
					!== false 
				|| stripos($this->_sourceFile, '.phtml') 
					!== false;
		}

		private function _getCommentStartIndex(string $line): int {
			$markers = ['/**', '/*'];
			foreach ($markers as $marker) {
				$index = stripos($line, $marker);
				if ($index !== false) {
					return $index;
				}
			}

			return -1;
		}

		private function _getCommentEndIndex(string $line): int {
			$index = stripos($line, '*/');
			return $index !== false ? $index : -1;
		}

		private function _hasOpeningPhpTag(string $line): bool {
			return stripos($line,'<?php') !== false
				|| stripos($line,'<?') !== false;
		}

		public function read(): ?LicenseHeader {
			$lines = file($this->_sourceFile);
			
			if ($lines === false 
				|| !is_array($lines) 
				|| empty($lines)) {
				return null;
			}

			$isPhp = $this->_isPhpFile();
			$canStartReadingLicenseHeader = $isPhp;
			$licenseHeaderLines = array();
			$maybeLicenseHeader = false;

			$cursor = 0;
			$originalOffset = -1;
			$originalLength = 0;

			foreach ($lines as $line) {
				$length = strlen($line);
				$hasOpeningPhpTag = $this->_hasOpeningPhpTag($line);

				if (!$canStartReadingLicenseHeader && $hasOpeningPhpTag) {
					$canStartReadingLicenseHeader = true;
					$cursor += $length;
					continue;
				}

				if (!$canStartReadingLicenseHeader) {
					$cursor += $length;
					continue;
				}

				$commentStartIndex = $this->_getCommentStartIndex($line);
				$isCommentStart = $commentStartIndex >= 0;

				if ($isCommentStart) {
					$initialHeaderLine = substr($line, $commentStartIndex);
					$licenseHeaderLines[] = $initialHeaderLine;

					$originalOffset = $cursor + $commentStartIndex;
					$originalLength += strlen($initialHeaderLine);

					$maybeLicenseHeader = true;

					continue;
				} else {
					$cursor += $length;
				}

				if (!$maybeLicenseHeader) {
					continue;
				}

				$endCommentIndex = $this->_getCommentEndIndex($line);
				$isCommentEnd = $endCommentIndex >= 0;

				if ($isCommentEnd) {
					$maybeLicenseHeader = false;
					$originalLength += ($endCommentIndex + 2);
					break;
				} else {
					$licenseHeaderLines[] = $line;
					$originalLength += $length;
				}
			}

			if (empty($licenseHeaderLines)) {
				return null;
			}

			$licenseHeaderYear = 0;
			$validLicenseHeader = false;
			foreach ($licenseHeaderLines as $licenseHeaderLine) {
				$matches = array();
				if (preg_match('/Copyright \(c\) 2014-([\d]{4}) ([\w+\s]+) and Contributors/i', 
					$licenseHeaderLine, 
					$matches)) {
					$licenseHeaderYear = intval($matches[1]);
					$validLicenseHeader = true;
				}
			}

			$licenseHeader = $validLicenseHeader
				? join("", $licenseHeaderLines)
				: null;

			return !empty($licenseHeader) 
				? new LicenseHeader($licenseHeader, 
					$originalOffset, 
					$originalLength,
					$licenseHeaderYear)
				: null;
		}
	}
}