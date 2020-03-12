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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit ;
}

class Abp01_Includes {
	const JS_MOXIE = 'moxiejs';

	const JS_PLUPLOAD = 'plupload';
	
	const JS_JQUERY = 'jquery';

	const JS_URI_JS = 'uri-js';

	const JS_WP_COLOR_PICKER = 'wp-color-picker';

	const JS_JQUERY_VISIBLE = 'jquery-visible';

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
	
	const JS_SUMO_SELECT = 'sumoSelect';

	const STYLE_WP_COLOR_PICKER = 'wp-color-picker';

	const STYLE_DASHICONS = 'dashicons';

	const STYLE_NPROGRESS = 'nprogress-css';
	
	const STYLE_SUMO_SELECT = 'sumoSelect-css';

	const STYLE_LEAFLET = 'leaflet-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS = 'leaflet-magnifyingglass-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON = 'leaflet-magnifyingglass-button-css';

	const STYLE_LEAFLET_FULLSCREEN = 'leaflet-fullscreen-css';

	const STYLE_FRONTEND_MAIN = 'abp01-frontend-main-css';

	const STYLE_JQUERY_TOASTR = 'jquery-toastr-css';

	const STYLE_ADMIN_COMMON = 'abp01-admin-common-css';

	const STYLE_ADMIN_MAIN = 'abp01-main-css';

	const STYLE_ADMIN_SETTINGS = 'abp01-settings-css';

	const STYLE_ADMIN_LOOKUP_MANAGEMENT = 'abp01-lookup-management-css';
	
	const STYLE_ADMIN_HELP = 'abp01-help-css';

	const STYLE_ADMIN_POSTS_LISTING = 'abp01-admin-posts-listing-css';

	const STYLE_SYSTEM_THICKBOX = 'thickbox';

	const STYLE_FRONTEND_MAIN_TWENTY_TEN = 'abp01-frontend-main-twentyten-css';

	const STYLE_FRONTEND_MAIN_TWENTY_ELEVEN = 'abp01-frontend-main-twentyeleven-css';

	const STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN = 'abp01-frontend-main-twentythirteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN = 'abp01-frontend-main-twentyfourteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN = 'abp01-frontend-main-twentyfifteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN = 'abp01-frontend-main-twentysixteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN = 'abp01-frontend-main-twentyseventeen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_NINETEEN = 'abp01-frontend-main-twentynineteen-css';

	private static $_refPluginsPath;

	private static $_scriptsInFooter = false;

	private static $_scripts = array(
		self::JS_URI_JS => array(
			'path' => 'media/js/3rdParty/uri/URI.js', 
			'version' => '1.14.1'
		), 
		self::JS_JQUERY_VISIBLE => array(
			'path' => 'media/js/3rdParty/visible/jquery.visible.js', 
			'version' => '1.1.0',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_JQUERY_BLOCKUI => array(
			'path' => 'media/js/3rdParty/jquery.blockUI.js', 
			'version' => '2.66',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.js', 
			'version' => '2.1.4',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.js', 
			'version' => '0.2.0'
		), 
		self::JS_JQUERY_EASYTABS => array(
			'path' => 'media/js/3rdParty/easytabs/jquery.easytabs.js', 
			'version' => '3.2.0',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_SUMO_SELECT => array(
			'path' => 'media/js/3rdParty/summoSelect/jquery.sumoselect.js',
			'version' => '2.0.2',
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_LEAFLET => array(
			'path' => 'media/js/3rdParty/leaflet/leaflet-src.js', 
			'version' => '0.7.3'
		), 
		self::JS_LEAFLET_MAGNIFYING_GLASS => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.js', 
			'version' => '0.1',
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.button.js', 
			'version' => '0.1',
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_FULLSCREEN => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-fullscreen/leaflet.fullscreen.js', 
			'version' => '0.1',
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_ICON_BUTTON => array(
			'path' => 'media/js/abp01-icon-button.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_LEAFLET
			)
		),
		self::JS_MACHINA => array(
			'path' => 'media/js/3rdParty/machina/machina.js', 
			'version' => '0.3.1',
			'deps' => array(
				self::JS_LODASH
			)
		), 
		self::JS_KITE_JS => array(
			'path' => 'media/js/3rdParty/kite.js', 
			'version' => '1.0'
		), 

		self::JS_ABP01_MAP => array(
			'path' => 'media/js/abp01-map.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_LEAFLET,
				self::JS_JQUERY
			)
		), 
		self::JS_ABP01_PROGRESS_OVERLAY => array(
			'path' => 'media/js/abp01-progress-overlay.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_MACHINA,
				self::JS_NPROGRESS,
				self::JS_KITE_JS
			)
		), 

		self::JS_ADMIN_MAIN => array(
			'path' => 'media/js/abp01-admin-main.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_EASYTABS,
				self::JS_JQUERY_BLOCKUI,
				self::JS_JQUERY_TOASTR,
				self::JS_MOXIE,
				self::JS_PLUPLOAD,
				self::JS_SUMO_SELECT,
				self::JS_KITE_JS,
				self::JS_URI_JS,
				self::JS_LEAFLET,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_MAP
			)
		), 
		self::JS_FRONTEND_MAIN => array(
			'path' => 'media/js/abp01-frontend-main.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_EASYTABS,
				self::JS_JQUERY_VISIBLE,
				self::JS_URI_JS,
				self::JS_LEAFLET_FULLSCREEN,
				self::JS_LEAFLET_MAGNIFYING_GLASS,
				self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON,
				self::JS_LEAFLET_ICON_BUTTON,
				self::JS_ABP01_MAP
			)
		), 
		self::JS_ADMIN_SETTINGS => array(
			'path' => 'media/js/abp01-admin-settings.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_WP_COLOR_PICKER,
				self::JS_JQUERY_BLOCKUI,
				self::JS_URI_JS,
				self::JS_ABP01_PROGRESS_OVERLAY
			)
		),
		self::JS_ADMIN_LOOKUP_MGMT => array(
			'path' => 'media/js/abp01-admin-lookup-management.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_SYSTEM_THICKBOX,
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_KITE_JS,
				self::JS_URI_JS,
				self::JS_ABP01_PROGRESS_OVERLAY
			)
		)
	);

	private static $_styles = array(
		self::STYLE_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.css', 
			'version' => '0.2.0'
		), 
		self::STYLE_SUMO_SELECT => array(
			'path' => 'media/js/3rdParty/summoSelect/sumoselect.css',
			'version' => '2.0.2'
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
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_DASHICONS,
				self::STYLE_NPROGRESS,
				self::STYLE_LEAFLET,
				self::STYLE_LEAFLET_FULLSCREEN,
				self::STYLE_LEAFLET_MAGNIFYING_GLASS,
				self::STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON
			)
		), 
		self::STYLE_FRONTEND_MAIN_TWENTY_TEN => array(
			'path' => 'media/css/twentyten/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_ELEVEN => array(
			'path' => 'media/css/twentyeleven/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		), 
		self::STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN => array(
			'path' => 'media/css/twentythirteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN => array(
			'path' => 'media/css/twentyfifteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		), 
		self::STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN => array(
			'path' => 'media/css/twentyfourteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN => array(
			'path' => 'media/css/twentysixteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN => array(
			'path' => 'media/css/twentyseventeen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_NINETEEN => array(
			'path' => 'media/css/twentynineteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.css', 
			'version' => '2.1.4'
		), 
		self::STYLE_ADMIN_COMMON => array(
			'path' => 'media/css/abp01-admin-common.css', 
			'version' => ABP01_VERSION
		),
		self::STYLE_ADMIN_MAIN => array(
			'path' => 'media/css/abp01-main.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_NPROGRESS,
				self::STYLE_LEAFLET,
				self::STYLE_SUMO_SELECT,
				self::STYLE_JQUERY_TOASTR
			)
		),
		self::STYLE_ADMIN_SETTINGS => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_WP_COLOR_PICKER,
				self::STYLE_NPROGRESS
			)
		),
		self::STYLE_ADMIN_LOOKUP_MANAGEMENT => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_SYSTEM_THICKBOX,
				self::STYLE_NPROGRESS,
				self::STYLE_JQUERY_TOASTR
			)
		),
		self::STYLE_ADMIN_HELP => array(
			'path' => 'media/css/abp01-help.css', 
			'version' => ABP01_VERSION
		),
		self::STYLE_ADMIN_POSTS_LISTING => array(
			'path' => 'media/css/abp01-admin-posts-listing.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_ADMIN_COMMON
			)
		)
	);

	private static $_styleSlugsForThemeIds = array(
		'twentyten' => self::STYLE_FRONTEND_MAIN_TWENTY_TEN,
		'twentyeleven' => self::STYLE_FRONTEND_MAIN_TWENTY_ELEVEN,
		'twentyfifteen' => self::STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN,
		'twentyfourteen' => self::STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN,
		'twentythirteen' => self::STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN,
		'twentysixteen' => self::STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN,
		'twentyseventeen' => self::STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN,
		'twentynineteen' => self::STYLE_FRONTEND_MAIN_TWENTY_NINETEEN
	);

	private static function _getEnv() {
		return Abp01_Env::getInstance();
	}

	private static function _getSettings() {
		return Abp01_Settings::getInstance();
	}

	private static function _hasScript($handle) {
		return !empty(self::$_scripts[$handle]);
	}

	private static function _hasStyle($handle) {
		return !empty(self::$_styles[$handle]);
	}

	private static function _getActualElement($handle, array &$collection) {
		$script = null;
		$actual = null;

		if (isset($collection[$handle])) {
			$script = $collection[$handle];
			if (!empty($script['alias'])) {
				$handle = $script['alias'];
				$actual = isset($collection[$handle]) 
					? $collection[$handle]
					: null;
			}

			if (!empty($actual)) {
				$deps = isset($script['deps']) 
					? $script['deps'] 
					: null;
				if (!empty($deps)) {
					$actual['deps'] = $deps;
				}
			} else {
				$actual = $script;
			}
		}

		return $actual;
	}

	private static function _getActualScriptToInclude($handle) {
		return self::_getActualElement($handle, self::$_scripts);
	}

	private static function _getActualStyleToInclude($handle) {
		return self::_getActualElement($handle, self::$_styles);
	}

	private static function _ensureScriptDependencies(array $deps) {
		foreach ($deps as $depHandle) {
			if (self::_hasScript($depHandle)) {
				self::_enqueueScript($depHandle);
			}
		}
	}

	private static function _ensureStyleDependencies(array $deps) {
		foreach ($deps as $depHandle) {
			if (self::_hasStyle($depHandle)) {
				self::_enqueueStyle($depHandle);
			}
		}
	}

	private static function _enqueueScript($handle) {
		if (empty($handle)) {
			return;
		}

		if (self::_hasScript($handle)) {
			if (!wp_script_is($handle, 'registered')) {
				$script = self::_getActualScriptToInclude($handle);

				$deps = isset($script['deps']) && is_array($script['deps']) 
					? $script['deps'] 
					: array();

				if (!empty($deps)) {
					self::_ensureScriptDependencies($deps);
				}

				wp_enqueue_script($handle, 
					plugins_url($script['path'], self::$_refPluginsPath), 
					$deps, 
					$script['version'], 
					self::$_scriptsInFooter);
				
				if (isset($script['inline-setup'])) {
					wp_add_inline_script($handle, $script['inline-setup']);
				}
			} else {
				wp_enqueue_script($handle);
			}
		} else {
			wp_enqueue_script($handle);
		}
	}

	private static function _enqueueStyle($handle) {
		if (empty($handle)) {
			return;
		}

		if (self::_hasStyle($handle)) {
			$style = self::_getActualStyleToInclude($handle);
			if (!isset($style['media']) || !$style['media']) {
				$style['media'] = 'all';
			}

			$deps = isset($style['deps']) && is_array($style['deps']) 
				? $style['deps'] 
				: array();

			if (!empty($deps)) {
				self::_ensureStyleDependencies($deps);
			}

			wp_enqueue_style($handle, 
				plugins_url($style['path'], self::$_refPluginsPath), 
				$deps, 
				$style['version'], 
				$style['media']);
		} else {
			wp_enqueue_style($handle);
		}
	}

	public static function setRefPluginsPath($refPluginsPath) {
		self::$_refPluginsPath = $refPluginsPath;
	}

	public static function setScriptsInFooter($scriptsInFooter) {
		self::$_scriptsInFooter = $scriptsInFooter;
	}

	public static function injectSettings($scriptHandle) {
		$settings = self::_getSettings();
		$tileLayers = $settings->getTileLayers();
		$mainTileLayer = $tileLayers[0];

		wp_localize_script($scriptHandle, 'abp01Settings', array(
			'showTeaser' => $settings->getShowTeaser() ? 'true' : 'false', 
			'mapShowFullScreen' => $settings->getShowFullScreen() ? 'true' : 'false', 
			'mapShowMagnifyingGlass' => $settings->getShowMagnifyingGlass() ? 'true' : 'false', 
			'mapAllowTrackDownloadUrl' => $settings->getAllowTrackDownload() ? 'true' : 'false',
			'mapShowScale' => $settings->getShowMapScale() ? 'true' : 'false',
			'mapTileLayer' => array(
				'url' => esc_js($mainTileLayer->url),
				'attributionTxt' => esc_js($mainTileLayer->attributionTxt),
				'attributionUrl' => esc_js($mainTileLayer->attributionUrl)
			),
			'trackLineColour' => $settings->getTrackLineColour()
		));
	}

	public static function includeScriptAdminEditorMain($addScriptSettings, $localization) {
		self::_enqueueScript(self::JS_ADMIN_MAIN);

		if ($addScriptSettings) {
			self::injectSettings(self::JS_ADMIN_MAIN);
		}

		if (!empty($localization)) {
			wp_localize_script(self::JS_ADMIN_MAIN, 
				'abp01MainL10n', 
				$localization);
		}
	}

	public static function includeScriptFrontendMain($addScriptSettings, $localization) {
		self::_enqueueScript(self::JS_FRONTEND_MAIN);

		if ($addScriptSettings) {
			self::injectSettings(self::JS_FRONTEND_MAIN);
		}

		if (!empty($localization)) {
			wp_localize_script(self::JS_FRONTEND_MAIN, 
				'abp01FrontendL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminSettings($localization) {
		self::_enqueueScript(self::JS_ADMIN_SETTINGS);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ADMIN_SETTINGS, 
				'abp01SettingsL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminLookupMgmt($localization) {
		self::_enqueueScript(self::JS_ADMIN_LOOKUP_MGMT);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ADMIN_LOOKUP_MGMT, 
				'abp01LookupMgmtL10n', 
				$localization);
		}
	}

	public static function includeStyleFrontendMain() {
		$style = self::_getActualStyleToInclude(self::STYLE_FRONTEND_MAIN);
		$alternateLocations = self::_getEnv()->getFrontendTemplateLocations();

		$themeCssFilePath = $alternateLocations->theme . '/' . $style['path'];
		if (is_readable($themeCssFilePath)) {
			$cssPathUrl = $alternateLocations->themeUrl . '/' . $style['path'];
			if (!isset($style['media']) || !$style['media']) {
				$style['media'] = 'all';
			}
	
			$deps = isset($style['deps']) && is_array($style['deps']) 
				? $style['deps'] 
				: array();
			
			if (!empty($deps)) {
				self::_ensureStyleDependencies($deps);
			}

			wp_enqueue_style(self::STYLE_FRONTEND_MAIN, 
				$cssPathUrl, 
				$deps, 
				$style['version'], 
				$style['media']);
		} else {
			self::_enqueueStyle(self::STYLE_FRONTEND_MAIN);
			self::includeStyleFrontendMainThemeSpecificIfPresent();
		}
	}

	public static function includeStyleFrontendMainThemeSpecificIfPresent() {
		$themeId = self::_getEnv()->getCurrentThemeId();
		if (isset(self::$_styleSlugsForThemeIds[$themeId])) {
			$styleSlug = self::$_styleSlugsForThemeIds[$themeId];
			self::_enqueueStyle($styleSlug);
		}
	}

	public static function includeStyleAdminMain() {
		self::_enqueueStyle(self::STYLE_ADMIN_MAIN);
	}

	public static function includeStyleAdminLookupManagement() {
		self::_enqueueStyle(self::STYLE_ADMIN_LOOKUP_MANAGEMENT);
	}

	public static function includeStyleAdminSettings() {
		self::_enqueueStyle(self::STYLE_ADMIN_SETTINGS);
	}

	public static function includeStyleAdminHelp() {
		self::_enqueueStyle(self::STYLE_ADMIN_HELP);
	}

	public static function includeStyleAdminPostsListing() {
		self::_enqueueStyle(self::STYLE_ADMIN_POSTS_LISTING);
	}
}
