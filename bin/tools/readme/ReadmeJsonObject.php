<?php
declare(strict_types=1);

/**
 * Typed, strict access to a decoded JSON object.
 * Every key must be read exactly by the consumer; anything else is reported by assertNoUnknownKeys().
 */
final class ReadmeJsonObject {
	const VARIABLE_NAME_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';

	private array $_values;

	private string $_path;

	private array $_readKeys = array();

	public function __construct(array $values, string $path) {
		$this->_values = $values;
		$this->_path = $path;
	}

	public static function fromFile(string $filePath, string $path): self {
		if (!is_file($filePath) 
			|| !is_readable($filePath)) {
			throw new ReadmeBuildException(
				sprintf('Cannot read "%s".', $filePath)
			);
		}

		try {
			$values = json_decode((string)file_get_contents($filePath), 
				true, 
				64, 
				JSON_THROW_ON_ERROR);
		} catch (JsonException $exc) {
			throw new ReadmeBuildException(
				sprintf('Invalid JSON in "%s": %s.', 
					$filePath, 
					$exc->getMessage()), 
				0, 
				$exc
			);
		}

		if (!self::_isObject($values)) {
			throw new ReadmeBuildException(
				sprintf('"%s" must contain a JSON object.', $filePath)
			);
		}

		return new self($values, $path);
	}

	private static function _isObject(mixed $value): bool {
		return is_array($value)
			&& (empty($value) || !array_is_list($value));
	}

	public function requireString(string $key): string {
		$value = $this->_require($key);
		if (!is_string($value) || trim($value) === '') {
			throw $this->_error(
				$key, 
				'must be a non-empty string'
			);
		}
		return $value;
	}

	/**
	 * Reads a path relative to the root directory and returns it resolved.
	 */
	public function requirePath(string $key, string $rootDirectory): string {
		$relativePath = str_replace('\\', 
			'/', 
			$this->requireString($key));

		if (preg_match('#^([A-Za-z]:)?/#', $relativePath) === 1 
			|| str_contains($relativePath, '..')) {
			throw $this->_error(
				$key, 
				'must be a path relative to the plugin root, without ".."'
			);
		}

		return rtrim($rootDirectory, '/\\') . '/' 
			. trim($relativePath, '/');
	}

	public function requireObject(string $key): self {
		$value = $this->_require($key);
		if (!self::_isObject($value)) {
			throw $this->_error(
				$key, 
				'must be an object'
			);
		}
		return new self($value, $this->_childPath($key));
	}

	/**
	 * @return array<string, string> Variable names mapped to string values
	 */
	public function requireVariableMap(string $key): array {
		$map = $this->requireObject($key);
		$values = array();

		foreach ($map->keys() as $name) {
			if (preg_match(self::VARIABLE_NAME_PATTERN, $name) !== 1) {
				throw $map->_error(
					$name, 
					'is not a valid variable name (letters, digits and _, starting with a letter)'
				);
			}
			$values[$name] = $map->requireString($name);
		}

		return $values;
	}

	/**
	 * @return string[]
	 */
	public function keys(): array {
		return array_map('strval', array_keys($this->_values));
	}

	public function assertNoUnknownKeys(): void {
		foreach ($this->keys() as $key) {
			if (!in_array($key, $this->_readKeys, true)) {
				throw $this->_error($key, 'is not a known key');
			}
		}
	}

	private function _require(string $key): mixed {
		if (!array_key_exists($key, $this->_values)) {
			throw $this->_error($key, 'is required');
		}
		$this->_readKeys[] = $key;
		return $this->_values[$key];
	}

	private function _childPath(string $key): string {
		return $this->_path . '.' . $key;
	}

	private function _error(string $key, string $problem): ReadmeBuildException {
		return new ReadmeBuildException(
			sprintf('Manifest: "%s" %s.', 
				$this->_childPath($key), 
				$problem)
		);
	}
}
