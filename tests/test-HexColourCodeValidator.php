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

 class HexColourCodeValidatorTests extends WP_UnitTestCase {
    public function test_tryValidateEmptyColourCode_allowEmpty() {
        $validator = new Abp01_Validate_HexColourCode(true);
        $this->assertTrue($validator->validate(''));
        $this->assertTrue($validator->validate(null));
    }

    public function test_tryValidateEmptyColourCode_disallowEmpty() {
        $validator = new Abp01_Validate_HexColourCode(false);
        $this->assertFalse($validator->validate(''));
        $this->assertFalse($validator->validate(null));
    }

    /**
     * @dataProvider _booleanValuesProvider
     */
    public function test_tryValidate_randomInvalidString($allowEmpty) {
        $numTests = 100;
        $faker = Faker\Factory::create();

        $validator = new Abp01_Validate_HexColourCode($allowEmpty);

        for ($i = 0; $i < $numTests; $i++) {
            $string = $faker->text(10);
            $this->assertFalse($validator->validate($string));
        }
    }

    /**
     * @dataProvider _booleanValuesProvider
     */
    public function test_canValidate_validHexColourCode($allowEmpty) {
        $colours = array(
            '#0184dc', '#2d04ac',
            '#8a925e', '#f3ad40',
            '#51d785', '#51d785',
            '#bc9318', '#4a2ec4',
            '#24b4d5', '#5942f9',
            '#e4deaf', '#953d89',
            '#2ba4f7', '#b61b8c',
            '#8faf26', '#e80d65',
            '#c66d97', '#2faffa',
            '#b2e4cf', '#c6749c',
            '#000000', '#ffffff'
        );

        $validator = new Abp01_Validate_HexColourCode($allowEmpty);

        foreach ($colours as $colour) {
            $this->assertTrue($validator->validate($colour));
            $this->assertTrue($validator->validate(strtoupper($colour)));
        }
    }

    public function _booleanValuesProvider() {
        return array(
            array(true),
            array(false)
        );
    }
 }