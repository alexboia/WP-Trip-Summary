<?php
namespace WpTripSummary\Skills\WpCodingConventions {
	final readonly class LicenseHeader {
		public function __construct(public string $header, 
			public int $offset, 
			public int $length,
			public int $year) {
			return;
		}

		public function toArray(): array {
			return [
				'header' => $this->normalizedHeader(),
				'offset' => $this->offset,
				'length' => $this->length,
				'year' => $this->year
			];
		}

		public function normalizedHeader(): string {
			return str_replace("\r\n", "\n", $this->header);
		}

		public function toJson() {
			return json_encode($this->toArray(), JSON_PRETTY_PRINT);
		}
	}
}