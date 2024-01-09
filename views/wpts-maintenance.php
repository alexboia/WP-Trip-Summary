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
	defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
	var abp01_nonce = '<?php echo $data->nonce; ?>';
	var abp01_ajaxExecuteToolAction = '<?php echo $data->ajaxExecuteToolAction; ?>';
	var abp01_ajaxBaseUrl = '<?php echo $data->ajaxUrl; ?>';
</script>

<div id="abp01-maintenance-page">
	<div id="abp01-maintenance-form-beacon"></div>	
	<h2><?php echo esc_html__('Maintenance', 'abp01-trip-summary'); ?></h2>
	<div id="abp01-tool-execution-result" class="abp01-tool-execution-result notice" style="display:none"></div>
	
	<div id="abp01-admin-maintenance-container" class="abp01-admin-maintenance-container">
		<div class="abp01-admin-maintenance-tagline">
			<h3><?php echo esc_html__('Available tools', 'abp01-trip-summary') ?></h3>
		</div>

		<div class="abp01-maintenance-tool-select-container">
			<select id="abp01-maintenance-tool-select" class="abp01-maintenance-tool-select">
				<option value=""><?php echo esc_html__('Select one', 'abp01-trip-summary') ?></option>
				<?php foreach ($data->toolsInfo as $key => $label): ?>
					<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="abp01-maintenance-tool-action-container">
			<input type="button" 
				id="abp01-execute-maintenance-tool" 
				name="abp01-execute-maintenance-tool" 
				class="button button-primary" 
				value="<?php echo esc_html__('Execute', 'abp01-trip-summary'); ?>" 
				disabled="disabled"
			/>
		</div>
	</div>

	<div id="abp01-admin-maintenance-result-container" 
			class="abp01-admin-maintenance-container" 
			style="display: none;">
		<div class="abp01-admin-maintenance-tagline">
			<h3><?php echo esc_html__('Execution result', 'abp01-trip-summary') ?></h3>

			<div id="abp01-admin-maintenance-result-container-inner" 
				class="abp01-admin-maintenance-result-container-inner">
			</div>
		</div>
	</div>

	<?php echo abp01_render_partial_view('common/wpts-progress-container.php', 
		new stdClass()); ?>
</div>