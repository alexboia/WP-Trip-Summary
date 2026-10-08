<?php
declare(strict_types=1);

/**
 * An entry of a section list: a fragment name, @changelog[:N] or @screenshots.
 */
final readonly class ReadmeSectionItem {
	const KIND_FRAGMENT = 'fragment';

	const KIND_CHANGELOG = 'changelog';

	const KIND_SCREENSHOTS = 'screenshots';

	public function __construct(
		public string $kind,
		public string $name,
		public ?int $count = null
	) {
	}

	public static function parse(string $item): ?self {
		if (preg_match('/^@changelog(?::([1-9][0-9]*))?$/', $item, $matches) === 1) {
			return new self(self::KIND_CHANGELOG, $item, isset($matches[1]) ? (int)$matches[1] : null);
		} else if ($item === '@screenshots') {
			return new self(self::KIND_SCREENSHOTS, $item);
		} else if (preg_match('/^[a-z0-9][a-z0-9-]*$/', $item) === 1) {
			return new self(self::KIND_FRAGMENT, $item);
		} else {
			return null;
		}
	}
}

/**
 * Shared state of a build: manifest, variables, sources and warnings.
 */
final class ReadmeBuildContext {
	const VARIABLE_PATTERN = '/\{\{\s*([A-Za-z][A-Za-z0-9_]*)\s*\}\}/';

	public readonly ReadmeManifest $manifest;

	/**
	 * @var array<string, string>
	 */
	public readonly array $variables;

	private ?ReadmeChangelog $_changelog = null;

	/**
	 * @var string[]
	 */
	private array $_warnings = array();

	public function __construct(ReadmeManifest $manifest) {
		$this->manifest = $manifest;
		$this->variables = self::_collectVariables($manifest);
	}

	/**
	 * @return array<string, string>
	 */
	private static function _collectVariables(ReadmeManifest $manifest): array {
		$headerVariables = ReadmePluginHeader::readVariables($manifest->pluginHeader);
		$duplicates = array_intersect_key($headerVariables, 
			$manifest->variables);

		if (!empty($duplicates)) {
			throw new ReadmeBuildException(
				sprintf('Manifest: variables defined both in "pluginHeader" and "variables": %s.',
					implode(', ', array_keys($duplicates)))
			);
		}

		return array_merge($headerVariables, 
			$manifest->variables);
	}

	/**
	 * Replaces {{variable}} placeholders. $source names the text in error messages.
	 */
	public function expandVariables(string $text, string $source): string {
		return (string)preg_replace_callback(self::VARIABLE_PATTERN, function(array $matches) use ($source) {
			if (!isset($this->variables[$matches[1]])) {
				throw new ReadmeBuildException(
					sprintf('%s: unknown variable "%s".', 
						$source, 
						$matches[1])
				);
			}
			return $this->variables[$matches[1]];
		}, $text);
	}

	/**
	 * Returns a section fragment without HTML comments, with variables expanded.
	 */
	public function loadFragment(string $name): string {
		$filePath = $this->_getFragmentFilePath($name);
		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new ReadmeBuildException(
				sprintf('Section fragment "%s" not found at "%s".', 
					$name, 
					$filePath)
			);
		}

		$rawContents = (string)file_get_contents($filePath);
		$markdown = ReadmeText::normalizeLineEndings($rawContents);

		if (preg_match('/<!--.*?\bTODO\b.*?-->/s', $markdown) === 1) {
			$this->warn(sprintf(
				'Section fragment "%s" contains TODO notes.', 
				$name
			));
		}

		$markdown = ReadmeText::stripHtmlComments($markdown);
		return trim($this->expandVariables($markdown, 
			$filePath));
	}

	private function _getFragmentFilePath(string $name): string {
		return $this->manifest->paths->sections . '/' . $name . '.md';
	}

	public function getChangelog(): ReadmeChangelog {
		if ($this->_changelog === null) {
			$this->_changelog = ReadmeChangelog::fromFile($this->manifest->paths->changelog);
		}
		return $this->_changelog;
	}

	/**
	 * Returns a path relative to the plugin root, with forward slashes.
	 */
	public function relativeToRoot(string $path): string {
		$root = rtrim(str_replace('\\', '/', $this->manifest->rootDirectory), '/') . '/';
		$path = str_replace('\\', '/', $path);
		return str_starts_with($path, $root)
			? substr($path, strlen($root))
			: $path;
	}

	public function warn(string $message): void {
		if (!in_array($message, $this->_warnings, true)) {
			$this->_warnings[] = $message;
		}
	}

	/**
	 * @return string[]
	 */
	public function getWarnings(): array {
		return $this->_warnings;
	}
}
