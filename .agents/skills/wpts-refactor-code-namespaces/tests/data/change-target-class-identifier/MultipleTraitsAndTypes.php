<?php
class Abp01_TraitConsumer {
	use /* first trait */ Abp01_FirstTrait,
		// second trait
		Abp01_SecondTrait;
	use Abp01_ThirdTrait;
	public function convert(?Abp01_Input $input): Abp01_Output|false {
		return false;
	}
	public function traitClass(): string {
		return Abp01_FirstTrait::class;
	}
}