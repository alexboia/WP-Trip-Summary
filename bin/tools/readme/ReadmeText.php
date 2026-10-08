<?php
declare(strict_types=1);

/**
 * Text helpers shared by the readme renderers.
 */
final class ReadmeText {
	const FENCE_PATTERN = '/^\s*(```|~~~)/';

	/**
	 * @return string[]
	 */
	public static function splitLines(string $text): array {
		return explode("\n", self::normalizeLineEndings($text));
	}

	public static function normalizeLineEndings(string $text): string {
		return str_replace(array("\r\n", "\r"), "\n", $text);
	}

	public static function stripHtmlComments(string $text): string {
		return (string)preg_replace('/<!--.*?-->/s', '', $text);
	}

	/**
	 * Splits a comma-separated value into trimmed, non-empty items.
	 *
	 * @return string[]
	 */
	public static function splitList(string $value): array {
		return array_values(array_filter(array_map('trim', explode(',', $value)),
			function(string $item) {
				return $item !== '';
			}));
	}

	/**
	 * Joins blocks with one blank line, collapses repeated blank lines and ends the text with a newline.
	 *
	 * @param string[] $blocks
	 */
	public static function joinBlocks(array $blocks): string {
		$text = implode("\n\n", array_map('trim', $blocks));
		$text = (string)preg_replace('/^[ \t]+$/m', '', $text);
		$text = (string)preg_replace("/\n{3,}/", "\n\n", $text);
		return trim($text) . "\n";
	}

	/**
	 * Applies $transform to every line outside fenced code blocks.
	 * The callback returns the new line, or null to remove it.
	 *
	 * @param callable(string): ?string $transform
	 */
	public static function transformProseLines(string $text, callable $transform): string {
		$output = array();
		$inFence = false;

		foreach (self::splitLines($text) as $line) {
			if (preg_match(self::FENCE_PATTERN, $line) === 1) {
				$inFence = !$inFence;
				$output[] = $line;
			} else if ($inFence) {
				$output[] = $line;
			} else {
				$transformed = $transform($line);
				if ($transformed !== null) {
					$output[] = $transformed;
				}
			}
		}

		return implode("\n", $output);
	}
}
