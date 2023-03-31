<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

class DefaultInstallerRequirementProviderTests extends WP_UnitTestCase {
	use GenericTestHelpers;

	public function test_canGetRequirements() {
		$provider = new Abp01_Installer_Requirement_Provider_Default($this->_getEnv());
		$requirements = $provider->getRequirements();

		$this->assertEquals(6, count($requirements));

		$this->assertTrue(is_a($requirements[0]->getRequirement(), 
			Abp01_Installer_Requirement_RequiredVersion::class));
		$this->assertEquals($requirements[0]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_PHP_VERSION);

		$this->assertTrue(is_a($requirements[1]->getRequirement(), 
			Abp01_Installer_Requirement_RequiredVersion::class));
		$this->assertEquals($requirements[1]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::INCOMPATIBLE_WP_VERSION);

		$this->assertTrue(is_a($requirements[2]->getRequirement(), 
			Abp01_Installer_Requirement_HasLibXml::class));
		$this->assertEquals($requirements[2]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::SUPPORT_LIBXML_NOT_FOUND);

		$this->assertTrue(is_a($requirements[3]->getRequirement(), 
			Abp01_Installer_Requirement_HasMysqli::class));
		$this->assertEquals($requirements[3]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQLI_NOT_FOUND);

		$this->assertTrue(is_a($requirements[4]->getRequirement(), 
			Abp01_Installer_Requirement_HasMysqlSpatialSupport::class));
		$this->assertEquals($requirements[4]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQL_SPATIAL_NOT_FOUND);

		$this->assertTrue(is_a($requirements[5]->getRequirement(), 
			Abp01_Installer_Requirement_HasRequiredMysqlSpatialFunctions::class));
		$this->assertEquals($requirements[5]->getUnsatisfiedStatusCode(), 
			Abp01_Installer_RequirementStatusCode::SUPPORT_MYSQL_SPATIAL_NOT_FOUND);
	}
}