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

class Abp01_Installer_Step_Update_UpdateTo021 implements Abp01_Installer_Step_Update_Interface {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	public function execute() { 
		$result = true;

		//1. Ensure storage directories
		if ($this->_ensureStorageDirectories()) {
			//2. Copy files if needed
			if (!$this->_moveTrackDataFiles()) {
				$result = false;
			}

			//3. Update track file paths in db.
			//  Since the plug-in update can be performed either via manual upload 
			//  or through WP's interface, the files can be moved 
			//  either by the plug-in or by the user.
			// Thus, fixing the routes path in the database is actually 
			//  independent of the process of moving the data files.
			if (!$this->_fixRoutePathsInDb()) {
				$result = false;
			}
		} else {
			$result = false;
		}

		return $result;
	}

	private function _ensureStorageDirectories() {
		$rootStorageDir = $this->_env->getRootStorageDir();
		$tracksStorageDir = $this->_env->getTracksStorageDir();
		$cacheStorageDir = $this->_env->getCacheStorageDir();
		$logStorageDir = $this->_env->getLogStorageDir();

		$service = new Abp01_Installer_Service_CreateStorageDirectories($rootStorageDir, 
			$tracksStorageDir, 
			$cacheStorageDir, 
			$logStorageDir);

		return $service->execute();
	}

	private function _moveTrackDataFiles() {
		$service = new Abp01_Installer_Service_MoveTrackDataFilesFromLegacyDirectories($this->_env);
		return $service->execute();
	}

	private function _fixRoutePathsInDb() {
		$service = new Abp01_Installer_Service_FixLegacyRoutePathsInDb($this->_env);
		return $service->execute();
	}

	public function getLastError() { 
		return null;
	}

	public function getTargetVersion() {
		return '0.2.1';
	}
}