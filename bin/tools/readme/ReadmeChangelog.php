<?php
declare(strict_types=1);

final readonly class ReadmeChangelogVersion {
	/**
	 * @param string[] $lines Non-empty Markdown lines of the version entry
	 */
	public function __construct(
		public string $version,
		public array $lines
	) {
	}
}

/**
 * The versions listed in CHANGELOG.md, newest first.
 * A version starts with a "### Version X" heading and ends at the next heading of level 3 or above.
 */
final readonly class ReadmeChangelog {
	const VERSION_HEADING_PATTERN = '/^###\s+Version\s+(\S+)\s*$/i';

	const SECTION_HEADING_PATTERN = '/^#{1,3}\s/';

	/**
	 * @param ReadmeChangelogVersion[] $versions
	 */
	public function __construct(public array $versions) {
	}

	public static function fromFile(string $filePath): self {
		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new ReadmeBuildException(sprintf('Cannot read changelog file "%s".', $filePath));
		}

		$versions = array();
		$currentVersion = null;
		$currentLines = array();

		foreach (ReadmeText::splitLines((string)file_get_contents($filePath)) as $line) {
			if (preg_match(self::SECTION_HEADING_PATTERN, $line) === 1) {
				if ($currentVersion !== null) {
					$versions[] = new ReadmeChangelogVersion($currentVersion, $currentLines);
				}

				$currentVersion = preg_match(self::VERSION_HEADING_PATTERN, $line, $matches) === 1
					? $matches[1]
					: null;
				$currentLines = array();
			} else if ($currentVersion !== null && trim($line) !== '') {
				$currentLines[] = rtrim($line);
			}
		}

		if ($currentVersion !== null) {
			$versions[] = new ReadmeChangelogVersion($currentVersion, $currentLines);
		}

		if (empty($versions)) {
			throw new ReadmeBuildException(sprintf('No "### Version X" entries found in "%s".', $filePath));
		}

		return new self($versions);
	}

	/**
	 * @return ReadmeChangelogVersion[]
	 */
	public function latest(?int $count): array {
		return $count === null
			? $this->versions
			: array_slice($this->versions, 0, $count);
	}

	public function hasVersion(string $version): bool {
		foreach ($this->versions as $changelogVersion) {
			if ($changelogVersion->version === $version) {
				return true;
			}
		}
		return false;
	}
}
