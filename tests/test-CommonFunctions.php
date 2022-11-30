<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class CommonFunctionsTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canGetWpErrorFromException() {
		$expectedFile = __FILE__;
		$expectedLineBefore = __LINE__;
		$testException = new Exception('Sample error message', 0x1234);
		$wpError = abp01_wp_error_from_exception($testException);

		$this->assertNotNull($wpError);
		$this->assertNotEmpty($wpError);
		$this->assertEquals('Sample error message', $wpError->get_error_message());
		$this->assertEquals(0x1234, $wpError->get_error_code());
		
		$data = $wpError->get_error_data();
		$this->assertNotEmpty($data);
		
		$this->assertArrayHasKey('file', $data);
		$this->assertEquals($expectedFile, $data['file']);

		$this->assertArrayHasKey('line', $data);
		$this->assertEquals($expectedLineBefore + 1, $data['line']);

		$this->assertArrayHasKey('stackTrace', $data);
		$this->assertEquals($testException->getTraceAsString(), $data['stackTrace']);
	}

	public function test_canCreateAjaxResponse_noAdditionalProps() {
		$ajaxResponse = abp01_get_ajax_response();
		
		$this->_assertBasicAjaxResponseStructureCorrect($ajaxResponse);
		$this->assertEquals(2, $this->_countObjectVars($ajaxResponse));
	}

	private function _assertBasicAjaxResponseStructureCorrect(stdClass $ajaxResponse) {
		$this->assertNotEmpty($ajaxResponse);
		$this->assertFalse($ajaxResponse->success);
		$this->assertNull($ajaxResponse->message);
	}

	public function test_canCreateAjaxResponse_withAdditionalProps() {
		$faker = $this->_getFaker();
		$additionalProps = array(
			'key1' => $faker->randomNumber(),
			'key2' => $faker->randomAscii,
			'key3' => $faker->boolean()
		);

		$ajaxResponse = abp01_get_ajax_response($additionalProps);

		$this->_assertBasicAjaxResponseStructureCorrect($ajaxResponse);
		foreach ($additionalProps as $key => $value) {
			$this->assertTrue(isset($ajaxResponse->$key));
			$this->assertEquals($value, $ajaxResponse->$key);
		}

		$this->assertEquals(count($additionalProps) + 2, 
			$this->_countObjectVars($ajaxResponse));
	}

	public function test_canGetStatusText_knownStatus() {
		$faker = $this->_getFaker();
		$statusToCssClassMapping = array(
			ABP01_STATUS_OK => 'abp01-status-ok',
			ABP01_STATUS_ERR => 'abp01-status-err',
			ABP01_STATUS_WARN => 'abp01-status-warn'
		);

		foreach ($statusToCssClassMapping as $status => $cssClass) {
			$text = $faker->randomAscii;
			$expectedFormattedStatusText = '<span class="abp01-status-text ' . $cssClass . '">' . $text . '</span>';

			$this->assertEquals($expectedFormattedStatusText, 
				abp01_get_status_text($text,  $status));
		}
	}

	public function test_canGetStatusText_unknownStatus() {
		$faker = $this->_getFaker();
		
		for ($i = 0; $i < 10; $i ++) {
			$text = $faker->randomAscii;
			$status = $faker->numberBetween(100, 1000);
			$expectedFormattedStatusText = '<span class="abp01-status-text abp01-status-neutral">' . $text . '</span>';

			$this->assertEquals($expectedFormattedStatusText, 
				abp01_get_status_text($text,  $status));
		}
	}

	public function test_canExtractPostIds_nonEmptyPostList() {
		$fakePostCounts = array(
			1, 5, 10, 100
		);

		foreach ($fakePostCounts as $count) {
			$expectedPostsData = $this->_generateFakePosts($count);
			$postIds = abp01_extract_post_ids($expectedPostsData['posts']);

			$this->_assertCollectedPostIdsMatchExpectedPostIds($postIds, 
				$expectedPostsData);
		}
	}

	private function _assertCollectedPostIdsMatchExpectedPostIds($colelctedPostsIds, $expectedPostData) {
		$this->assertEquals(count($expectedPostData['ids']), count($colelctedPostsIds));
		$this->assertEmpty(array_diff($colelctedPostsIds, $expectedPostData['ids']));
	}

	private function _generateFakePosts($count) {
		$faker = $this->_getFaker();
		$posts = array();
		$postIds = array();

		for ($i = 0; $i < $count; $i++) {
			$data = new stdClass();
			$data->ID = $faker->randomNumber();
			$post = new WP_Post($data);
			$posts[] = $post;
			$postIds[] = $data->ID;
		}
		
		return array(
			'posts' => $posts,
			'ids' => $postIds
		);
	}

	public function test_canExtractPostIds_emptyPostList() {
		$this->assertEmpty(abp01_extract_post_ids(array()));
	}

	public function test_canExtractPostIds_ignoresUnsupportedPostData() {
		$fakePostCounts = array(
			1, 5, 10, 100
		);

		foreach ($fakePostCounts as $count) {
			$expectedPostsData = $this->_generateFakePostsWithUnsupportedData($count);
			$postIds = abp01_extract_post_ids($expectedPostsData['posts']);

			$this->_assertCollectedPostIdsMatchExpectedPostIds($postIds, 
				$expectedPostsData);
		}
	}

	private function _generateFakePostsWithUnsupportedData($count) {
		$faker = $this->_getFaker();
		$posts = array();
		$postIds = array();

		$invalidPostCount = $count > 1 
			? $faker->numberBetween(1, $count - 1)
			: $faker->randomNumber() % 2 == 0;

		for ($i = 0; $i < $count; $i++) {
			if ($invalidPostCount > 0) {
				$invalidPostCount --;
				$posts[] = $this->_generateInvalidPostData();
			} else {
				$data = new stdClass();
				$data->ID = $faker->randomNumber();
				$post = new WP_Post($data);
				$posts[] = $post;
				$postIds[] = $data->ID;
			}
		}
		
		return array(
			'posts' => $posts,
			'ids' => $postIds
		);
	}

	private function _generateInvalidPostData() {
		$faker = $this->_getFaker();

		if ($faker->randomNumber() % 2 == 0) {
			$data = new stdClass();
			$data->someProp = $faker->randomAscii;
		} else {
			$data = array(
				'someKey' => $faker->randomNumber()
			);
		}

		return $data;
	}

	public function test_canGetLookupTypeLabel_whenValidType() {
		foreach (Abp01_Lookup::getSupportedCategories() as $c) {
			$label = abp01_get_lookup_type_label($c);
			$this->assertNotEmpty($label);
		}
	}

	public function test_canGetLookupTypeLabel_whenInvalidType() {
		$invalidType = $this->_generateInvalidLookupType();
		$label = abp01_get_lookup_type_label($invalidType);
		$this->assertEmpty($label);
	}

	private function _generateInvalidLookupType() {
		$faker = $this->_getFaker();
		$invalidLookupType = $faker->text();
		while (Abp01_Lookup::isTypeSupported($invalidLookupType)) {
			$invalidLookupType = $faker->text();
		}
		return $invalidLookupType;
	}
}