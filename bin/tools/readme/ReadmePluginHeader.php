<?php
declare(strict_types=1);

/**
 * Reads WordPress plugin header fields, the same way get_file_data() does.
 */
final class ReadmePluginHeader {
	const MAX_HEADER_BYTES = 8192;

	/**
	 * @return array<string, string> Variable names mapped to header field values
	 */
	public static function readVariables(ReadmeManifestPluginHeader $pluginHeader): array {
		$header = self::_readHeader($pluginHeader->file);
		$values = array();

		foreach ($pluginHeader->variables as $variable => $field) {
			$pattern = '/^[ \t\/*#@]*' . preg_quote($field, '/') . ':(.*)$/mi';
			if (preg_match($pattern, $header, $matches) !== 1 
				|| trim($matches[1]) === '') {
				throw new ReadmeBuildException(
					sprintf('Plugin header field "%s" was not found in "%s".',
						$field,
						$pluginHeader->file)
				);
			}
			$values[$variable] = trim($matches[1]);
		}

		return $values;
	}

	private static function _readHeader(string $filePath): string {
		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new ReadmeBuildException(
				sprintf('Cannot read plugin header file "%s".', 
					$filePath)
			);
		}

		$header = file_get_contents($filePath, 
			false, 
			null, 
			0, 
			self::MAX_HEADER_BYTES);

		return str_replace("\r", 
			"\n", 
			(string)$header);
	}
}
