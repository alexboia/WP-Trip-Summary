<?php
namespace WpTripSummary\Skills\WpCodingConventions {

    use InvalidArgumentException;

	class LicenseHeaderGenerator {
		private string $_licenseHeaderTemplateFile;

		private ?string $_licenseHeaderTemplate = null;

		public function __construct(string $licenseHeaderTemplateFile) {
			if (!is_readable($licenseHeaderTemplateFile)) {
				throw new InvalidArgumentException('Invalid license header template file specified.');
			}

			$this->_licenseHeaderTemplateFile = $licenseHeaderTemplateFile;
		}

		public function generate(int $year): string {		
			$vars = array(
				'$CURRENT_YEAR$' => $year
			);

			$template = $this->_getLicenseHeaderTemplate();
			$header = $template;

			foreach ($vars as $name => $value) {
				$header = str_ireplace($name, 
					$value, 
					$header);
			}

			return $header;
		}

		private function _getLicenseHeaderTemplate(): string {
			if ($this->_licenseHeaderTemplate === null) {
				$this->_licenseHeaderTemplate = file_get_contents($this->_licenseHeaderTemplateFile);
			}

			return $this->_licenseHeaderTemplate;
		}

		public function generateForCurrentYear(): string {
			return $this->generate(intval(date('Y')));
		}
	}
}