<?php

use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

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

class HelpTests extends WP_UnitTestCase {
    use ExpectException;
    use GenericTestHelpers;
    use AssertStringContains;

    private static $_knownLocales = array(
        'en_US' => array(
            'minLength' => 1024,
            'test' => '<h1 id="help-root">Table of Contents</h1>'
        ),
        'ro_RO' => array(
            'minLength' => 1024,
            'test' => '<h1 id="help-root">Cuprins</h1>'
        ),
        'fr_FR' => array(
            'minLength' => 1024,
            'test' => '<h1 id="help-root">Sommaire</h1>'
        ),
        'default' => array(
            'minLength' => 1024,
            'test' => '<h1 id="help-root">Table of Contents</h1>'
        )
    );

    private static $_unknownLocales = array();

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        foreach (self::_getAvailableTranslationCodes() as $code) {
            if (!isset(self::$_knownLocales[$code])) {
                self::$_unknownLocales[] = $code;
            }
        }
    }

    public static function tearDownAfterClass(): void {
        parent::tearDownAfterClass();
        self::$_knownLocales = array();
        self::$_unknownLocales = array();
    }

    public function test_canGetHelpForLocale_knownLocale() {
        $help = new Abp01_Help();
        foreach (self::$_knownLocales as $code => $spec) {
            $content = $help->getHelpContentForLocale($code);
            $this->_assertContentMatchesSpec($content, $spec);
        }
    }

    public function test_canGetHelpForLocale_unknownLocale() {
        $help = new Abp01_Help();
        $spec = self::$_knownLocales['default'];

        foreach (self::$_unknownLocales as $code) {
            $content = $help->getHelpContentForLocale($code);
            $this->_assertContentMatchesSpec($content, $spec);
        }
    }

    public function test_canGetHelpForCurrentLocale_currentLocaleIsKnown() {
        $this->_runCanGetHelpForCurrentLocale(array_keys(self::$_knownLocales), 
            null);
    }

    public function test_canGetHelpForCurrentLocale_currentLocaleIsUnKnown() {
        $this->_runCanGetHelpForCurrentLocale(self::$_unknownLocales, 
            'default');
    }

    private function _runCanGetHelpForCurrentLocale($currentLocalePool, $specForLocale = null) {
        $currentLocale = null;
        $faker = self::_getFaker();

        $currentLocaleGenerator = function() use (&$faker, &$currentLocale, $currentLocalePool) {
            if ($currentLocale === null) {
                $currentLocale = $faker->randomElement($currentLocalePool);
            }
            return $currentLocale;
        };

        add_filter('locale', $currentLocaleGenerator);

        $help = new Abp01_Help();
        for ($i = 0; $i < 10; $i ++) {
            $spec = self::$_knownLocales[!empty($specForLocale) 
                ? $specForLocale 
                : get_locale()];

            $content = $help->getHelpContentForCurrentLocale();
            $this->_assertContentMatchesSpec($content, $spec);

            $currentLocale = null;
        }

        remove_filter('locale', $currentLocaleGenerator);
    }

    /**
     * @dataProvider emptyValuesProvider
     * @expectedException InvalidArgumentException
     */
    public function test_tryGetHelpForLocale_emptyLocale($locale) {
        $this->expectException(InvalidArgumentException::class);
        $help = new Abp01_Help();
        $help->getHelpContentForLocale($locale);
    }

    private function _assertContentMatchesSpec($content, $spec) {
        $this->assertNotEmpty($content);
        $this->assertGreaterThan($spec['minLength'], strlen($content));
        $this->assertStringContainsString($spec['test'], $content);
    }

    private static function _getAvailableTranslationCodes() {
        if (!function_exists('wp_get_available_translations')) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		}

		$translations = array();
		$systemTranslations = wp_get_available_translations();

		foreach ($systemTranslations as $tx) {
			$translations[] = $tx['language'];
		}

		return $translations;
    }
}