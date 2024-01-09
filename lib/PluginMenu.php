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

class Abp01_PluginMenu {
	/**
	 * @var array
	 */
	private $_menuItems = array();

	public function __construct(array $menuItems) {
		$this->_menuItems = $menuItems;
	}
	
	public function register() {
		add_action('admin_menu', array($this, 'addMenuItems'));
	}

	public function addMenuItems() {
		foreach ($this->_menuItems as $menuItem) {
			$this->_addParentMenuItem($menuItem);
		}
	}

	private function _addParentMenuItem(array $menuItem) {
		$slug = $menuItem['slug'];

		add_menu_page($menuItem['pageTitle'], 
			$menuItem['menuTitle'], 
			$menuItem['capability'], 
			$slug, 
			$menuItem['callback'], 
			!empty($menuItem['iconUrl']) 
				? $menuItem['iconUrl'] 
				: '', 
			!empty($menuItem['position']) 
				? $menuItem['position'] 
				: null);

		if (!empty($menuItem['reRegisterAsChildWithMenuTitle'])) {
			$subMenuItem = array(
				'slug' => $slug,
				'menuTitle' => $menuItem['reRegisterAsChildWithMenuTitle'],
				'pageTitle' => $menuItem['pageTitle'],
				'capability' => $menuItem['capability'],
				'callback' => $menuItem['callback']
			);

			$this->_addSubMenuItem($slug, $subMenuItem);
		}

		if (!empty($menuItem['children'])) {
			foreach ($menuItem['children'] as $subMenuItem) {
				$this->_addSubMenuItem($slug, $subMenuItem);
			}
		}
	}

	private function _addSubMenuItem($parentSlug, array $menuItem) {
		add_submenu_page($parentSlug, 
			$menuItem['pageTitle'], 
			$menuItem['menuTitle'], 
			$menuItem['capability'], 
			$menuItem['slug'], 
			$menuItem['callback'], 
			!empty($menuItem['position']) 
				? $menuItem['position'] 
				: null);
	}
}