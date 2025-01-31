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

class ViewerTests extends WP_UnitTestCase {
    use GenericTestHelpers;

    public function test_canGetAvailableTabs() {
        $availableTabs = Abp01_Viewer::getAvailableTabs();

        $this->assertNotEmpty($availableTabs);
        $this->assertEquals(3, count($availableTabs));
        $this->assertArrayHasKey(Abp01_Viewer::TAB_INFO, $availableTabs);
        $this->assertArrayHasKey(Abp01_Viewer::TAB_MAP, $availableTabs);
    }

    public function test_canCheckIfTabIsSupport_validTabName() {
        foreach (Abp01_Viewer::getAvailableTabs() as $tab => $label) {
            $this->assertTrue(Abp01_Viewer::isTabSupported($tab));
        }
    }

    public function test_tryCheckIfTabIsSupport_invalidTabName() {
        $faker = $this->_getFaker();
        $validTabs = array_keys(Abp01_Viewer::getAvailableTabs());

        for ($i = 0; $i < 10; $i ++) {
            $invalidTab = $faker->randomAscii;
            while (in_array($invalidTab, $validTabs)) {
                $invalidTab = $faker->randomAscii;
            }

            $this->assertFalse(Abp01_Viewer::isTabSupported($invalidTab));
        }
    }
}