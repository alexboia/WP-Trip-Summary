<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Helper {

    use DateTime;
    use DateTimeZone;

	trait KmlDateTimeParser {
		protected static $_DEFAULT_FORMAT_FULL = 'Y-m-d\\TH:i:s.vP';

		protected static $_DEFAULT_FORMAT_MILLISECONDS = 'Y-m-d\\TH:i:s.v';

		protected static $_DEFAULT_FORMAT_SECONDS = 'Y-m-d\\TH:i:s';

		protected static $_DEFAULT_FORMAT_MINUTES = 'Y-m-d\\TH:i';

		protected static $_DEFAULT_FORMAT_DATE = 'Y-m-d';

		protected static $_DEFAULT_FORMAT_MONTH = 'Y-m';

		protected static $_DEFAULT_FORMAT_YEAR = 'Y';

		protected function _parseKmlDate(string|null $kmlDateTimeString, $format = null, DateTimeZone|null $timeZone): DateTime|null {
			if (empty($kmlDateTimeString)) {
				return null;
			}

			if (!empty($format)) {
				return DateTime::createFromFormat($format, $kmlDateTimeString, $timeZone);
			}

			$tryFormats = array(
				self::$_DEFAULT_FORMAT_FULL, 
				self::$_DEFAULT_FORMAT_MILLISECONDS,
				self::$_DEFAULT_FORMAT_SECONDS,
				self::$_DEFAULT_FORMAT_MINUTES,
				self::$_DEFAULT_FORMAT_DATE,
				self::$_DEFAULT_FORMAT_MONTH,
				self::$_DEFAULT_FORMAT_YEAR
			);

			foreach ($tryFormats as $f) {
				$result = DateTime::createFromFormat($f, $kmlDateTimeString, $timeZone);
				if ($result != null) {
					return $result;
				}
			}

			return null;
		}
	}
}