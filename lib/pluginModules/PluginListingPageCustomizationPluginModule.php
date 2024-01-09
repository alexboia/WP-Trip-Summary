<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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
	exit;
}

/**
 * @package WP-Trip-Summary
 */
class Abp01_PluginModules_PluginListingPageCustomizationPluginModule extends Abp01_PluginModules_PluginModule {
	public function __construct(Abp01_Env $env, Abp01_Auth $auth) {
		parent::__construct($env, $auth);
	}

	public function load() {
		add_filter('plugin_row_meta', 
			array($this, 'registerPluginRowMeta'), 
			10, 
			2);
	}

	public function registerPluginRowMeta($links, $file) {
		if ($this->_isThisPlugin($file)) {
			$links[] = 
				'<a href="' . esc_attr($this->_getSettingsPageUrl()) . '" target="_blank">' 
					. esc_html__('Settings', 'abp01-trip-summary') 
				. '</a>';
			$links[] = 
				'<a href="' . esc_attr($this->_getMaintenancePageUrl()) . '" target="_blank">' 
					. esc_html__('Maintenance', 'abp01-trip-summary') 
				. '</a>';
		}
		return $links;
	}

	private function _isThisPlugin($file) {
		return $file === plugin_basename(ABP01_PLUGIN_MAIN);
	}

	private function _getSettingsPageUrl() {
		return $this->_env->getAdminPageUrl(ABP01_MAIN_MENU_SLUG);
	}

	private function _getMaintenancePageUrl() {
		return $this->_env->getAdminPageUrl(ABP01_MAINTENANCE_SUBMENU_SLUG);
	}
}