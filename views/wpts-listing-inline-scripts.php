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
	defined('ABP01_LOADED') or die;

	/**
	 * @var \Abp01_ViewModel_SimpleScriptsInfoVm $data
	 */
?>

<script type="text/javascript">
	var abp01_auditLogNonce = '<?php echo esc_js($data->nonce); ?>';
	var abp01_auditLogAjaxAction = '<?php echo esc_js($data->ajaxAction); ?>';
	var abp01_auditLogAjaxBaseUrl = '<?php echo esc_js($data->ajaxBaseUrl); ?>';
</script>

<div id="abp01-listing-audit-log-container" class="abp01-bootstrap abp01-bootstrap-host">
	<div id="abp01-listing-audit-log-window" class="modal modal-lg fade abp01-modal-window" tabindex="-1" aria-labelledby="abp01-listing-audit-log-window-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div id="abp01-listing-audit-log-window-content" class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="abp01-listing-audit-log-window-title"><?php echo esc_html__('Audit log', 'abp01-trip-summary'); ?><span id="abp01-listing-audit-log-window-title-extra"></span></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'abp01-trip-summary'); ?>"></button>
				</div>
				<div class="modal-body">
					<div id="abp01-audit-log-container-inner">
						<!-- Content set by script -->
					</div>
				</div>
				<div class="modal-footer">
					<button id="abp01-btn-close-listing-audit-log-window" type="button" class="btn btn-danger"><span class="dashicons dashicons-no"></span> <?php echo esc_html__('Close', 'abp01-trip-summary'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>