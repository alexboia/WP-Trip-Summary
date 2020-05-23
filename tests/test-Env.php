<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

class EnvTests extends WP_UnitTestCase {
	public function test_canGetInstance() {
		$instance = Abp01_Env::getInstance();
		$otherInstance = Abp01_Env::getInstance();
		
		$this->assertNotNull($instance);
		$this->assertNotNull($otherInstance);
		
		$this->assertSame($instance, $otherInstance);
	}

	public function test_canReadDbParams() {
		$env = Abp01_Env::getInstance();

		$this->assertEquals(DB_HOST, $env->getDbHost());
		$this->assertEquals(DB_USER, $env->getDbUserName());
		$this->assertEquals(DB_PASSWORD, $env->getDbPassword());
		$this->assertEquals(DB_NAME,$env->getDbName());
	}

	public function test_canReadDbTableParams() {
		$env = Abp01_Env::getInstance();
		$dbTablePrefix = $env->getDbTablePrefix();

		$this->assertEquals($GLOBALS['table_prefix'], $dbTablePrefix);
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_track', $env->getRouteTrackTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_details', $env->getRouteDetailsTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_lookup', $env->getLookupTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_lookup_lang', $env->getLookupLangTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_details_lookup', $env->getRouteDetailsLookupTableName());
	}

	public function test_canGetVersions() {
		$env = Abp01_Env::getInstance();

		$this->assertEquals(PHP_VERSION, $env->getPhpVersion());
		$this->assertEquals(get_bloginfo('version', 'raw'), $env->getWpVersion());
		$this->assertEquals('5.6.2', $env->getRequiredPhpVersion());
		$this->assertEquals('5.0', $env->getRequiredWpVersion());
		$this->assertEquals('0.2.5', $env->getVersion());
	}

	public function test_canGetDirectories() {
		$env = Abp01_Env::getInstance();
		$pluginRoot = realpath(dirname(__FILE__) . '/../');

		$this->assertEquals(wp_get_theme()->get_stylesheet_directory(), $env->getCurrentThemeDir());
		$this->assertEquals(wp_get_theme()->get_stylesheet_directory_uri(), $env->getCurrentThemeUrl());
		$this->assertEquals(wp_normalize_path(sprintf('%s/data', $pluginRoot)), $env->getDataDir());
	}

	public function test_canGetDbObject() {
		$db = Abp01_Env::getInstance()->getDb();
		$otherDb = Abp01_Env::getInstance()->getDb();

		$this->assertNotNull($db);
		$this->assertNotNull($otherDb);
		$this->assertSame($db, $otherDb);

		$this->assertInstanceOf('MysqliDb', $db);
	}

	public function test_canCheckDebugMode() {
		$this->assertEquals(WP_DEBUG, Abp01_Env::getInstance()->isDebugMode());
	}

	public function test_canGetLang() {
		$this->assertEquals(get_locale(), Abp01_Env::getInstance()->getLang());
	}
}