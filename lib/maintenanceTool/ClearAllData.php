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

class Abp01_MaintenanceTool_ClearAllData implements Abp01_MaintenanceTool {
	/**
	 * @var Abp01_MaintenanceTool_Helper_Files
	 */
	private $_filesHelper;

	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	public function __construct(Abp01_Route_Manager $routeManager, Abp01_Env $env) {
		$this->_routeManager = $routeManager;
		$this->_filesHelper = new Abp01_MaintenanceTool_Helper_Files($env);
	}

    public function execute(array $parameters = array()) { 
		$this->_clearDbData();
		$this->_clearAllFiles();
		return new Abp01_MaintenanceTool_Result(true);
	}

	private function _clearDbData() {
		$this->_routeManager->clearAll();
	}

	private function _clearAllFiles() {
		$this->_filesHelper->clearCacheFiles();
		$this->_filesHelper->clearTrackFiles();
	}

    public function getName() { 
		return __('Clear all trip summary related data (cannot be undone)', 'abp01-trip-summary');
	}

	public function getId() {
		return 'clear-all-data';
	}
}