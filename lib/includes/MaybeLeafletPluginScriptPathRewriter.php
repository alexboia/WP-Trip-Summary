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

class Abp01_Includes_MaybeLeafletPluginScriptPathRewriter implements Abp01_Includes_PathRewriter {
    public function needsRewriting(array $item) {
        return $this->_isLeafletPluginItem($item) 
            && $this->_rewriteRequested($item);
    }

    private function _isLeafletPluginItem(array $item) {
        return isset($item['is-leaflet-plugin'])  
            && $item['is-leaflet-plugin'] === true;
    }

    public function _rewriteRequested(array $item) {
        return isset($item['needs-wrap']) 
            && $item['needs-wrap'] === true;
    }

    public function rewritePath(array $item) {
        if ($this->needsRewriting($item)) {
            $scriptPath = self::_urlRewriteEnabled()
                ? $item['path']
                : 'abp01-plugin-leaflet-plugins-wrapper.php?load=' . $item['path'];
        } else {
            $scriptPath = $item['path'];
        }

        return $scriptPath;
    }

    private static function _urlRewriteEnabled() {
		return abp01_is_url_rewrite_enabled();
	}
}