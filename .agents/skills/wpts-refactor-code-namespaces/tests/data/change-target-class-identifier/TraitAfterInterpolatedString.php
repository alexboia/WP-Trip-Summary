<?php
class Abp01_TraitConsumer {
	public function describe(string $name): string {
		return "{$name}}";
	}
	use Abp01_LoggingTrait;
}