<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit ;
}

class Abp01_Includes {
	const JS_JQUERY = 'jquery';

	const JS_URI_JS = 'uri-js';

	const JS_JQUERY_VISIBLE = 'jquery-visible';

	const JS_JQUERY_ICHECK = 'jquery-icheck';

	const JS_JQUERY_BLOCKUI = 'jquery-blockui';

	const JS_JQUERY_TOASTR = 'jquery-toastr';

	const JS_NPROGRESS = 'nprogress';

	const JS_JQUERY_EASYTABS = 'jquery-easytabs';

	const JS_LEAFLET = 'leaflet';

	const JS_LEAFLET_MAGNIFYING_GLASS = 'leaflet-magnifyingglass';

	const JS_LEAFLET_MAGNIFYING_GLASS_BUTTON = 'leaflet-magnifyingglass-button';

	const JS_LEAFLET_FULLSCREEN = 'leaflet-fullscreen';
	
	const JS_LEAFLET_ICON_BUTTON = 'abp01-leaflet-icon-button';

	const JS_LODASH = 'lodash';

	const JS_MACHINA = 'machina';

	const JS_KITE_JS = 'kite-js';

	const JS_ABP01_MAP = 'abp01-map';

	const JS_ABP01_PROGRESS_OVERLAY = 'abp01-progress-overlay';

	const JS_ADMIN_MAIN = 'abp01-main-admin';

	const JS_FRONTEND_MAIN = 'abp01-main-frontend';

	const JS_ADMIN_SETTINGS = 'abp01-settings-admin';

	const JS_ADMIN_LOOKUP_MGMT = 'abp01-admin-lookup-management';
	
	const JS_SYSTEM_THICKBOX = 'thickbox';

	const STYLE_DASHICONS = 'dashicons';

	const STYLE_NPROGRESS = 'nprogress-css';

	const STYLE_LEAFLET = 'leaflet-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS = 'leaflet-magnifyingglass-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON = 'leaflet-magnifyingglass-button-css';

	const STYLE_LEAFLET_FULLSCREEN = 'leaflet-fullscreen-css';

	const STYLE_FRONTEND_MAIN = 'abp01-frontend-main-css';

	const STYLE_JQUERY_ICHECK = 'jquery-icheck-css';

	const STYLE_JQUERY_TOASTR = 'jquery-toastr-css';

	const STYLE_ADMIN_MAIN = 'abp01-main-css';

	const STYLE_SYSTEM_THICKBOX = 'thickbox';

	private static $_refPluginsPath;

	private static $_scriptsInFooter = false;

	private static $_scripts = array(
		self::JS_URI_JS => array(
			'path' => 'media/js/3rdParty/uri/URI.js', 
			'version' => '1.14.1'
		), 
		self::JS_JQUERY_VISIBLE => array(
			'path' => 'media/js/3rdParty/visible/jquery.visible.js', 
			'version' => '1.1.0'
		), 
		self::JS_JQUERY_ICHECK => array(
			'path' => 'media/js/3rdParty/icheck/icheck.js', 
			'version' => '1.0.2'
		), 
		self::JS_JQUERY_BLOCKUI => array(
			'path' => 'media/js/3rdParty/jquery.blockUI.js', 
			'version' => '2.66'
		), 
		self::JS_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.js', 
			'version' => '2.0.3'
		), 
		self::JS_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.js', 
			'version' => '0.2.0'
		), 
		self::JS_JQUERY_EASYTABS => array(
			'path' => 'media/js/3rdParty/easytabs/jquery.easytabs.js', 
			'version' => '3.2.0'
		), 
		self::JS_LEAFLET => array(
			'path' => 'media/js/3rdParty/leaflet/leaflet-src.js', 
			'version' => '0.7.3'
		), 
		self::JS_LEAFLET_MAGNIFYING_GLASS => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.js', 
			'version' => '0.1'
		), 
		self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.button.js', 
			'version' => '0.1'
		), 
		self::JS_LEAFLET_FULLSCREEN => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-fullscreen/leaflet.fullscreen.js', 
			'version' => '0.1'
		), 
		self::JS_LEAFLET_ICON_BUTTON => array(
			'path' => 'media/js/abp01-icon-button.js',
			'version' => '0.1'
		),
		self::JS_LODASH => array(
			'path' => 'media/js/3rdParty/lodash/lodash.js', 
			'version' => '0.3.1'
		), 
		self::JS_MACHINA => array(
			'path' => 'media/js/3rdParty/machina/machina.js', 
			'version' => '0.3.1'
		), 
		self::JS_KITE_JS => array(
			'path' => 'media/js/3rdParty/kite.js', 
			'version' => '1.0'
		), 
		self::JS_ABP01_MAP => array(
			'path' => 'media/js/abp01-map.js', 
			'version' => '0.2'
		), 
		self::JS_ABP01_PROGRESS_OVERLAY => array(
			'path' => 'media/js/abp01-progress-overlay.js', 
			'version' => '0.2'
		), 
		self::JS_ADMIN_MAIN => array(
			'path' => 'media/js/abp01-admin-main.js', 
			'version' => '0.2'
		), 
		self::JS_FRONTEND_MAIN => array(
			'path' => 'media/js/abp01-frontend-main.js', 
			'version' => '0.2'
		), 
		self::JS_ADMIN_SETTINGS => array(
			'path' => 'media/js/abp01-admin-settings.js', 
			'version' => '0.1'
		),
		self::JS_ADMIN_LOOKUP_MGMT => array(
			'path' => 'media/js/abp01-admin-lookup-management.js',
			'version' => '0.1'
		)
	);

	private static $_styles = array(
		self::STYLE_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.css', 
			'version' => '2.0.3'
		), 
		self::STYLE_LEAFLET => array(
			'path' => 'media/js/3rdParty/leaflet/leaflet.css', 
			'version' => '0.7.3'
		), 
		self::STYLE_LEAFLET_MAGNIFYING_GLASS => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.css', 
			'version' => '0.1'
		), 
		self::STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.button.css', 
			'version' => '0.1'
		), 
		self::STYLE_LEAFLET_FULLSCREEN => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-fullscreen/leaflet.fullscreen.css', 
			'version' => '0.0.4'
		), 
		self::STYLE_FRONTEND_MAIN => array(
			'path' => 'media/css/abp01-frontend-main.css', 
			'version' => '0.2'
		), 
		self::STYLE_JQUERY_ICHECK => array(
			'path' => 'media/js/3rdParty/icheck/skins/minimal/_all.css', 
			'version' => '1.0.2'
		), 
		self::STYLE_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.css', 
			'version' => '2.0.3'
		), 
		self::STYLE_ADMIN_MAIN => array(
			'path' => 'media/css/abp01-main.css', 
			'version' => '0.2'
		)
	);

	public static function setRefPluginsPath($refPluginsPath) {
		self::$_refPluginsPath = $refPluginsPath;
	}

	public static function setScriptsInFooter($scriptsInFooter) {
		self::$_scriptsInFooter = $scriptsInFooter;
	}

	private static function _enqueueScript($handle) {
		if (empty($handle)) {
			return;
		}
		if (isset(self::$_scripts[$handle])) {
			$script = self::$_scripts[$handle];
			wp_enqueue_script($handle, plugins_url($script['path'], self::$_refPluginsPath), array(), $script['version'], self::$_scriptsInFooter);
		} else {
			wp_enqueue_script($handle);
		}
	}

	private static function _enqueueStyle($handle) {
		if (empty($handle)) {
			return;
		}
		if (isset(self::$_styles[$handle])) {
			$style = self::$_styles[$handle];
			if (!isset($style['media']) || !$style['media']) {
				$style['media'] = 'all';
			}
			wp_enqueue_style($handle, plugins_url($style['path'], self::$_refPluginsPath), array(), $style['version'], $style['media']);
		} else {
			wp_enqueue_style($handle);
		}
	}

	public static function injectSettings($scriptHandle) {
		$settings = Abp01_Settings::getInstance();
		$tileLayers = $settings->getTileLayers();

		wp_localize_script($scriptHandle, 'abp01Settings', array(
			'showTeaser' => $settings->getShowTeaser() ? 'true' : 'false', 
			'mapShowFullScreen' => $settings->getShowFullScreen() ? 'true' : 'false', 
			'mapShowMagnifyingGlass' => $settings->getShowMagnifyingGlass() ? 'true' : 'false', 
			'mapAllowTrackDownloadUrl' => $settings->getAllowTrackDownload() ? 'true' : 'false',
			'mapShowScale' => $settings->getShowMapScale() ? 'true' : 'false',
			'mapTileLayer' => $tileLayers[0]
		));
	}

	public static function includeScriptJQuery() {
		self::_enqueueScript(self::JS_JQUERY);
	}

	public static function includeScriptURIJs() {
		self::_enqueueScript(self::JS_URI_JS);
	}

	public static function includeScriptJQueryVisible() {
		self::_enqueueScript(self::JS_JQUERY_VISIBLE);
	}

	public static function includeScriptJQueryICheck() {
		self::_enqueueScript(self::JS_JQUERY_ICHECK);
	}

	public static function includeScriptJQueryBlockUI() {
		self::_enqueueScript(self::JS_JQUERY_BLOCKUI);
	}

	public static function includeScriptJQueryToastr() {
		self::_enqueueScript(self::JS_JQUERY_TOASTR);
	}

	public static function includeScriptNProgress() {
		self::_enqueueScript(self::JS_NPROGRESS);
	}

	public static function includeScriptJQueryEasyTabs() {
		self::_enqueueScript(self::JS_JQUERY_EASYTABS);
	}

	public static function includeScriptLeaflet() {
		self::_enqueueScript(self::JS_LEAFLET);
	}

	public static function includeScriptLeafletMagnifyingGlass() {
		self::_enqueueScript(self::JS_LEAFLET_MAGNIFYING_GLASS);
		self::_enqueueScript(self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON);
	}

	public static function includeScriptLeafletFullscreen() {
		self::_enqueueScript(self::JS_LEAFLET_FULLSCREEN);
	}
	
	public static function includeScriptLeafletIconButton() {
		self::_enqueueScript(self::JS_LEAFLET_ICON_BUTTON);
	}

	public static function includeScriptLodash() {
		self::_enqueueScript(self::JS_LODASH);
	}

	public static function includeScriptMachina() {
		self::_enqueueScript(self::JS_MACHINA);
	}

	public static function includeScriptKiteJs() {
		self::_enqueueScript(self::JS_KITE_JS);
	}

	public static function includeScriptMap() {
		self::_enqueueScript(self::JS_ABP01_MAP);
	}

	public static function includeScriptProgressOverlay() {
		self::_enqueueScript(self::JS_ABP01_PROGRESS_OVERLAY);
	}

	public static function includeScriptAdminEditorMain() {
		self::_enqueueScript(self::JS_ADMIN_MAIN);
	}

	public static function includeScriptFrontendMain() {
		self::_enqueueScript(self::JS_FRONTEND_MAIN);
	}

	public static function includeScriptAdminSettings() {
		self::_enqueueScript(self::JS_ADMIN_SETTINGS);
	}

	public static function includeScriptAdminLookupMgmt() {
		self::_enqueueScript(self::JS_ADMIN_LOOKUP_MGMT);
	}

	public static function includeScriptSystemThickbox() {
		self::_enqueueScript(self::JS_SYSTEM_THICKBOX);
	}

	public static function includeStyleDashIcons() {
		self::_enqueueStyle(self::STYLE_DASHICONS);
	}

	public static function includeStyleNProgress() {
		self::_enqueueStyle(self::STYLE_NPROGRESS);
	}

	public static function includeStyleLeaflet() {
		self::_enqueueStyle(self::STYLE_LEAFLET);
	}

	public static function includeStyleLeafletMagnifyingGlass() {
		self::_enqueueStyle(self::STYLE_LEAFLET_MAGNIFYING_GLASS);
		self::_enqueueStyle(self::STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON);
	}

	public static function includeStyleLeafletFullScreen() {
		self::_enqueueStyle(self::STYLE_LEAFLET_FULLSCREEN);
	}

	public static function includeStyleFrontendMain() {
		self::_enqueueStyle(self::STYLE_FRONTEND_MAIN);
	}

	public static function includeStyleJQueryICheck() {
		self::_enqueueStyle(self::STYLE_JQUERY_ICHECK);
	}

	public static function includeStyleJQueryToastr() {
		self::_enqueueStyle(self::STYLE_JQUERY_TOASTR);
	}

	public static function includeStyleAdminMain() {
		self::_enqueueStyle(self::STYLE_ADMIN_MAIN);
	}

	public static function includeStyleSystemThickBox() {
		self::_enqueueStyle(self::STYLE_SYSTEM_THICKBOX);
	}
}
