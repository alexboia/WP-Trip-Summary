<?php
declare(strict_types=1);

/**
 * A value read from the Makefile, with the line it was defined on, for error messages.
 */
final readonly class ReadmeMakefileValue {
	public function __construct(
		public string $value,
		public int $line
	) {
	}
}

/**
 * The recipe of one target, as written in a [Target] block.
 * Renderers read it through typed accessors and declare which keys they accept.
 */
final class ReadmeMakefileTarget {
	public readonly string $name;

	public readonly string $filePath;

	public readonly int $line;

	/**
	 * @var array<string, ReadmeMakefileValue>
	 */
	private array $_values = array();

	/**
	 * @var array<string, array<string, ReadmeMakefileValue>>
	 */
	private array $_maps = array();

	public function __construct(string $name, string $filePath, int $line) {
		$this->name = $name;
		$this->filePath = $filePath;
		$this->line = $line;
	}

	public function setValue(string $key, string $value, int $line): void {
		if (isset($this->_values[$key]) || isset($this->_maps[$key])) {
			throw $this->errorAtLine(
				$line, 
				sprintf('"%s" is already defined; use += to append to it', 
					$key)
			);
		}
		$this->_values[$key] = new ReadmeMakefileValue($value, $line);
	}

	public function appendValue(string $key, string $value, int $line): void {
		if (isset($this->_maps[$key])) {
			throw $this->errorAtLine(
				$line, 
				sprintf('"%s" is a map and cannot be appended to', 
					$key)
			);
		}

		if (isset($this->_values[$key])) {
			$existing = $this->_values[$key];
			$this->_values[$key] = new ReadmeMakefileValue($existing->value . ', ' . $value, 
				$existing->line);
		} else {
			$this->_values[$key] = new ReadmeMakefileValue($value, 
				$line);
		}
	}

	public function setMapEntry(string $key, string $entryName, string $value, int $line): void {
		if (isset($this->_values[$key])) {
			throw $this->errorAtLine(
				$line, 
				sprintf('"%s" is already defined as a value, not as a map', 
					$key)
			);
		}

		if (isset($this->_maps[$key][$entryName])) {
			throw $this->errorAtLine(
				$line, 
				sprintf('"%s[%s]" is already defined', 
					$key, 
					$entryName)
			);
		}

		$this->_maps[$key][$entryName] = new ReadmeMakefileValue($value, 
			$line);
	}

	public function requireValue(string $key): ReadmeMakefileValue {
		if (!isset($this->_values[$key]) || trim($this->_values[$key]->value) === '') {
			throw $this->errorAtLine(
				$this->line, 
				sprintf('"%s" is required', $key)
			);
		}
		return $this->_values[$key];
	}

	public function optionalValue(string $key): ?ReadmeMakefileValue {
		return $this->_values[$key] ?? null;
	}

	/**
	 * @param string[] $allowed
	 */
	public function requireChoice(string $key, array $allowed, ?string $default = null): string {
		$value = $this->_values[$key] ?? null;
		if ($value === null && $default !== null) {
			return $default;
		}

		$choice = $value === null
			? ''
			: trim($value->value);

		if (!in_array($choice, $allowed, true)) {
			throw $this->errorAtLine(
				$value?->line ?? $this->line,
				sprintf('"%s" must be one of: %s', 
					$key, 
					implode(', ', $allowed))
			);
		}

		return $choice;
	}

	/**
	 * @return array<string, ReadmeMakefileValue> Entries in definition order
	 */
	public function map(string $key): array {
		return $this->_maps[$key] ?? array();
	}

	/**
	 * @param string[] $allowedValueKeys
	 * @param string[] $allowedMapKeys
	 */
	public function assertKnownKeys(array $allowedValueKeys, array $allowedMapKeys): void {
		foreach ($this->_values as $key => $value) {
			if (!in_array($key, $allowedValueKeys, true)) {
				throw $this->errorAtLine(
					$value->line, 
					sprintf('"%s" is not supported by this target format', 
						$key)
				);
			}
		}

		foreach ($this->_maps as $key => $entries) {
			if (!in_array($key, $allowedMapKeys, true)) {
				throw $this->errorAtLine(
					reset($entries)->line,
					sprintf('"%s[...]" is not supported by this target format', 
						$key)
				);
			}
		}
	}

	public function errorAtLine(int $line, string $problem): ReadmeBuildException {
		return new ReadmeBuildException(
			sprintf('%s:%d: [%s] %s.', 
				$this->filePath, 
				$line, 
				$this->name, 
				$problem)
		);
	}
}

/**
 * Parses readme/Makefile into targets. The syntax is documented at the top of the Makefile.
 */
final readonly class ReadmeMakefile {
	const TARGET_PATTERN = '/^\[([A-Za-z][A-Za-z0-9_-]*)\]$/';

	const ASSIGNMENT_PATTERN = '/^([a-z][a-z0-9-]*)(?:\[([^\]]+)\])?\s*(\+?=)\s*(.*)$/';

	/**
	 * @param array<string, ReadmeMakefileTarget> $targets Targets in definition order, keyed by name
	 */
	public function __construct(public array $targets) {
		return;
	}

	public static function fromFile(string $filePath): self {
		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new ReadmeBuildException(
				sprintf('Cannot read Makefile "%s".', $filePath)
			);
		}

		$targets = array();
		$currentTarget = null;
		$logicalLines = self::_readLogicalLines($filePath);

		foreach ($logicalLines as $lineNumber => $line) {
			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}

			//Is this a [Target] line?
			if (preg_match(self::TARGET_PATTERN, $line, $matches) === 1) {
				$name = $matches[1];
				if (self::_findTargetName($targets, $name) !== null) {
					throw new ReadmeBuildException(
						sprintf('%s:%d: target [%s] is already defined.', 
							$filePath, 
							$lineNumber, 
							$name)
					);
				}

				$currentTarget = new ReadmeMakefileTarget($name, $filePath, $lineNumber);
				$targets[$name] = $currentTarget;

			//Or is this an assignment line?
			} else if (preg_match(self::ASSIGNMENT_PATTERN, $line, $matches) === 1) {
				//No un-targeted assignments, if you would
				if ($currentTarget === null) {
					throw new ReadmeBuildException(
						sprintf('%s:%d: assignment outside a [Target] block.', 
							$filePath, 
							$lineNumber)
					);
				}

				self::_assign($currentTarget, 
					$matches, 
					$lineNumber);
			} else {
				throw new ReadmeBuildException(
					sprintf('%s:%d: cannot parse "%s".', 
						$filePath, 
						$lineNumber, 
						$line)
				);
			}
		}

		if (empty($targets)) {
			throw new ReadmeBuildException(sprintf('No targets defined in "%s".', $filePath));
		}

		return new self($targets);
	}

	/**
	 * Joins continued lines and trims them.
	 *
	 * @return array<int, string> Logical lines keyed by the number of their first physical line
	 */
	private static function _readLogicalLines(string $filePath): array {
		$logicalLines = array();
		$pending = null;
		$pendingLineNumber = 0;

		$contents = (string)file_get_contents($filePath);
		foreach (ReadmeText::splitLines($contents) as $index => $physicalLine) {
			$line = trim($physicalLine);
			if ($pending === null) {
				$pendingLineNumber = $index + 1;
				$pending = '';
			} else if ($line !== '') {
				$pending .= ' ';
			}

			if (str_ends_with($line, '\\')) {
				$pending .= rtrim(substr($line, 0, -1));
			} else {
				$logicalLines[$pendingLineNumber] = $pending . $line;
				$pending = null;
			}
		}

		if ($pending !== null) {
			$logicalLines[$pendingLineNumber] = $pending;
		}

		return $logicalLines;
	}

	private static function _assign(ReadmeMakefileTarget $target, array $matches, int $lineNumber): void {
		list(, $key, $entryName, $operator, $value) = $matches;
		$value = trim($value);

		if ($entryName !== '') {
			if ($operator !== '=') {
				throw $target->errorAtLine(
					$lineNumber, 
					'map entries cannot be appended to'
				);
			}
			$target->setMapEntry(
				$key, 
				trim($entryName), 
				$value, 
				$lineNumber
			);
		} else if ($operator === '+=') {
			$target->appendValue($key, $value, $lineNumber);
		} else {
			$target->setValue($key, $value, $lineNumber);
		}
	}

	/**
	 * @param array<string, ReadmeMakefileTarget> $targets
	 */
	private static function _findTargetName(array $targets, string $name): ?string {
		foreach (array_keys($targets) as $targetName) {
			if (strcasecmp($targetName, $name) === 0) {
				return $targetName;
			}
		}
		return null;
	}

	/**
	 * Finds a target by name, ignoring case.
	 */
	public function getTarget(string $name): ReadmeMakefileTarget {
		$targetName = self::_findTargetName($this->targets, $name);
		if ($targetName === null) {
			throw new ReadmeBuildException(
				sprintf('Unknown target "%s". Available targets: %s.',
					$name,
					implode(', ', array_keys($this->targets))
			));
		}
		return $this->targets[$targetName];
	}
}
