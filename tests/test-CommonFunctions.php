<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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
}