<?php
namespace WpTripSummary\Skills\WpCodingConventions {

    use InvalidArgumentException;

	class LicenseHeaderUpdater {
		private string $_sourceFile;
		
		public function __construct(string $sourceFile) {
			if (empty($sourceFile)) {
				throw new InvalidArgumentException('Empty source file name specified.');
			}

			if (!is_readable($sourceFile)) {
				throw new InvalidArgumentException('Invalid source file specified.');
			}

			$this->_sourceFile = $sourceFile;
		}

		public function update(int $toYear) {
			$currentHeader = $this->_getCurrentLicenseHeader();
			$fileContents = file_get_contents($this->_sourceFile);

			$strip = 0;
			$insertOffset = -1;
			$insertContents = array();
			$isPhpFile = $this->_isPhpFile();
			$beginNewLineCount = 0;
			$endingNewLineCount = 1;
			$needsPHPTags = false;
			
			if ($currentHeader === null) {
				$context = array();
				$insertOffset = $this->_getInsertOffset($fileContents, 
					$isPhpFile,
					$context);

				//No opening PHP tag
				if ($isPhpFile) {
					if ($insertOffset === 0) {
						$needsPHPTags = true;
						$beginNewLineCount = 1;
					} else if(!$context['cleanOpenMarker']) {
						$beginNewLineCount = 2;
					}
				}
			} else {
				$insertOffset = $currentHeader->offset;
				$strip = $currentHeader->length;
			}

			$beginNewLines = $beginNewLineCount > 0 
				? str_repeat("\n", $beginNewLineCount) 
				: "";

			$endingNewLines = $endingNewLineCount > 0 
				? str_repeat("\n", $endingNewLineCount)
				: "";

			if ($needsPHPTags) {
				$insertContents[] = "<?php$beginNewLines";
			} else {
				$insertContents[] = $beginNewLines;
			}

			$insertContents[] = $this->_generateLicenseHeader($toYear);
			$insertContents[] = $endingNewLines;
			if ($needsPHPTags) {
				$insertContents[] = "?>\n";
			}

			$finalContents = ""; 
			if ($insertOffset > 0) {
				$finalContents = substr($fileContents, 
				0, 
				$insertOffset);	
			}

			$finalContents .= join("", $insertContents);
			$finalContents .= substr($fileContents, 
					$insertOffset + $strip);

			file_put_contents($this->_sourceFile, 
				$finalContents);
		}

		private function _getCurrentLicenseHeader(): ?LicenseHeader {
			$reader = new LicenseHeaderReader($this->_sourceFile);
			return $reader->read();
		}

		private function _getInsertOffset(string $contents, bool $isPhpFile, array &$context): int {
			if (!$isPhpFile) {
				return 0;
			}

			$offset = -1;
			$searchMarkers = ['<?php', '<?'];

			foreach ($searchMarkers as $marker) {
				$index = stripos($contents, $marker);
				if ($index !== false) {
					$offset = ($index + strlen($marker));
					break;
				}
			}

			$context['cleanOpenMarker'] = true;
			if ($offset < 0) {
				return 0;
			}

			$cursor = $offset;
			$length = strlen($contents);

			while ($cursor < $length) {
				if ($contents[$cursor] !== "\n") {
					$context['cleanOpenMarker'] = false;
				}
				$cursor ++;
			}

			return $offset;
		}

		private function _isPhpFile(): bool {
			return stripos($this->_sourceFile, '.php') 
					!== false 
				|| stripos($this->_sourceFile, '.phtml') 
					!== false;
		}

		private function _generateLicenseHeader(int $forYear): string {
			$template = realpath(__DIR__ . '/../references/.license-header');
			$generator = new LicenseHeaderGenerator($template);
			return $generator->generate($forYear);
		}
	}
}