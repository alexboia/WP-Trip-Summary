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
	var abp01_ajaxUrl = '<?php echo esc_js($data->context->ajaxBaseUrl); ?>';
	var abp01_getHelpAction = '<?php echo esc_js($data->context->getHelpAction); ?>';
	var abp01_getHelpNonce = '<?php echo esc_js($data->context->getHelpNonce); ?>';
</script>
<div id="abp01-help-page">
	<h2><?php echo esc_html__('WP Trip Summary Help', 'abp01-trip-summary'); ?></h2>
	<div id="abp01-help-load-result" class="abp01-help-load-result notice" style="display:none"></div>
	<div id="abp01-help-contents">
		<?php if (!empty($data->helpContents)): ?>
			<div id="abp01-help-contents-controls">
				<label for="abp01-help-contents-lang"><?php echo esc_html__('Use the selector below to change the language for displaying the help contents:', 'abp01-trip-summary'); ?></label>
				<select id="abp01-help-contents-lang" name="abp01-help-contents-lang">
					<?php foreach ($data->localesWithHelpContents as $key => $info): ?>
						<?php if ($key === $data->currentLocale): ?>
							<option value="<?php echo esc_attr($key) ?>" data-is-current="<?php echo abp01_bool2str($info['isCurrent']) ?>" selected="selected"><?php echo esc_html($info['label']) ?></option>
						<?php else: ?>
							<option value="<?php echo esc_attr($key) ?>" data-is-current="<?php echo abp01_bool2str($info['isCurrent']) ?>"><?php echo esc_html($info['label']) ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<div id="abp01-help-contents-body">
				<?php echo $data->helpContents; ?>
			</div>
		<?php else: ?>
			<div id="abp01-help-result" class="error settings-error abp01-help-result">
				<?php echo esc_html__('No help contents has been found. This usually indicates some problems with the help content files that have been distributed with the plug-in. Please check your website file structure.', 'abp01-trip-summary'); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php echo abp01_render_partial_view('common/wpts-progress-container.php', 
		new stdClass()); ?>
</div>