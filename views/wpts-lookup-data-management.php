<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
	var abp01_nonce = '<?php echo esc_js($data->context->nonce); ?>';
	var abp01_ajaxUrl = '<?php echo esc_js($data->context->ajaxBaseUrl); ?>';
	var abp01_ajaxGetLookupAction = '<?php echo esc_js($data->context->getLookupAction); ?>';
	var abp01_ajaxAddLookupAction = '<?php echo esc_js($data->context->addLookupAction); ?>';
	var abp01_ajaxEditLookupAction = '<?php echo esc_js($data->context->editLookupAction); ?>';
	var abp01_ajaxDeleteLookupAction = '<?php echo esc_js($data->context->deleteLookupAction); ?>';
</script>
<div id="abp01-admin-lookup-page">
	<h2><?php echo esc_html__('Lookup data management', 'abp01-trip-summary'); ?></h2>
	<div id="abp01-admin-lookup-page-beacon"></div>
	<div id="abp01-lookup-listing-result" class="updated settings-error abp01-lookup-listing-result" style="display:none"></div>
	<div id="abp01-admin-lookup-container">
		<div id="abp01-admin-lookup-control-container">
			<div class="abp01-lookupControl-item">
				<label for="abp01-lookupTypeSelect"><?php echo esc_html__('Lookup type:', 'abp01-trip-summary'); ?></label>
				<select id="abp01-lookupTypeSelect" class="abp01-lookupControl">
					<?php foreach ($data->controllers->availableTypes as $value => $label): ?>
						<option value="<?php echo esc_attr($value); ?>" <?php echo $value == $data->controllers->selectedType ? 'selected="selected"' : '' ?>><?php echo esc_html($label); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="abp01-lookupControl-item">
				<label for="abp01-lookupLangSelect"><?php echo esc_html__('Language:', 'abp01-trip-summary'); ?></label>
				<select id="abp01-lookupLangSelect" class="abp01-lookupControl">
					<?php foreach ($data->controllers->availableLanguages as $value => $label): ?>
						<option value="<?php echo esc_attr($value); ?>" <?php echo $value == $data->controllers->selectedLanguage ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="abp01-clear"></div>
		</div>
		
		<div id="abp01-admin-lookup-listing-container">
			<div class="abp01-lookup-general-buttons-top">
				<a id="abp01-reload-list-top" href="javascript:void(0)" class="button button-large"><?php echo esc_html__('Reload list', 'abp01-trip-summary'); ?></a>
				<a id="abp01-add-lookup-top" href="javascript:void(0)" class="button button-primary button-large"><?php echo esc_html__('Add new item', 'abp01-trip-summary'); ?></a>
			</div>
			<table id="abp01-admin-lookup-listing" class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="40%"><?php echo esc_html__('Default label', 'abp01-trip-summary'); ?></th>
						<th width="40%"><?php echo esc_html__('Label', 'abp01-trip-summary'); ?></th>
						<th width="20%"><?php echo esc_html__('Actions', 'abp01-trip-summary'); ?></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
			<div class="abp01-lookup-general-buttons-bottom">
				<a id="abp01-reload-list-bottom" href="javascript:void(0)" class="button button-large"><?php echo esc_html__('Reload list', 'abp01-trip-summary'); ?></a>
				<a id="abp01-add-lookup-bottom" href="javascript:void(0)" class="button button-primary button-large"><?php echo esc_html__('Add new item', 'abp01-trip-summary'); ?></a>				
			</div>
		</div>
	</div>

	<div id="abp01-lookup-item-form" style="display: none;">
		<div class="abp01-lookup-item-form-container">
			<div id="abp01-lookup-operation-result" class="updated settings-error abp01-lookup-operation-result" style="display:none"></div>
			<div class="abp01-lookup-item-form-fields">
				<div class="abp01-form-line">
					<label for="abp01-lookup-item-defaultLabel"><?php echo esc_html__('Default label', 'abp01-trip-summary'); ?>:</label>
					<input type="text" id="abp01-lookup-item-defaultLabel" name="defaultLabel" />
				</div>
				<div class="abp01-form-line">
					<label for="abp01-lookup-item-translatedLabel"><?php echo esc_html__('Translated label', 'abp01-trip-summary'); ?><span class="abp01-languageDetails" rel="abp01-languageDetails"></span>:</label>
					<input type="text" id="abp01-lookup-item-translatedLabel" name="translatedLabel" />
				</div>
			</div>			
			<div class="abp01-lookup-item-form-controls">
				<a id="abp01-save-lookup-item" href="javascript:void(0)" class="button button-primary button-large"><?php echo esc_html__('Save item', 'abp01-trip-summary'); ?></a>
				<a id="abp01-cancel-lookup-item" href="javascript:void(0)" class="button button-large"><?php echo esc_html__('Cancel', 'abp01-trip-summary'); ?></a>
			</div>
		</div>		
	</div>

	<div id="abp01-lookup-item-delete-form" style="display: none;">
		<div class="abp01-lookup-item-form-container">
			<div id="abp01-lookup-delete-operation-result" class="updated settings-error abp01-lookup-operation-result" style="display:none"></div>
			<div class="abp01-lookup-item-form-fields">
				<div class="abp01-form-line abp01-delete-item-warning">
					<?php echo esc_html__('Are you sure you want to delete this item? This action cannot be undone', 'abp01-trip-summary'); ?>
				</div>
				<div class="abp01-form-line">
					<label for="abp01-lookup-item-deleteOnlyLang"><?php echo esc_html__('Only delete item translation', 'abp01-trip-summary'); ?>:</label>
					<input type="checkbox" id="abp01-lookup-item-deleteOnlyLang" name="deleteOnlyLang" value="1" />
				</div>
			</div>
			<div class="abp01-lookup-item-form-controls">
				<a id="abp01-delete-lookup-item" href="javascript:void(0)" class="button button-primary button-large"><?php echo esc_html__('Confirm delete', 'abp01-trip-summary'); ?></a>
				<a id="abp01-cancel-delete-lookup-item" href="javascript:void(0)" class="button button-large"><?php echo esc_html__('Cancel', 'abp01-trip-summary'); ?></a>
			</div>
		</div>		
	</div>

	<script id="tpl-abp01-lookupDataRow" type="text/x-kite">
		{{#lookupItems}}
			<tr id="lookupItemRow-{{id}}">
				<td width="40%" rel="defaultLabelCell">{{defaultLabel|esc-html}}</td>
				<td width="40%" rel="translatedLabelCell">
					{{? hasTranslation }}
						{{label|esc-html}}
					{{^?}}
						-
					{{/?}}
				</td>
				<td width="20%">
					<a href="javascript:void(0)" rel="item-edit" data-lookupId="{{id}}"><?php echo esc_html__('Edit', 'abp01-trip-summary'); ?></a> |
					<a href="javascript:void(0)" rel="item-delete" data-lookupId="{{id}}"><?php echo esc_html__('Delete', 'abp01-trip-summary'); ?></a>
				</td>
			</tr>
		{{/lookupItems}}
	</script>
	<script id="tpl-abp01-progress-container" type="text/x-kite">
		<div id="abp01-progress-container" class="abp01-progress-container">
			<div data-role="progressLabel" id="abp01-progress-label" class="abp01-progress-label"></div>
			<div data-role="progressParent" id="abp01-progress-bar" class="abp01-progress-bar"></div>
		</div>
	</script>
</div>