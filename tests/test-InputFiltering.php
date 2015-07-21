<?php
class InputFilteringTests extends WP_UnitTestCase {
	public function testCanFilterSingleValue_cleanAndCorrectTypeInput() {
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
	
	public function testCanFilterSingleValue_convertMildlyBogusValues() {
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
	
	public function testCanFilterSingleValue_canStripTags() {
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
	
	public function testCanFilterSingleValue_canApplyMinLimit() {
		$result = Abp01_InputFiltering::filterSingleValue('100', 'integer', 101);
		$this->assertTrue(is_int($result));
		$this->assertEquals(101, $result);
		
		$result = Abp01_InputFiltering::filterSingleValue('250.45', 'float', 255.55);
		$this->assertTrue(is_float($result));
		$this->assertEquals(255.55, $result);
	}
	
	public function testCanFilterSingleValue_canApplyMaxLimit() {
		$result = Abp01_InputFiltering::filterSingleValue('9500', 'integer', -INF, 9000);
		$this->assertTrue(is_int($result));
		$this->assertEquals(9000, $result);
		
		$result = Abp01_InputFiltering::filterSingleValue('9500.55', 'float', -INF, 9000.45);
		$this->assertTrue(is_float($result));
		$this->assertEquals(9000.45, $result);
	}
	
	public function testCanFilterSingleValue_canApplyMinAndMaxLimits() {
		$result = Abp01_InputFiltering::filterSingleValue('150', 'integer', 200, 9000);
		$this->assertTrue(is_int($result));
		$this->assertEquals(200, $result);
		
		$result = Abp01_InputFiltering::filterSingleValue('9500', 'integer', 200, 9000);
		$this->assertTrue(is_int($result));
		$this->assertEquals(9000, $result);
	}
	
	public function testCanFilterArrayOfScalars() {
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
		foreach ($result as $resultValue) {
			$this->assertTrue(is_string($resultValue));
		}
	}

	public function testCanFilterObjectOfScalars_asInt() {
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
		foreach (get_object_vars($result) as $val) {
			$this->assertTrue(is_int($val));
		}
	}
	
	public function testCanFilterObjectOfScalars_asFloat() {
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
		foreach (get_object_vars($result) as $val) {
			$this->assertTrue(is_float($val));
		}
	}
	
	public function testCanFilterObjectOfScalars_asString() {
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
		foreach (get_object_vars($result) as $val) {
			$this->assertTrue(is_string($val));
		}
	}
	
	public function testCanFilterArrayOfObjects_asInt() {
		
	}
}
