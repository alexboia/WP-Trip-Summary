<?php

namespace WpTripSummary\Skills\WpDocumentHooks {
	class HookDocParser {
		private const int IN_DESCRIPTION = 0;
		private const int IN_SEE = 1;
		private const int IN_UNSTABLE = 2;
		private const int IN_PARAM = 3;
		private const int IN_CATEGORY = 4;
		private const int IN_SINCE = 5;
		private const int IN_IGNORED_TAG = 6;

		private string $_name;

		private string $_hookDocComment;

		private array $_see = array();

		private ?string $_since = null;

		private array $_unstable = array();

		private array $_parameters = array();

		private array $_currentUnstableLines = array();

		private array $_currentSeeLines = array();

		private array $_descriptionLines = array();

		private array $_categoryLines = array();

		private array $_currentParameter;

		private int $_state = self::IN_DESCRIPTION;

		private int $_currentParameterIndex = -1;

		private ?HookDoc $_result = null;

		public function __construct(string $name, string $hookDocComment){
			$this->_name = trim($name);
			$this->_hookDocComment = $hookDocComment;
			$this->_currentParameter = $this->_emptyParameter();
		}

		public function parse(): ?HookDoc {
			if ($this->_name === '' || trim($this->_hookDocComment) === '') {
				return null;
			}

			if ($this->_result !== null) {
				return $this->_result;
			}

			$lines = preg_split('/\R/u', $this->_hookDocComment);
			if ($lines === false) {
				$lines = array($this->_hookDocComment);
			}

			foreach ($lines as $line) {
				$cleanLine = $this->_cleanLine($line);
				if ($cleanLine === '') {
					continue;
				}

				$this->_maybeCategoryLine($cleanLine);
				$this->_maybeSinceLine($cleanLine);
				$this->_maybeSeeLine($cleanLine);
				$this->_maybeUnstableLine($cleanLine);
				$this->_maybeParameterLine($cleanLine);
				$this->_maybeUnknownTagLine($cleanLine);
				$this->_maybeUntaggedLine($cleanLine);
			}

			$this->_commitCurrent();

			$description = !empty($this->_descriptionLines)
				? join(' ', $this->_descriptionLines)
				: '';

			$category = !empty($this->_categoryLines)
				? join(' ', $this->_categoryLines)
				: null;

			$this->_result = new HookDoc($this->_name,
				$description,
				$category,
				$this->_since,
				$this->_unstable,
				$this->_see,
				$this->_parameters);

			return $this->_result;
		}

		private function _emptyParameter(): array {
			return array(
				'name' => null,
				'type' => null,
				'byReference' => false,
				'variadic' => false,
				'descriptionLines' => array()
			);
		}

		private function _commitCurrent(): void {
			switch ($this->_state) {
				case self::IN_DESCRIPTION:
				//Do nothing, we will collect it upon build
					break;
				case self::IN_SEE:
					if (!empty($this->_currentSeeLines)) {
						$this->_see[] = join(' ', $this->_currentSeeLines);
					}
					$this->_currentSeeLines = array();
					break;
				case self::IN_CATEGORY:
				//Do nothing, we will collect it upon build
					break;
				case self::IN_SINCE:
				//Do nothing, we will collect it upon build
					break;
				case self::IN_UNSTABLE:
					if (!empty($this->_currentUnstableLines)) {
						$this->_unstable[] = join(' ', $this->_currentUnstableLines);
					}
					$this->_currentUnstableLines = array();
					break;
				case self::IN_PARAM:
					if (!empty($this->_currentParameter['name'])
						&& !empty($this->_currentParameter['type'])) {
						$this->_parameters[] = array(
							'name' => $this->_currentParameter['name'],
							'type' => $this->_currentParameter['type'],
							'byReference' => $this->_currentParameter['byReference'],
							'variadic' => $this->_currentParameter['variadic'],
							'description' => join(' ', $this->_currentParameter['descriptionLines'])
						);
					}
					$this->_currentParameter = $this->_emptyParameter();
					break;
			}
		}

		private function _maybeCategoryLine(string $cleanLine): void {
			if ($this->_isTagLine($cleanLine, 'category')) {
				//Tag always changes context
				$this->_commitCurrent();
				$this->_categoryLines = array();
				$this->_state = self::IN_CATEGORY;

				$categoryLine = trim(substr($cleanLine, strlen('@category')));
				if (!empty($categoryLine)) {
					$this->_categoryLines[] = $categoryLine;
				}
			}
		}

		private function _maybeSinceLine(string $cleanLine): void {
			if ($this->_isTagLine($cleanLine, 'since')) {
				$this->_commitCurrent();
				$this->_state = self::IN_SINCE;
				$sinceLine = trim(substr($cleanLine, strlen('@since')));
				if (!empty($sinceLine)) {
					$this->_since = $sinceLine;
				}
			}
		}

		private function _maybeSeeLine(string $cleanLine): void {
			if ($this->_isTagLine($cleanLine, 'see')) {
				$this->_commitCurrent();
				$this->_currentSeeLines = array();
				$this->_state = self::IN_SEE;

				$seeLine = trim(substr($cleanLine, strlen('@see')));
				if (!empty($seeLine)) {
					$this->_currentSeeLines[] = $seeLine;
				}
			}
		}

		private function _maybeUnstableLine(string $cleanLine): void {
			if ($this->_isTagLine($cleanLine, 'unstable')) {
				$this->_commitCurrent();
				$this->_currentUnstableLines = array();
				$this->_state = self::IN_UNSTABLE;

				$unstableLine = trim(substr($cleanLine, strlen('@unstable')));
				if (!empty($unstableLine)) {
					$this->_currentUnstableLines[] = $unstableLine;
				}
			}
		}

		private function _maybeParameterLine(string $cleanLine): void {
			if ($this->_isTagLine($cleanLine, 'param')) {
				$this->_commitCurrent();
				$this->_currentParameter = $this->_emptyParameter();
				$this->_state = self::IN_PARAM;
				$this->_currentParameterIndex++;

				$type = 'mixed';
				$name = $this->_anonParamName();
				$description = '';
				$paramLine = trim(substr($cleanLine, strlen('@param')));

				if (!empty($paramLine)) {
					$nameMatched = preg_match(
						'/\$[a-z_\x80-\xff][a-z0-9_\x80-\xff]*/i',
						$paramLine,
						$nameMatch,
						PREG_OFFSET_CAPTURE
					);

					if ($nameMatched === 1) {
						$name = $nameMatch[0][0];
						$nameOffset = $nameMatch[0][1];

						$parameterDeclaration = $this->_parseParameterDeclaration(
							substr($paramLine, 0, $nameOffset)
						);

						if (!empty($parameterDeclaration['type'])) {
							$type = $parameterDeclaration['type'];
						}

						$this->_currentParameter['byReference'] = 
							$parameterDeclaration['byReference'];
						$this->_currentParameter['variadic'] = 
							$parameterDeclaration['variadic'];

						$description = trim(substr(
							$paramLine,
							$nameOffset + strlen($name)
						));
					} else {
						$parts = preg_split('/\s+/', $paramLine, 2);
						if ($parts !== false && $parts[0] !== '') {
							$type = $parts[0];
							$description = isset($parts[1]) ? trim($parts[1]) : '';
						}
					}
				}

				$this->_currentParameter['name'] = $name;
				$this->_currentParameter['type'] = $type;
				if ($description !== '') {
					$this->_currentParameter['descriptionLines'][] = $description;
				}
			}
		}

		/**
		 * Separates the PHPDoc type from the argument-passing markers before a variable.
		 *
		 * The suffix is consumed right-to-left because PHP places the reference marker
		 * before the variadic marker (`&...$values`). Removing only terminal markers
		 * also preserves ampersands that belong to intersection types such as `Foo&Bar`.
		 *
		 * @return array{type: string, byReference: bool, variadic: bool}
		 */
		private function _parseParameterDeclaration(string $declaration): array {
			$type = trim($declaration);
			$variadic = preg_match('/\.\.\.\s*$/', $type) === 1;
			if ($variadic) {
				$type = preg_replace('/\.\.\.\s*$/', '', $type) ?? $type;
			}

			$byReference = preg_match('/&\s*$/', $type) === 1;
			if ($byReference) {
				$type = preg_replace('/&\s*$/', '', $type) ?? $type;
			}

			return array(
				'type' => trim($type),
				'byReference' => $byReference,
				'variadic' => $variadic
			);
		}

		private function _anonParamName(): string {
			return sprintf('$__%d', $this->_currentParameterIndex);
		}

		private function _isTagLine(string $cleanLine, string $tag): bool {
			return preg_match(
				'/^@' . preg_quote($tag, '/') . '(?:\s|$)/i',
				$cleanLine
			) === 1;
		}

		private function _maybeUnknownTagLine(string $cleanLine): void {
			//Unknown tag, commit previous, mark current state 
			//	as ignoring whatever this is
			if (str_starts_with($cleanLine, '@')
				&& preg_match('/^@(category|since|see|unstable|param)(?:\s|$)/i', $cleanLine) !== 1) {
				$this->_commitCurrent();
				$this->_state = self::IN_IGNORED_TAG;
			}
		}

		private function _maybeUntaggedLine(string $cleanLine): void {
			if (strpos($cleanLine, '@') !== 0) {
				switch ($this->_state) {
					case self::IN_DESCRIPTION:
						$this->_descriptionLines[] = $cleanLine;
						break;
					case self::IN_SEE:
						$this->_currentSeeLines[] = $cleanLine;
						break;
					case self::IN_CATEGORY:
						$this->_categoryLines[] = $cleanLine;
						break;
					case self::IN_UNSTABLE:
						$this->_currentUnstableLines[] = $cleanLine;
						break;
					case self::IN_PARAM:
						$this->_currentParameter['descriptionLines'][] = $cleanLine;
						break;
					case self::IN_SINCE:
						//Deliberately ignored,
						//	should only be a version identifier,
						//	no multiple lines required
						break;
				}
			}
		}

		private function _cleanLine(string $line): string {
			$processLine = trim($line);
			if ($processLine === '') {
				return '';
			}

			//Allow for flexible open markers
			$startsWithSlashTwo = str_starts_with($processLine, '/**');
			$startsWithSlashOne = str_starts_with($processLine, '/*');

			$hasOpeningMarker = $startsWithSlashTwo
				|| $startsWithSlashOne;

			if ($hasOpeningMarker) {
				$markerLength = $startsWithSlashTwo ? 3 : 2;
				$processLine = ltrim(substr($processLine, 
					$markerLength));
			}

			//Remove close marker
			if (str_ends_with($processLine, '*/')) {
				$processLine = rtrim(substr($processLine, 0, -2));
			}

			if (!$hasOpeningMarker) {
				if (!str_starts_with($processLine, '*')) {
					return '';
				}
				$processLine = ltrim(substr($processLine, 1));
			}

			return trim($processLine);
		}
	}
}
