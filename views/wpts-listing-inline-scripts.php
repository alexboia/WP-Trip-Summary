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
	defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
	var abp01_auditLogNonce = '<?php echo $data->nonce; ?>';
	var abp01_auditLogAjaxAction = '<?php echo $data->ajaxAction; ?>';
	var abp01_auditLogAjaxBaseUrl = '<?php echo $data->ajaxBaseUrl; ?>';
</script>
<script id="tpl-abp01-progress-container" type="text/x-kite">
	<div id="abp01-progress-container" class="abp01-progress-container">
		<div data-role="progressLabel" id="abp01-progress-label" class="abp01-progress-label"></div>
		<div data-role="progressParent" id="abp01-progress-bar" class="abp01-progress-bar"></div>
	</div>
</script>
<script id="tpl-abp01-audit-log-container" type="text/x-kite">
	<div id="abp01-audit-log-container" class="abp01-window-container">
		<div id="abp01-audit-log-container-header" class="abp01-window-container-header">
			<h3><?php echo __('Audit log', 'abp01-trip-summary'); ?></h3>
			<a href="javascript:void(0)" class="abp01-close-window abp01-close-tile-layer-selector">
				<span class="dashicons dashicons-dismiss"></span>
			</a>
			<div class="abp01-clear"></div>
		</div>
		<div id="abp01-audit-log-container-inner" class="abp01-window-container-inner">
			{{auditLogContent}}
		</div>
	</div>
</script>