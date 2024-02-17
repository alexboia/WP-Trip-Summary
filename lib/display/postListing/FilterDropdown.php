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

class Abp01_Display_PostListing_FilterDropdown extends Abp01_Display_PostListing_Filter {
	/**
	 * @var Abp01_Display_PostListing_FilterDataSource
	 */
	private $_dataSource;

	private $_contents = null;

	public function __construct(string $key, 
			string|null $label, 
			Abp01_Display_PostListing_FilterDataSource $dataSource, 
			Abp01_Display_PostListing_FilterProcessor $processor) {
		parent::__construct($key, $label, $processor);
		$this->_dataSource = $dataSource;
	}

	public function render(): string {
		if ($this->_contents === null) {
			$this->_contents = $this->_render();
		}
		return $this->_contents;
	}

	public function getCurrentValue(): mixed {
		$currentValue = parent::getCurrentValue();
		$options = $this->getOptions();

		if (!empty($options[$currentValue])) {
			return $currentValue;
		} else {
			return null;
		}
	}

	private function _render(): string {
		$contents = '';
		$options = $this->getOptions();
		$currentValue = $this->getCurrentValue();

		if (empty($options)) {
			return '';
		}

		if (!empty($this->_label)) {
			$contents = '<label class="screen-reader-text" for="' . $this->_key . '">' . $this->_label . '</label>';
		}

		$contents .= '<select name="' . $this->_key . '" id="' . $this->_key . '">';	
		foreach ($options as $optValue => $optLabel) {
			$contents .= '<option value="' . esc_attr($optValue) . '" ' . selected($currentValue, $optValue, false) . '>';
			$contents .= esc_html($optLabel);
			$contents .= '</option>';
		}

		$contents .= '</select>';

		return $contents;
	}

	public function getOptions(): array {
		return $this->_dataSource->getOptions();
	}
}