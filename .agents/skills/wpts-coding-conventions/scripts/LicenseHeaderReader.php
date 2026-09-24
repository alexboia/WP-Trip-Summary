<?php
namespace WpTripSummary\Skills\WpCodingConventions {

    use InvalidArgumentException;

	class LicenseHeaderReader {
		private string $_sourceFile;

		public function __construct(string $sourceFile){
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

		private function _isCommentStartLine(string $cleanLine): bool {  
			$startsWithSlashTwo = str_starts_with($cleanLine, '/**');
			$startsWithSlashOne = str_starts_with($cleanLine, '/*');

			$isCommentStart = $startsWithSlashOne || $startsWithSlashTwo;
			return $isCommentStart;
		}

		private function _isCommentEndLine(string $cleanLine): bool {
			return str_ends_with($cleanLine, '*/');
		}

		private function _hasOpeningPhpTag(string $cleanLine): bool {
			return stripos($cleanLine,'<?php') !== false
				|| stripos($cleanLine,'<?') !== false;
		}

		public function read(): ?string {
			$lines = file($this->_sourceFile);
			
			if ($lines === false || !is_array($lines) || empty($lines)) {
				return null;
			}

			$isPhp = $this->_isPhpFile();
			$canStartReadingLicenseHeader = $isPhp;
			$licenseHeaderLines = array();
			$maybeLicenseHeader = false;

			foreach ($lines as $line) {
				$cleanLine = trim($line);
				$hasOpeningPhpTag = $this->_hasOpeningPhpTag($cleanLine);

				if (!$canStartReadingLicenseHeader && $hasOpeningPhpTag) {
					$canStartReadingLicenseHeader = true;
					continue;
				}

				if (!$canStartReadingLicenseHeader) {
					continue;
				}

				$isCommentStart = $this->_isCommentStartLine($cleanLine);
				if ($isCommentStart) {
					$licenseHeaderLines[] = $cleanLine;
					$maybeLicenseHeader = true;
					continue;
				}

				if (!$maybeLicenseHeader) {
					continue;
				}

				$licenseHeaderLines[] = $cleanLine;

				$isCommentEnd = $this->_isCommentEndLine($cleanLine);
				if ($isCommentEnd) {
					$maybeLicenseHeader = false;
					break;
				}
			}

			$validLicenseHeader = false;
			foreach ($licenseHeaderLines as $licenseHeaderLine) {
				if (preg_match('/Copyright \(c\) 2014-([\d]{4}) ((\w+)\s*)+ and Contributors/i', $licenseHeaderLine)) {
					$validLicenseHeader = true;
				}
			}

			return $validLicenseHeader
				? join("\n", $licenseHeaderLines)
				: null;
		}
	}
}