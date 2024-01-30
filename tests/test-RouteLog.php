<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

 class RouteLogTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use RouteLogTestHelpers;

	/**
	 * @var IntegerIdGenerator
	 */
	private $_idGenerator;

	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->_idGenerator = new IntegerIdGenerator();
	}

	public function test_canCreate_withoutEntries() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId);
		$this->_assertRouteLogEmpty($log);
	}

	private function _assertRouteLogEmpty(Abp01_Route_Log $log) {
		$this->assertEquals(0, $log->getLogEntryCount());
		$this->assertFalse($log->hasLogEntries());
		$this->assertEmpty($log->getLogEntries());
		
		$this->assertNull($log->getFastestRun());
		$this->assertNull($log->getSlowestRun());

		$this->assertEquals(0, $log->getFastestTime());
		$this->assertEquals(0, $log->getSlowestTime());
	}

	public function test_canCreate_withEntries() {
		$postId = $this->_generatePostId();
		$logEntries = $this->_generateRouteLogEntries($postId);

		$log = new Abp01_Route_Log($postId, $logEntries);
		$this->_assertRouteLogOnlyHasEntries($log, $logEntries);
	}

	/**
	 * @param Abp01_Route_Log $log 
	 * @param Abp01_Route_Log_Entry[] $logEntries 
	 */
	private function _assertRouteLogOnlyHasEntries(Abp01_Route_Log $log, array $logEntries) {
		$this->assertEquals(count($logEntries), $log->getLogEntryCount());
		$this->assertTrue($log->hasLogEntries());

		foreach ($logEntries as $expectedLogEntry) {
			$actualLogEntry = $this->_findFirst(
				$log->getLogEntries(), 
				function(Abp01_Route_Log_Entry $logEntry) use ($expectedLogEntry) {
					return $logEntry->id === $expectedLogEntry->id;
				}, 
				null
			);

			$this->assertNotNull($actualLogEntry);
			$this->_assertLogEntriesMatch($expectedLogEntry, 
				$actualLogEntry);
		}
	}

	public function test_canSetEntries() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId);
		$logEntries = $this->_generateRouteLogEntries($postId);

		$log->setLogEntries($logEntries);
		$this->_assertRouteLogOnlyHasEntries($log, $logEntries);
	}

	public function test_canAddEntry() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId);
		
		$logEntries = $this->_generateRouteLogEntries($postId);
		$addedLogEntries = array();

		foreach ($logEntries as $addLogEntry) {
			$log->addLogEntry($addLogEntry);
			$addedLogEntries[] = $addLogEntry;
			$this->_assertRouteLogOnlyHasEntries($log, $addedLogEntries);
		}
	}

	public function test_canClearLogEntries() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId, $this->_generateRouteLogEntries($postId));

		$log->clearLogEntries();
		$this->_assertRouteLogEmpty($log);
	}

	public function test_canComputeStats() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId);

		$entry1 = new Abp01_Route_Log_Entry();
		$entry1->timeInHours = 5;

		$entry2 = new Abp01_Route_Log_Entry();
		$entry2->timeInHours = 10;

		$entry3 = new Abp01_Route_Log_Entry();
		$entry3->timeInHours = 1;

		$entry4 = new Abp01_Route_Log_Entry();
		$entry4->timeInHours = 3;

		$log->addLogEntry($entry1);
		$log->addLogEntry($entry2);
		$log->addLogEntry($entry3);
		$log->addLogEntry($entry4);

		$this->assertSame($entry2, $log->getSlowestRun());
		$this->assertSame($entry2->timeInHours, $log->getSlowestTime());

		$this->assertSame($entry3, $log->getFastestRun());
		$this->assertSame($entry3->timeInHours, $log->getFastestTime());

		$asPlainObj = $log->toPlainObject();

		$this->assertEquals($entry2->toPlainObject(), $asPlainObj->slowestRun);
		$this->assertEquals($entry3->toPlainObject(), $asPlainObj->fastestRun);
	}

	public function test_canConvertToPlainObject_whenEmpty() {
		$postId = $this->_generatePostId();
		$log = new Abp01_Route_Log($postId);

		$asPlainObj = $log->toPlainObject();

		$this->assertNotNull($asPlainObj);
		
		$this->assertEquals($postId, $asPlainObj->postId);
		$this->assertEquals(array(), $asPlainObj->logEntries);

		$this->assertNull($asPlainObj->slowestRun);
		$this->assertNull($asPlainObj->fastestRun);
	}

	public function test_canConvertToPlainObject_whenNotEmpty() {
		//TODO
	}
 }