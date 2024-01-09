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

class Abp01_MaintenanceTool_Registry {
	/**
	 * @var Abp01_MaintenanceTool[]
	 */
	private $_tools = array();

	public function registerTool(Abp01_MaintenanceTool $tool) {
		$this->_tools[$tool->getId()] = $tool;
	}

	public function getRegisteredTools() {
		return $this->_tools;
	}

	public function getRegisteredToolsInfo() {
		$info = array();
		foreach ($this->getRegisteredTools() as $t) {
			$info[$t->getId()] = $t->getName();
		}
		return $info;
	}

	/**
	 * @param string $id 
	 * @param array $parameters 
	 * @return Abp01_MaintenanceTool_Result 
	 */
	public function executeTool($id, array $parameters = array()) {
		if (empty($id)) {
			throw new InvalidArgumentException('Tool ID may not be empty.');
		}

		if (!$this->isToolRegistered($id)) {
			throw new InvalidArgumentException('Tool with id <' . $id . '> was not registered');
		}

		$tool = $this->_tools[$id];
		return $tool->execute($parameters);
	}

	public function isToolRegistered($id) {
		return !empty($id) && isset($this->_tools[$id]);
	}
}