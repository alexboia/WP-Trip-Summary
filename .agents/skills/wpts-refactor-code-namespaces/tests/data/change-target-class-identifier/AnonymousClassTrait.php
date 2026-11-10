<?php
class Abp01_TraitConsumer {
	public function make(): mixed {
		return new class {
			use Abp01_AnonymousTrait;
		};
	}
}