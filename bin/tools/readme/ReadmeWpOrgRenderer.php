<?php
declare(strict_types=1);

/**
 * Renders a WordPress.org plugin directory readme.
 *
 * Fragments are converted as follows, outside fenced code blocks:
 * - level 1-3 headings become "= Heading ="; a fragment's first heading is dropped
 *   when the fragment is the only item of its section, which then provides the title;
 * - bold-only questions ("**Question?**") become "= Question? =";
 * - images are removed, a linked image becomes a text link to the image's target;
 * - HTML tags are removed, keeping their text;
 * - links to "#anchors" become plain text; relative links are prefixed with "link-base".
 */
final class ReadmeWpOrgRenderer extends ReadmeTargetRenderer {
	const MAX_SHORT_DESCRIPTION_LENGTH = 150;

	const HEADING_PATTERN = '/^#{1,3}\s+(.+?)\s*#*\s*$/';

	const QUESTION_PATTERN = '/^\*\*([^*]+\?)\*\*\s*$/';

	const IMAGE_LINE_PATTERN = '/^\s*!\[[^\]]*\]\([^)]*\)\s*$/';

	const LINKED_IMAGE_PATTERN = '/\[!\[([^\]]*)\]\([^)]*\)\]\(([^)]+)\)/';

	const IMAGE_PATTERN = '/!\[[^\]]*\]\([^)]*\)/';

	const LINK_PATTERN = '/(?<!!)\[([^\]]+)\]\(([^)\s]+)\)/';

	const ABSOLUTE_URL_PATTERN = '#^([a-z][a-z0-9+.-]*:|//)#i';

	private ?string $_linkBase = null;

	public function render(): string {
		$this->_target->assertKnownKeys(
			array('format', 
				'output', 
				'title', 
				'short-description', 
				'link-base', 
				'changelog-links'),

			array('header', 
				'section', 
				'screenshot'));

		$linkBase = $this->_target->optionalValue('link-base');
		$this->_linkBase = $linkBase !== null
			? $this->_expand($linkBase)
			: null;

		$blocks = array(
			$this->_renderHeader(),
			$this->_renderShortDescription()
		);

		$sections = $this->_target->map('section');
		if (empty($sections)) {
			throw $this->_target->errorAtLine(
				$this->_target->line, 
				'at least one section[Name] = items entry is required'
			);
		}

		foreach ($sections as $sectionName => $itemsValue) {
			$blocks[] = '== ' . $sectionName . ' ==';
			$items = $this->_parseItems($itemsValue);
			foreach ($items as $item) {
				$blocks[] = $this->_renderItem($item, count($items) === 1);
			}
		}

		return ReadmeText::joinBlocks($blocks);
	}

	private function _renderHeader(): string {
		$lines = array('=== ' . $this->_expand($this->_target->requireValue('title')) . ' ===');

		foreach ($this->_target->map('header') as $field => $value) {
			$lines[] = $field . ': ' . $this->_expand($value);
		}

		$this->_checkStableTag();
		return implode("\n", $lines);
	}

	private function _checkStableTag(): void {
		$stableTag = $this->_target->map('header')['Stable tag'] ?? null;
		if ($stableTag === null) {
			$this->_context->warn(sprintf('[%s] No "Stable tag" header is defined.', $this->_target->name));
			return;
		}

		$version = $this->_expand($stableTag);
		if (!$this->_context->getChangelog()->hasVersion($version)) {
			$this->_context->warn(sprintf('[%s] Stable tag %s has no changelog entry.', $this->_target->name, $version));
		}
	}

	private function _renderShortDescription(): string {
		$value = $this->_target->requireValue('short-description');
		$shortDescription = $this->_expand($value);

		if (mb_strlen($shortDescription) > self::MAX_SHORT_DESCRIPTION_LENGTH) {
			throw $this->_target->errorAtLine($value->line, sprintf('"short-description" has %d characters; the limit is %d',
				mb_strlen($shortDescription),
				self::MAX_SHORT_DESCRIPTION_LENGTH));
		}

		return $shortDescription;
	}

	private function _renderItem(ReadmeSectionItem $item, bool $isOnlyItem): string {
		if ($item->kind === ReadmeSectionItem::KIND_CHANGELOG) {
			return $this->_renderChangelog($item);
		} else if ($item->kind === ReadmeSectionItem::KIND_SCREENSHOTS) {
			return $this->_renderScreenshots();
		} else {
			return $this->_convertFragment(
				$this->_context->loadFragment($item->name), 
				$item->name, 
				$isOnlyItem
			);
		}
	}

	private function _renderChangelog(ReadmeSectionItem $item): string {
		$linksAsText = $this->_target->requireChoice(
			'changelog-links', 
			array('keep', 'text'), 
			'keep'
		) === 'text';

		$blocks = array();

		foreach ($this->_getChangelogVersions($item) as $version) {
			$lines = array('= ' . $version->version . ' =');
			foreach ($version->lines as $line) {
				$lines[] = $linksAsText
					? (string)preg_replace(self::LINK_PATTERN, '$1', $line)
					: $this->_convertLinks($line, 'CHANGELOG');
			}
			$blocks[] = implode("\n", $lines);
		}

		return implode("\n\n", $blocks);
	}

	private function _renderScreenshots(): string {
		$lines = array();
		$number = 1;

		foreach ($this->_getScreenshots() as $caption) {
			$lines[] = $number . '. ' . $caption;
			$number++;
		}

		return implode("\n", $lines);
	}

	private function _convertFragment(string $markdown, string $fragmentName, bool $dropTitle): string {
		$markdown = (string)preg_replace('#<a\s+name="[^"]*"\s*>\s*</a>#i', 
			'', 
			$markdown);
			
		$isFirstHeading = true;

		return ReadmeText::transformProseLines($markdown,
			function(string $line) use (&$isFirstHeading, $dropTitle, $fragmentName) {
				if (preg_match(self::HEADING_PATTERN, $line, $matches) === 1) {
					$isTitle = $isFirstHeading;
					$isFirstHeading = false;
					return $isTitle && $dropTitle
						? null
						: '= ' . $matches[1] . ' =';
				}

				if (preg_match(self::QUESTION_PATTERN, $line, $matches) === 1) {
					return '= ' . trim($matches[1]) . ' =';
				}

				if (preg_match(self::IMAGE_LINE_PATTERN, $line) === 1) {
					return null;
				}

				$line = (string)preg_replace(self::LINKED_IMAGE_PATTERN, '[$1]($2)', $line);
				$line = (string)preg_replace(self::IMAGE_PATTERN, '', $line);
				if (preg_match('#</?[a-z][^>]*>#i', $line) === 1) {
					$line = strip_tags($line);
				}

				return $this->_convertLinks($line, $fragmentName);
			});
	}

	private function _convertLinks(string $line, string $source): string {
		return (string)preg_replace_callback(self::LINK_PATTERN, function(array $matches) use ($source) {
			list(, $text, $url) = $matches;

			if (str_starts_with($url, '#')) {
				return $text;
			} else if (preg_match(self::ABSOLUTE_URL_PATTERN, $url) === 1) {
				return $matches[0];
			} else if ($this->_linkBase === null) {
				$this->_context->warn(sprintf('[%s] Relative link "%s" in "%s" kept as is; set "link-base".',
					$this->_target->name,
					$url,
					$source));
				return $matches[0];
			} else {
				return sprintf('[%s](%s)', $text, rtrim($this->_linkBase, '/') . '/' . ltrim($url, '/'));
			}
		}, $line);
	}
}
