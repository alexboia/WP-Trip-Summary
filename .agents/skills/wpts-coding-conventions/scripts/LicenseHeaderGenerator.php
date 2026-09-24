<?php
namespace WpTripSummary\Skills\WpCodingConventions {
	use InvalidArgumentException;
	use RuntimeException;

	class LicenseHeaderGenerator {
		private string $_licenseHeaderTemplateFile;

		private ?string $_licenseHeaderTemplate = null;

		public function __construct(string $licenseHeaderTemplateFile) {
			if (!is_file($licenseHeaderTemplateFile) || !is_readable($licenseHeaderTemplateFile)) {
				throw new InvalidArgumentException('Invalid license header template file specified.');
			}

			$this->_licenseHeaderTemplateFile = $licenseHeaderTemplateFile;
		}

		public function generate(int $year): string {
			if ($year < 2014 || $year > 9999) {
				throw new InvalidArgumentException('License year must be between 2014 and 9999.');
			}

			return str_ireplace('$CURRENT_YEAR$', (string) $year, $this->_getLicenseHeaderTemplate());
		}

		private function _getLicenseHeaderTemplate(): string {
			if ($this->_licenseHeaderTemplate === null) {
				$template = @file_get_contents($this->_licenseHeaderTemplateFile);
				if ($template === false) {
					throw new RuntimeException('Could not read license header template.');
				}
				$this->_licenseHeaderTemplate = $template;
			}

			return $this->_licenseHeaderTemplate;
		}

		public function generateForCurrentYear(): string {
			return $this->generate(intval(date('Y')));
		}
	}
}