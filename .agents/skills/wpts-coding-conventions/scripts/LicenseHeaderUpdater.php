<?php
namespace WpTripSummary\Skills\WpCodingConventions {
	use InvalidArgumentException;
	use RuntimeException;

	class LicenseHeaderUpdater {
		private string $_sourceFile;

		private string $_templateFile;

		public function __construct(string $sourceFile) {
			if (!is_file($sourceFile) || !is_readable($sourceFile)) {
				throw new InvalidArgumentException('Invalid or unreadable source file: ' . $sourceFile);
			}

			$extension = strtolower(pathinfo($sourceFile, PATHINFO_EXTENSION));
			if (!in_array($extension, array('php', 'phtml', 'js', 'ts', 'css'), true)) {
				throw new InvalidArgumentException('Unsupported source file extension: ' . $extension);
			}

			$this->_sourceFile = $sourceFile;
			$this->_templateFile = __DIR__ . '/../references/.license-header';
		}

		public function update(int $toYear): bool {
			$contents = $this->_readSource();
			$updated = $this->preview($toYear);

			if ($updated === $contents) {
				return false;
			}

			if (@file_put_contents($this->_sourceFile, $updated) 
					!== strlen($updated)) {
				throw new RuntimeException('Could not write source file: ' 
					. $this->_sourceFile);
			}

			return true;
		}

		private function _getCurrentHeader(): ?LicenseHeader {
			$reader = new LicenseHeaderReader($this->_sourceFile);
			return $reader->read();
		}

		private function _generateHeaderText(int $toYear, string $newLine): string {
			$generator = new LicenseHeaderGenerator($this->_templateFile);
			
			$header = $generator->generate($toYear);
			$header = str_replace("\r\n", "\n", 
				$header);
			$header = str_replace("\n", $newLine, 
				$header);

			return $header;
		}

		private function _determineNewLine(string $contents) {
			return str_contains($contents, "\r\n") ? "\r\n" : "\n";
		}

		private function _isPhpFile(): bool {
			$extension = strtolower(pathinfo($this->_sourceFile, 
				PATHINFO_EXTENSION));

			$isPhpFile = in_array($extension, 
				array('php', 'phtml'),
				true);

			return $isPhpFile;
		}

		public function preview(int $toYear): string {
			if ($toYear < 2014 || $toYear > 9999) {
				throw new InvalidArgumentException('License year must be between 2014 and 9999.');
			}

			$contents = $this->_readSource();
			$currentHeader = $this->_getCurrentHeader();

			$newLine = $this->_determineNewLine($contents);
			$headerText = $this->_generateHeaderText($toYear, $newLine);

			if ($currentHeader !== null) {
				return substr_replace($contents, 
					$headerText, 
					$currentHeader->offset, 
					$currentHeader->length);
			}

			$isPhpFile = $this->_isPhpFile();
			if (!$isPhpFile) {
				return $headerText . $newLine . $contents;
			}

			$offset = 0;
			foreach (token_get_all($contents) as $token) {
				$text = is_array($token) 
					? $token[1] 
					: $token;

				if (is_array($token) && $token[0] === T_OPEN_TAG) {
					$offset += strlen(rtrim($text));
					$remainder = substr($contents, $offset);
					$separator = $this->_remainderNeedsNewLine($remainder)
						? $newLine
						: "";

					return substr($contents, 0, $offset) 
						. $newLine 
						. $headerText 
						. $separator 
						. $remainder;
				}
				$offset += strlen($text);
			}

			//A PHP template containing only markup needs a separate PHP comment block.
			return '<?php' 
					. $newLine 
					. $headerText 
					. $newLine 
				. '?>' 
				. $contents;
		}

		private function _readSource(): string {
			$contents = @file_get_contents($this->_sourceFile);
			if ($contents === false) {
				throw new RuntimeException('Could not read source file: ' . $this->_sourceFile);
			}
			return $contents;
		}

		private function _remainderNeedsNewLine(string $remainder):bool {
			return !str_starts_with($remainder, "\n") 
				&& !str_starts_with($remainder, "\r\n");
		}
	}
}