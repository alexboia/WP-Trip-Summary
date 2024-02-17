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
	exit ;
}

class Abp01_Display_PostListing_ColumnCustomization implements Abp01_Display_PostListing_Customization {
	/**
	 * @var Abp01_Display_PostListing_Column[]
	 */
	private $_columns = array();

	private $_forPostTypes = array();

	/**
	 * @param Abp01_Display_PostListing_Column[] $columns 
	 * @param string[] $forPostTypes 
	 * @return void 
	 */
	public function __construct(array $columns, array $forPostTypes) {
		$this->_columns = array();
		foreach ($columns as $c) {
			$this->_columns[$c->getKey()] = $c;
		}

		$this->_forPostTypes = $forPostTypes;
	}

	public function apply() {
		//TODO: obey post types returned by Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes()
		add_filter('manage_posts_columns', 
			array($this, 'registerPostListingColumns'), 
			10, 2);
		add_filter('manage_pages_columns', 
			array($this, 'registerPageListingColumns'), 
			10, 1);

		add_action('manage_posts_custom_column', 
			array($this, 'getPostListingCustomColumnValue'), 
			10, 2);
		add_action('manage_pages_custom_column', 
			array($this, 'getPostListingCustomColumnValue'),
			10, 2);
	}

	public function registerPostListingColumns($existingColumns, $postType) {
		if ($this->_shouldAddColumnsForPostTypeListing($postType)) {
			$existingColumns = $this->_addCustomColumns($existingColumns);
		}
		return $existingColumns;
	}

	private function _shouldAddColumnsForPostTypeListing($postType) {
		return in_array($postType, $this->_forPostTypes);
	}

	private function _addCustomColumns($existingColumns) {
		foreach ($this->_columns as $key => $column) {
			$existingColumns[$key] = $column->renderLabel();
		}
		return $existingColumns;
	}

	public function registerPageListingColumns($existingColumns) {
		if ($this->_shouldAddColumnsForPageListing()) {
			$existingColumns = $this->_addCustomColumns($existingColumns);
		}
		return $existingColumns;
	}

	private function _shouldAddColumnsForPageListing() {
		return $this->_shouldAddColumnsForPostTypeListing('page');
	}

	public function getPostListingCustomColumnValue($columnKey, $postId) {
		if (isset($this->_columns[$columnKey])) {
			$column = $this->_columns[$columnKey];
			echo $column->renderValue($postId);
		}
	}
}