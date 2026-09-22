<?php 
namespace WpTripSummary\Skills\WpDocumentHooks {
	final readonly class HookDoc {
		public function __construct(
			public string $name,
			public string $description,
			public ?string $category = null,
			public ?string $since = null,
			public array $unstable = array(),
			public array $see = array(),
			public array $parameters = array(),
		){
			return;
		}

		public function isUnstable(): bool {
			return !empty($this->unstable);
		}
	}
}