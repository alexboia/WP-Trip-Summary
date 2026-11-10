<?php
$captured = 'value';
$callback = static function(Abp01_Input $input) use /* capture */ ($captured): string {
	return $captured;
};
class Abp01_TraitConsumer {
	use Abp01_LoggingTrait;
	public function make(): mixed {
		$captured = 'value';
		return function(Abp01_Input $input) use (&$captured): ?Abp01_Output {
			return null;
		};
	}
}