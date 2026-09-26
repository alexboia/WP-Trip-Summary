<?php
declare(strict_types=1);

final class IndentationType {
	public const string TAB = 'tab';

	public const string SPACE = 'space';

	public const string MIXED = 'mixed';
}

final readonly class LineIndentation {
	public function __construct(
		public string $file, 
		public int $line, 
		public array $indentationTypes,
		public int $blockLevel
	) {
		return;
	}
}