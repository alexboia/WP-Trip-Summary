<?php
trait Abp01_LoggingTrait {
	public function getTraitValue(): string {
		return 'trait-ok';
	}
}
require __DIR__ . '/SingleTrait.php';
echo (new \WpTripSummary\TraitConsumer())->getTraitValue();