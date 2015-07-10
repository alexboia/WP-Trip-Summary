<?php
class Abp01_InputFiltering {
	public static function filterSingleValue($input, $asType) {
		if (get_magic_quotes_gpc()) {
			$rawInput = stripslashes($input);
		}

		$input = sanitize_text_field($input);
		settype($input, $asType);

		if (is_numeric($input)) {
			$minVal = func_num_args() >= 3 ? func_get_arg(2) : ~PHP_INT_MAX;
			$maxVal = func_num_args() == 4 ? func_get_arg(3) : PHP_INT_MAX;
			$input = max($minVal, $input);
			$input = min($maxVal, $input);
		}
		return $input;
	}
	
	public static function filterValue($input, $asType) {
		$extraParams = array();
		$nArgs = func_num_args();
		$callback = array(self, 'filterSingleValue');
		
		if ($nArgs > 2) {
			for ($i = 2; $i < $nArgs; $i ++) {
				$extraParams = func_get_arg($i);
			}
		}
		
		if (is_array($value)) {
			$result = array();
			foreach ($input as $value) {
				$result[] = call_user_func_array($callback, array_merge(array($value), $extraParams));
			}
			return $result;
		} else {
			return call_user_func_array($callback, array_merge(array($input), $extraParams));
		}
	}
}