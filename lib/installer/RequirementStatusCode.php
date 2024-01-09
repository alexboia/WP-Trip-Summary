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

class Abp01_Installer_RequirementStatusCode {
	/**
	 * @var int Status code returned when all installation requirements have been met
	 */
	const ALL_REQUIREMENTS_MET = 0;

	/**
	 * @var int Error code returned when an incompatible PHP version is detected upon installation
	 */
	const INCOMPATIBLE_PHP_VERSION = 1;

	/**
	 * @var int Error code returned when an incompatible WordPress version is detected upon installation
	 */
	const INCOMPATIBLE_WP_VERSION = 2;

	/**
	 * @var int Error code returned when LIBXML is not found
	 */
	const SUPPORT_LIBXML_NOT_FOUND = 3;

	/**
	 * @var int Error code returned when MySQL Spatial extension is not found
	 */
	const SUPPORT_MYSQL_SPATIAL_NOT_FOUND = 4;

	/**
	 * @var int Error code returned when MySqli extension is not found
	 */
	const SUPPORT_MYSQLI_NOT_FOUND = 5;

	/**
	 * @var int Error code returned when the installation capabilities cannot be detected
	 */
	const COULD_NOT_DETECT_INSTALLATION_CAPABILITIES = 255;

	public static function areAllRequirementsMet($statusCode) {
		return $statusCode === self::ALL_REQUIREMENTS_MET;
	}
}