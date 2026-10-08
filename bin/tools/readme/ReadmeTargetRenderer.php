<?php
declare(strict_types=1);

/**
 * Renders one Makefile target. The target's "format" selects the renderer.
 */
abstract class ReadmeTargetRenderer {
	const FORMATS = array(
		'markdown' => 'ReadmeMarkdownRenderer',
		'wporg' => 'ReadmeWpOrgRenderer'
	);

	protected ReadmeMakefileTarget $_target;

	protected ReadmeBuildContext $_context;

	public function __construct(ReadmeMakefileTarget $target, ReadmeBuildContext $context) {
		$this->_target = $target;
		$this->_context = $context;
	}

	public static function create(ReadmeMakefileTarget $target, ReadmeBuildContext $context): self {
		$format = $target->requireChoice('format', array_keys(self::FORMATS));
		$rendererClass = self::FORMATS[$format];
		return new $rendererClass($target, $context);
	}

	abstract public function render(): string;

	public function getOutputPath(): string {
		$output = $this->_target->requireValue('output');
		$relativePath = str_replace('\\', 
			'/', 
			trim($output->value)
		);

		if (preg_match('#^([A-Za-z]:)?/#', $relativePath) === 1 
			|| str_contains($relativePath, '..')) {
			throw $this->_target->errorAtLine(
				$output->line, 
				'"output" must be a path relative to the plugin root, without ".."'
			);
		}

		return rtrim($this->_context->manifest->rootDirectory, '/\\') . '/' 
			. $relativePath;
	}

	protected function _expand(ReadmeMakefileValue $value): string {
		$source = sprintf('%s:%d', 
			$this->_target->filePath, 
			$value->line);

		return trim($this->_context->expandVariables($value->value, 
			$source));
	}

	/**
	 * @return ReadmeSectionItem[]
	 */
	protected function _parseItems(ReadmeMakefileValue $value): array {
		$items = array();

		foreach (ReadmeText::splitList($value->value) as $itemText) {
			$item = ReadmeSectionItem::parse($itemText);
			if ($item === null) {
				throw $this->_target->errorAtLine(
					$value->line,
					sprintf('"%s" is not a fragment name, @changelog, @changelog:N or @screenshots', 
						$itemText)
				);
			}
			$items[] = $item;
		}

		if (empty($items)) {
			throw $this->_target->errorAtLine(
				$value->line, 
				'the section list is empty'
			);
		}

		return $items;
	}

	/**
	 * @return ReadmeChangelogVersion[]
	 */
	protected function _getChangelogVersions(ReadmeSectionItem $item): array {
		return $this->_context->getChangelog()
			->latest($item->count);
	}

	/**
	 * Returns the screenshot[file] = caption entries, after checking that the files exist.
	 *
	 * @return array<string, string> Paths relative to the plugin root mapped to captions
	 */
	protected function _getScreenshots(): array {
		$entries = $this->_target->map('screenshot');
		if (empty($entries)) {
			throw $this->_target->errorAtLine(
				$this->_target->line, 
				'@screenshots needs at least one screenshot[file] = caption entry'
			);
		}

		$screenshots = array();
		foreach ($entries as $fileName => $caption) {
			$filePath = $this->_context->manifest->paths->screenshots . '/' . $fileName;
			if (!is_file($filePath)) {
				throw $this->_target->errorAtLine(
					$caption->line, 
					sprintf('screenshot "%s" not found', 
						$filePath)
				);
			}

			$relativePath = $this->_context
				->relativeToRoot($filePath);

			$screenshots[$relativePath] = 
				$this->_expand($caption);
		}

		return $screenshots;
	}
}
