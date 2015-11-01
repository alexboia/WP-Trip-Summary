<?php
class EnvTests extends WP_UnitTestCase {
	public function testCanGetInstance() {
		$instance = Abp01_Env::getInstance();
		$otherInstance = Abp01_Env::getInstance();
		
		$this->assertNotNull($instance);
		$this->assertNotNull($otherInstance);
		
		$this->assertSame($instance, $otherInstance);
	}

	public function testCanReadDbParams() {
		$env = Abp01_Env::getInstance();

		$this->assertEquals(DB_HOST, $env->getDbHost());
		$this->assertEquals(DB_USER, $env->getDbUserName());
		$this->assertEquals(DB_PASSWORD, $env->getDbPassword());
		$this->assertEquals(DB_NAME,$env->getDbName());
	}

	public function testCanReadDbTableParams() {
		$env = Abp01_Env::getInstance();
		$dbTablePrefix = $env->getDbTablePrefix();

		$this->assertEquals($GLOBALS['table_prefix'], $dbTablePrefix);
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_track', $env->getRouteTrackTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_details', $env->getRouteDetailsTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_lookup', $env->getLookupTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_lookup_lang', $env->getLookupLangTableName());
		$this->assertEquals($dbTablePrefix . 'abp01_techbox_route_details_lookup', $env->getRouteDetailsLookupTableName());
	}

	public function testCanGetVersions() {
		$env = Abp01_Env::getInstance();

		$this->assertEquals(PHP_VERSION, $env->getPhpVersion());
		$this->assertEquals(get_bloginfo('version', 'raw'), $env->getWpVersion());
		$this->assertEquals('5.2.4', $env->getRequiredPhpVersion());
		$this->assertEquals('4.0', $env->getRequiredWpVersion());
		$this->assertEquals('0.2b', $env->getVersion());
	}

	public function testCanGetDirectories() {
		$env = Abp01_Env::getInstance();
		$pluginRoot = realpath(dirname(__FILE__) . '/../');

		$this->assertEquals(wp_get_theme()->get_stylesheet_directory(), $env->getCurrentThemeDir());
		$this->assertEquals(wp_get_theme()->get_stylesheet_directory_uri(), $env->getCurrentThemeUrl());
		$this->assertEquals(wp_normalize_path(sprintf('%s/data', $pluginRoot)), $env->getDataDir());
	}

	public function testCanGetDbObject() {
		$db = Abp01_Env::getInstance()->getDb();
		$otherDb = Abp01_Env::getInstance()->getDb();

		$this->assertNotNull($db);
		$this->assertNotNull($otherDb);
		$this->assertSame($db, $otherDb);

		$this->assertInstanceOf('MysqliDb', $db);
	}

	public function testCanCheckDebugMode() {
		$this->assertEquals(WP_DEBUG, Abp01_Env::getInstance()->isDebugMode());
	}

	public function testCanGetLang() {
		$this->assertEquals(get_locale(), Abp01_Env::getInstance()->getLang());
	}
}