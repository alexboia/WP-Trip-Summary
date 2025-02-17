<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class ConversionHelperTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canConvert_whenInBytes() {
		$formatted = Abp01_ConversionHelper::getByteSizeDescription(0);
		$this->assertEquals('0B', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(500);
		$this->assertEquals('500B', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1023);
		$this->assertEquals('1023B', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1024);
		$this->assertEquals('1024B', $formatted);
	}

	public function test_canConvert_whenInKiloBytes() {
		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1026);
		$this->assertEquals('1KB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1028);
		$this->assertEquals('1KB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(2048);
		$this->assertEquals('2KB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1048576);
		$this->assertEquals('1024KB', $formatted);
	}

	public function test_canConvert_whenInMegaBytes() {
		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1048577);
		$this->assertEquals('1MB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(10485770);
		$this->assertEquals('10MB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(11 * 1024 * 1024);
		$this->assertEquals('11MB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(6.6 * 1024 * 1024);
		$this->assertEquals('6.6MB', $formatted);

		$formatted = Abp01_ConversionHelper::getByteSizeDescription(1024 * 1024 * 1024);
		$this->assertEquals('1024MB', $formatted);
	}
}