<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

class Abp01_Installer_Requirement_Provider_Default implements Abp01_Installer_Requirement_Provider {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	/**
	 * @return Abp01_Installer_Requirement_Descriptor[] 
	 */
	public function getRequirements() {
		$env = $this->_env;
		return array(
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_RequiredVersion($env->getPhpVersion(), $env->getRequiredPhpVersion()),
				Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_PHP_VERSION
			),
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_RequiredVersion($env->getWpVersion(), $env->getRequiredWpVersion()),
				Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_WP_VERSION
			),
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_HasLibXml(),
				Abp01_Installer_RequirementStatusCode::SUPPORT_LIBXML_NOT_FOUND
			),
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_HasMysqli(),
				Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQLI_NOT_FOUND
			),
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_HasMysqlSpatialSupport($this->_env),
				Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQL_SPATIAL_NOT_FOUND
			),
			new Abp01_Installer_Requirement_Descriptor(
				new Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions($this->_env),
				Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQL_SPATIAL_NOT_FOUND
			)
		);
	}
}