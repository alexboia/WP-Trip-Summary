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

class CachedChangelogDataSourceTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	protected function setUp(): void {
		parent::setUp();
		$this->_purgeCache();
	}

	private function _purgeCache() {
		delete_option(Abp01_ChangeLogDataSource_Cached::OPT_CHANGELOG_CACHE_KEY);
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->_purgeCache();
		Mockery::close();
	}

	public function test_canGetChangeLog_noChangesInVersion() {
		$changelogData = $this->_generateChangeLogData();
		$dataSourceMock = $this->_createDataSourceMock($changelogData, 1);
		$cachedDataSource = $this->_createCachedChangeLogDataSource($dataSourceMock);

		$firstCallChageLog = $cachedDataSource->getChangeLog();
		$secondCallChageLog = $cachedDataSource->getChangeLog();

		$this->assertEquals($firstCallChageLog, $secondCallChageLog);
		$this->_assertCacheExists();
	}

	private function _generateChangeLogData() {
		$changeLogData = array();
		$faker = $this->_getFaker();

		for ($iVersion = 0; $iVersion < 5; $iVersion ++) {
			$version = $faker->uuid;
			$changeLogData[$version] = array();
			for ($iChangeLogLine = 0; $iChangeLogLine < 10; $iChangeLogLine ++) {
				$changeLogData[$version][] = $faker->sentence();
			}
		}

		return $changeLogData;
	}

	/**
	 * @return Abp01_ChangeLogDataSource
	 */
	private function _createDataSourceMock(array $changeLog, $expectedCallCount) {
		/** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|Abp01_ChangeLogDataSource $mock */
		$mock = Mockery::mock('Abp01_ChangeLogDataSource');
        $mock = $mock->shouldReceive('getChangeLog')
			->times($expectedCallCount)
            ->andReturn($changeLog)
            ->getMock();

        return $mock;
	}

	private function _createCachedChangeLogDataSource(Abp01_ChangeLogDataSource $dataSource) {
		return new Abp01_ChangeLogDataSource_Cached($dataSource, $this->_getEnv());
	}

	private function _assertCacheExists() {
		$cache = get_option(Abp01_ChangeLogDataSource_Cached::OPT_CHANGELOG_CACHE_KEY, false);
		$this->assertTrue(is_array($cache));
		$this->assertArrayHasKey('_data', $cache);
		$this->assertArrayHasKey('_version', $cache);
		$this->assertEquals($this->_getEnv()->getVersion(), $cache['_version']);
	}

	private function _getEnv() {
		return abp01_get_env();
	}

	public function test_canGetChangeLog_withChangesInVersion() {
		$faker = $this->_getFaker();
		$changelogData = $this->_generateChangeLogData();
		$dataSourceMock = $this->_createDataSourceMock($changelogData, 2);
		$cachedDataSource = $this->_createCachedChangeLogDataSource($dataSourceMock);

		$firstCallChageLog = $cachedDataSource->getChangeLog();

		$this->_setCachedDataVersion($faker->uuid);
		$secondCallChageLog = $cachedDataSource->getChangeLog();

		$this->assertEquals($firstCallChageLog, $secondCallChageLog);
		$this->_assertCacheExists();
	}

	private function _setCachedDataVersion($version) {
		$cache = get_option(Abp01_ChangeLogDataSource_Cached::OPT_CHANGELOG_CACHE_KEY);
		$cache['_version'] = $version;
		update_option(Abp01_ChangeLogDataSource_Cached::OPT_CHANGELOG_CACHE_KEY, $cache);
	}
}