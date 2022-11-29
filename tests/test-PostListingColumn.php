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

class PostListingColumnTests extends WP_UnitTestCase {
    use GenericTestHelpers;

    public function test_canRenderLabel() {
        $faker = $this->_getFaker();

        for ($i = 0; $i < 10; $i ++) {
            $key = $faker->uuid;
            $label = $faker->words(3, true);
            $dataSource = $this->_getWildcardPostListingColumnDataSourceMock();

            $column = new Abp01_Display_PostListing_Column($key, 
                $label, 
                $dataSource);

            $this->assertEquals($label, $column->renderLabel());
            $this->assertEquals($key, $column->getKey());
        }
    }

    public function test_canRenderValue() {
        $faker = $this->_getFaker();

        for ($i = 0; $i < 10; $i ++) {
            $key = $faker->uuid;
            $label = $faker->words(3, true);
            $postId = $faker->randomNumber();
            $value = $faker->text();

            $dataSource = $this->_getPostListingColumnDataSourceMock($postId, $value);

            $column = new Abp01_Display_PostListing_Column($key, 
                $label, 
                $dataSource);

            $this->assertEquals($value, $column->renderValue($postId));
            $this->assertEquals($key, $column->getKey());
        }
    }

    /**
     * @return \Mockery\MockInterface|\Mockery\LegacyMockInterface|\Abp01_Display_PostListing_ColumnDataSource
     */
    private function _getWildcardPostListingColumnDataSourceMock() {
        return Mockery::spy('Abp01_Display_PostListing_ColumnDataSource');
    }

    /**
     * @return \Mockery\MockInterface|\Mockery\LegacyMockInterface|\Abp01_Display_PostListing_ColumnDataSource
     */
    private function _getPostListingColumnDataSourceMock($postId, $withValue) {
        $mock = Mockery::mock('Abp01_Display_PostListing_ColumnDataSource');
        $mock = $mock->shouldReceive('getValue')
            ->with($postId)
            ->andReturn($withValue)
            ->getMock();

        return $mock;
    }
}