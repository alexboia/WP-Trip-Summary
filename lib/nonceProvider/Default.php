<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

class Abp01_NonceProvider_Default implements Abp01_NonceProvider {
    private $_nonceUrlParam = null;

    private $_actionCode = null;

    public function __construct($actionCode, $nonceUrlParam = 'abp01_nonce') {
        if (empty($actionCode)) {
            throw new InvalidArgumentException('Nonce action code may not be empty.');
        }

        if (empty($nonceUrlParam)) {
            throw new InvalidArgumentException('Nonce URL parameter name may not be empty.');
        }

        $this->_actionCode = $actionCode . '_nonce';
        $this->_nonceUrlParam = $nonceUrlParam;
    }

    public function generateNonce() {
        $resourceId = func_num_args() == 1 
            ? func_get_arg(0) 
            : '';
            
        return wp_create_nonce($this->_getResourceScopedActionCode($resourceId));
    }

    private function _getResourceScopedActionCode($resourceId) {
        return !empty($resourceId) 
            ? $this->_actionCode . ':' . $resourceId 
            : $this->_actionCode;
    }

    public function valdidateNonce() {
        $resourceId = func_num_args() == 1 
            ? func_get_arg(0) 
            : '';

        return check_ajax_referer($this->_getResourceScopedActionCode($resourceId), 
            $this->_nonceUrlParam, 
            false);
    }

    public function hasNonce() {
        return !empty($_GET[$this->_nonceUrlParam]);
    }
}