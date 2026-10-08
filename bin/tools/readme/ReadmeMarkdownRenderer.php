<?php
declare(strict_types=1);

/**
 * Renders a GitHub-flavored Markdown readme: the fragments, in order, separated by blank lines.
 */
final class ReadmeMarkdownRenderer extends ReadmeTargetRenderer {
	public function render(): string {
		$this->_target->assertKnownKeys(array('format', 'output', 'banner', 'sections'),
			array('screenshot'));

		$blocks = array();
		$banner = $this->_target->optionalValue('banner');
		if ($banner !== null && trim($banner->value) !== '') {
			$blocks[] = $this->_expand($banner);
		}

		foreach ($this->_parseItems($this->_target->requireValue('sections')) as $item) {
			$blocks[] = $this->_renderItem($item);
		}

		return ReadmeText::joinBlocks($blocks);
	}

	private function _renderItem(ReadmeSectionItem $item): string {
		if ($item->kind === ReadmeSectionItem::KIND_CHANGELOG) {
			return $this->_renderChangelog($item);
		} else if ($item->kind === ReadmeSectionItem::KIND_SCREENSHOTS) {
			return $this->_renderScreenshots();
		} else {
			return $this->_context->loadFragment($item->name);
		}
	}

	private function _renderChangelog(ReadmeSectionItem $item): string {
		$blocks = array();
		foreach ($this->_getChangelogVersions($item) as $version) {
			$blocks[] = '### Version ' . $version->version . "\n" . implode("\n", $version->lines);
		}
		return implode("\n\n", $blocks);
	}

	private function _renderScreenshots(): string {
		$blocks = array();
		foreach ($this->_getScreenshots() as $path => $caption) {
			$blocks[] = sprintf('![%s](%s)', $caption, $path);
		}
		return implode("\n\n", $blocks);
	}
}
