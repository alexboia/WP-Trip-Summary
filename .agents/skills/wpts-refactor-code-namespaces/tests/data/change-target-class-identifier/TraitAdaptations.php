<?php
class Abp01_TraitConsumer {
	use Abp01_FirstTrait, Abp01_SecondTrait, Abp01_ThirdTrait {
		Abp01_FirstTrait::execute insteadof Abp01_SecondTrait, Abp01_ThirdTrait;
		Abp01_SecondTrait::execute as private executeSecond;
		execute as protected;
	}
	private const AFTER_TRAITS = 'after';
	public function methodAfterTraits(): string {
		return 'done';
	}
}