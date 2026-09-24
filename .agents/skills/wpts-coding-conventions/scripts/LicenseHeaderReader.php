<?php
namespace WpTripSummary\Skills\WpCodingConventions {
	use InvalidArgumentException;
	use RuntimeException;

	class LicenseHeaderReader {
		private string $_sourceFile;

		public function __construct(string $sourceFile) {
			if (!is_file($sourceFile) || !is_readable($sourceFile)) {
				throw new InvalidArgumentException('Invalid or unreadable source file: ' . $sourceFile);
			}

			$this->_sourceFile = $sourceFile;
		}

		private function _isPhpFile(): bool {
			$extension = strtolower(pathinfo($this->_sourceFile, 
				PATHINFO_EXTENSION));

			$isPhpFile = in_array($extension, 
				array('php', 'phtml'),
				true);

			return $isPhpFile;
		}

		public function read(): ?LicenseHeader {
			$contents = @file_get_contents($this->_sourceFile);
			if ($contents === false) {
				throw new RuntimeException('Could not read source file: ' . $this->_sourceFile);
			}

			$isPhpFile = $this->_isPhpFile();
			if ($isPhpFile) {
				$offset = 0;
				$commentTokens = array(\T_COMMENT, \T_DOC_COMMENT);

				foreach (token_get_all($contents) as $token) {
					$text = is_array($token) ? $token[1] : $token;
					if (is_array($token) 
						&& in_array($token[0], 
							$commentTokens, 
							true)) {
						$header = $this->_readComment($text, 
							$offset);

						if ($header !== null) {
							return $header;
						}
					}

					$offset += strlen($text);
				}
			} else {
				preg_match_all('~/\*.*?\*/~s', 
					$contents, 
					$comments, 
					PREG_OFFSET_CAPTURE);

				foreach ($comments[0] as [$comment, $offset]) {
					$header = $this->_readComment($comment, 
						$offset);

					if ($header !== null) {
						return $header;
					}
				}
			}

			return null;
		}

		private function _readComment(string $comment, int $offset): ?LicenseHeader {
			if (!str_starts_with($comment, '/*') 
				|| !str_ends_with($comment, '*/')) {
				return null;
			}

			if (!preg_match(LicenseHeader::COPYRIGHT_PATTERN, 
				$comment, 
				$matches)) {
				return null;
			}

			return new LicenseHeader($comment, 
				$offset, 
				strlen($comment), 
				intval($matches[2]));
		}
	}
}