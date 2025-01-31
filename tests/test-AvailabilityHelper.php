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

class AvailabilityHelperTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canGetTripSummaryAvailableForPostTypes() {
		$availableTypes = Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes();
		$this->assertNotEmpty($availableTypes);
		$this->assertCount(2, $availableTypes);
		$this->assertContains(Abp01_AvailabilityHelper::POST_TYPE_PAGE, $availableTypes);
		$this->assertContains(Abp01_AvailabilityHelper::POST_TYPE_POST, $availableTypes);
	}

	public function test_canCheckIsEditorAvailableForPostType_whenValidPostType() {
		$availableTypes = Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes();
		foreach ($availableTypes as $t) {
			$this->assertTrue(Abp01_AvailabilityHelper::isEditorAvailableForPostType($t));
		}
	}

	public function test_canCheckIsEditorAvailableForPostType_whenInvalidPostType() {
		$unspportedType = $this->_generateUnsupportedPostType();
		$this->assertFalse(Abp01_AvailabilityHelper::isEditorAvailableForPostType($unspportedType));
	}

	private function _generateUnsupportedPostType() {
		$faker = $this->_getFaker();
		$type = $faker->text();
		while (in_array($type, Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes())) {
			$type = $faker->text();
		}
		return $type;
	}
}