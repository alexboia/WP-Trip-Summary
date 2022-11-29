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

class InputFilteringTests extends WP_UnitTestCase {
    public function test_canFilterSingleValue_cleanAndCorrectTypeInput() {
        $result = Abp01_InputFiltering::filterSingleValue(100, 'integer');
        $this->assertTrue(is_int($result));
        $this->assertEquals(100, $result);

        $result = Abp01_InputFiltering::filterSingleValue(100.45, 'float');
        $this->assertTrue(is_float($result));
        $this->assertEquals(100.45, $result);

        $result = Abp01_InputFiltering::filterSingleValue(true, 'boolean');
        $this->assertTrue(is_bool($result));
        $this->assertEquals(true, $result);

        $result = Abp01_InputFiltering::filterSingleValue(false, 'boolean');
        $this->assertTrue(is_bool($result));
        $this->assertEquals(false, $result);

        $result = Abp01_InputFiltering::filterSingleValue('test', 'string');
        $this->assertTrue(is_string($result));
        $this->assertEquals('test', $result);
    }

    public function test_canFilterSingleValue_convertMildlyBogusValues() {
        $result = Abp01_InputFiltering::filterSingleValue('200a', 'integer');
        $this->assertTrue(is_int($result));
        $this->assertEquals(200, $result);

        $result = Abp01_InputFiltering::filterSingleValue('200', 'integer');
        $this->assertTrue(is_int($result));
        $this->assertEquals(200, $result);

        $result = Abp01_InputFiltering::filterSingleValue('a100', 'integer');
        $this->assertTrue(is_int($result));
        $this->assertEquals(0, $result);

        $result = Abp01_InputFiltering::filterSingleValue('200.45', 'float');
        $this->assertTrue(is_float($result));
        $this->assertEquals(200.45, $result);

        $result = Abp01_InputFiltering::filterSingleValue('200.45abc', 'float');
        $this->assertTrue(is_float($result));
        $this->assertEquals(200.45, $result);

        $result = Abp01_InputFiltering::filterSingleValue('a200.45', 'float');
        $this->assertTrue(is_float($result));
        $this->assertEquals(0, $result);
    }

    public function test_canFilterSingleValue_canStripTags() {
        $result = Abp01_InputFiltering::filterSingleValue('<a href="test.html">Test</a>', 'string');
        $this->assertTrue(is_string($result));
        $this->assertEquals('Test', $result);

        $result = Abp01_InputFiltering::filterSingleValue('<script>1</script>', 'integer');
        $this->assertTrue(is_int($result));
        $this->assertEquals(0, $result);

        $result = Abp01_InputFiltering::filterSingleValue('<script type="text/javascript">alert("Test")</script>', 'string');
        $this->assertTrue(is_string($result));
        $this->assertEquals('', $result);

        $result = Abp01_InputFiltering::filterSingleValue('<style type="text/css">s</style>', 'string');
        $this->assertTrue(is_string($result));
        $this->assertEquals('', $result);

        $result = Abp01_InputFiltering::filterSingleValue('<p class="article">Sample paragraph</p>', 'string');
        $this->assertTrue(is_string($result));
        $this->assertEquals('Sample paragraph', $result);
    }

    public function test_canFilterSingleValue_canApplyMinLimit() {
        $result = Abp01_InputFiltering::filterSingleValue('100', 'integer', 101);
        $this->assertTrue(is_int($result));
        $this->assertEquals(101, $result);

        $result = Abp01_InputFiltering::filterSingleValue('250.45', 'float', 255.55);
        $this->assertTrue(is_float($result));
        $this->assertEquals(255.55, $result);
    }

    public function test_canFilterSingleValue_canApplyMaxLimit() {
        $result = Abp01_InputFiltering::filterSingleValue('9500', 'integer', -INF, 9000);
        $this->assertTrue(is_int($result));
        $this->assertEquals(9000, $result);

        $result = Abp01_InputFiltering::filterSingleValue('9500.55', 'float', -INF, 9000.45);
        $this->assertTrue(is_float($result));
        $this->assertEquals(9000.45, $result);
    }

    public function test_canFilterSingleValue_canApplyMinAndMaxLimits() {
        $result = Abp01_InputFiltering::filterSingleValue('150', 'integer', 200, 9000);
        $this->assertTrue(is_int($result));
        $this->assertEquals(200, $result);

        $result = Abp01_InputFiltering::filterSingleValue('9500', 'integer', 200, 9000);
        $this->assertTrue(is_int($result));
        $this->assertEquals(9000, $result);
    }

    public function test_canFilterArrayOfScalars() {
        $intValues = array('1', '2', '3', '4', '1a', '100a', '45abc', 'test');
        $result = Abp01_InputFiltering::filterValue($intValues, 'integer');
        $this->assertEquals(array(1, 2, 3, 4, 1, 100, 45, 0), $result);
        foreach ($result as $resultValue) {
            $this->assertTrue(is_int($resultValue));
        }

        $boolValues = array('1', '0', false, true, '', 0, 0.0, 1, null);
        $result = Abp01_InputFiltering::filterValue($boolValues, 'boolean');
        $this->assertEquals(array(true, false, false, true, false, false, false, true, false), $result);
        foreach ($result as $resultValue) {
            $this->assertTrue(is_bool($resultValue));
        }

        $strValues = array('clean', '<script>alert("dirty")</script>', '<script type="text/javascript">alert("dirty");</script>', '', '<a href="test.html">test</a>');
        $result = Abp01_InputFiltering::filterValue($strValues, 'string');
        $this->assertEquals(array('clean', '', '', '', 'test'), $result);
        $this->_assertArrayElementType($result, 'is_string');
    }

    public function test_canFilterObjectOfScalars_asInt() {
        $intValues = new stdClass();
        $intValues->prop1 = '1';
        $intValues->prop2 = '10bca';
        $intValues->prop3 = 'a45';
        $intValues->prop4 = 'test';

        $result = Abp01_InputFiltering::filterValue($intValues, 'integer');
        $expected = new stdClass();
        $expected->prop1 = 1;
        $expected->prop2 = 10;
        $expected->prop3 = 0;
        $expected->prop4 = 0;

        $this->assertEquals($expected, $result);
        $this->_assertObjectPropertyTypes($result, 'is_int');
    }

    public function test_canFilterObjectOfScalars_asFloat() {
        $floatValues = new stdClass();
        $floatValues->prop1 = '1.5';
        $floatValues->prop2 = '10.45bca';
        $floatValues->prop3 = 'a45.5';
        $floatValues->prop4 = 'test';
        $floatValues->prop5 = '<p>999.99</p>';

        $result = Abp01_InputFiltering::filterValue($floatValues, 'float');
        $expected = new stdClass();
        $expected->prop1 = 1.5;
        $expected->prop2 = 10.45;
        $expected->prop3 = 0.0;
        $expected->prop4 = 0.0;
        $expected->prop5 = 999.99;

        $this->assertEquals($expected, $result);
        $this->_assertObjectPropertyTypes($result, 'is_float');
    }

    public function test_canFilterObjectOfScalars_asString() {
        $strValues = new stdClass();
        $strValues->prop1 = 'clean';
        $strValues->prop2 = '<script>alert("dirty")</script>';
        $strValues->prop3 = '<script type="text/javascript">alert("dirty");</script>';
        $strValues->prop4 = '<span class="test">Test me</span>';

        $result = Abp01_InputFiltering::filterValue($strValues, 'string');
        $expected = new stdClass();
        $expected->prop1 = 'clean';
        $expected->prop2 = '';
        $expected->prop3 = '';
        $expected->prop4 = 'Test me';

        $this->assertEquals($expected, $result);
        $this->_assertObjectPropertyTypes($result, 'is_string');
    }

    public function test_canFilterArrayOfObjects_asInt() {
        $intValues1 = new stdClass();
        $intValues1->prop1 = '1';
        $intValues1->prop2 = '10bca';

        $intValues2 = new stdClass();
        $intValues2->prop1 = 'a45';
        $intValues2->prop2 = 'test';
        $intValues2->prop3 = '<p>5</p>';

        $objectValues = array($intValues1, $intValues2);
        $result = Abp01_InputFiltering::filterValue($objectValues, 'integer');

        $expectedValues1 = new stdClass();
        $expectedValues1->prop1 = 1;
        $expectedValues1->prop2 = 10;

        $expectedValues2 = new stdClass();
        $expectedValues2->prop1 = 0;
        $expectedValues2->prop2 = 0;
        $expectedValues2->prop3 = 5;

        $this->assertEquals(2, count($result));

        $this->assertEquals($expectedValues1, $result[0]);
        $this->_assertObjectPropertyTypes($result[0], 'is_int');

        $this->assertEquals($expectedValues2, $result[1]);
        $this->_assertObjectPropertyTypes($result[1], 'is_int');
    }

    private function _assertObjectPropertyTypes($object, $checkCallback) {
        foreach (get_object_vars($object) as $val) {
            $this->assertTrue(call_user_func($checkCallback, $val));
        }
    }

    private function _assertArrayElementType($array, $checkCallback) {
        foreach ($array as $val) {
            $this->assertTrue(call_user_func($checkCallback, $val));
        }
    }
}
