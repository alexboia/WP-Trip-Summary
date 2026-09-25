<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

declare(strict_types = 1);

namespace WpTripSummary\MaintenanceTool {

	if (!defined('ABP01_LOADED')) {
		exit ;
	}

    use Abp01_MaintenanceTool;
    use Abp01_MaintenanceTool_Result;
    use Override;
    use WpTripSummary\Env;
    use WpTripSummary\Io\NginxAccessDirectives;

	class NginxAccessDirectivesHelper implements Abp01_MaintenanceTool {
		private Env $_env;

		public function __construct(Env $env) {
			$this->_env = $env;
		}

		#[Override]
		public function execute(array $parameters = array()): Abp01_MaintenanceTool_Result{
			$url = $this->_getRootStorageUrlPath();
			return new Abp01_MaintenanceTool_Result(true, array(
				'directives' => NginxAccessDirectives::generateDenyAccessRules($url)
			));
		}

		private function _getRootStorageUrlPath(): string {
			return $this->_env->getRootStorageUrl(true);
		}

		#[Override]
		public function getId(): string {
			return 'nginx-access-directives-helper';
		}

		#[Override]
		public function getName(): string {
			return __('Nginx Access Directives Helper', 'abp01-trip-summary') 
				?? 'Nginx Access Directives Helper';
		}

	}
}