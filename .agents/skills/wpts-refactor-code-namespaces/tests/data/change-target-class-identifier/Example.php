<?php
/** The word use in a comment is not an import. */

class Abp01_Example extends Abp01_Base implements Abp01_First, Abp01_Second {
	public static function make(): Abp01_Example {
		$className = Abp01_Dynamic::class;
		$dynamic = new $className();
		new Abp01_Service();
		if ($dynamic instanceof Abp01_Contract) {
			Abp01_StaticTool::run();
		}
		try {
			$dynamicNew = new Abp01_Dynamic();
		} catch (Abp01_FirstException|Abp01_SecondException $exception) {}
		return new Abp01_Example();
	}
}