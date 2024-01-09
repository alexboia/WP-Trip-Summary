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

class Abp01_PluginMenuItemCollector {
	private $_menuItems = array();

	public function collectMenuItems(array $menuItems) {
		foreach ($menuItems as $menuItem) {
			$this->_ensureMenuItemValidOrThrow($menuItem);

			$slug = $menuItem['slug'];
			$parentSlug = !empty($menuItem['parent']) 
				? $menuItem['parent'] 
				: null;

			if (!empty($parentSlug)) {
				$this->_collectChildMenuItem($parentSlug, $menuItem);
			} else {
				$this->_collectParentMenuItem($slug, $menuItem);
			}
		}
	}

	private function _ensureMenuItemValidOrThrow(array $menuItem) {
		if (empty($menuItem['slug'])) {
			throw new Abp01_Exception('Menu item must have a non-empty slug');
		}

		if (empty($menuItem['callback']) || !is_callable($menuItem['callback'])) {
			throw new Abp01_Exception('Menu item must have a non-empty, valid callback');
		}

		if (empty($menuItem['menuTitle'])) {
			throw new Abp01_Exception('Menu item must have a non-empty menu title');
		}

		if (empty($menuItem['pageTitle'])) {
			throw new Abp01_Exception('Menu item must have a non-empty page title');
		}

		if (empty($menuItem['capability'])) {
			throw new Abp01_Exception('Menu item must have a non-empty capability');
		}
		
		if (!empty($menuItem['parent']) && !empty($menuItem['reRegisterAsChildWithMenuTitle'])) {
			throw new Abp01_Exception('A child menu item cannot register itself as a child menu item');
		}
	}

	private function _collectChildMenuItem($parentSlug, array $menuItem) {
		//if the parent menu item has not yet been defined,
		//  define a placeholder, so that we have where to store
		//  the sub-menu item association
		if (!isset($this->_menuItems[$parentSlug])) {
			$this->_menuItems[$parentSlug] = array(
				'children' => array()
			);
		}

		$this->_menuItems[$parentSlug]['children'][] = $menuItem;
	}

	private function _collectParentMenuItem($slug, array $menuItem) {
		//if there is an entry for the top-level menu item
		//  it means that it is a placeholder, in which case
		//  we need to override it, whilst keeping the sub-menu items
		if (isset($this->_menuItems[$slug])) {
			$children = $this->_menuItems[$slug]['children'];
		} else {
			$children = array();
		}

		$this->_menuItems[$slug] = array_merge($menuItem, array(
			'children' => $children
		));
	}

	public function validateCollectedMenuItems() {
		foreach ($this->_menuItems as $parentMenuItem) {
			$this->_ensureMenuItemValidOrThrow($parentMenuItem);
		}
	}

	public function getCollectedMenuItems() {
		$this->validateCollectedMenuItems();
		return $this->_menuItems;
	}

	public function reset() {
		return $this->_menuItems;
	}
}