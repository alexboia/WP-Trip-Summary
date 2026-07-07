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

	/**
	 * @var \stdClass $data
	 */
?>

<script type="text/javascript">
	var abp01_ajaxUrl = '<?php echo esc_js($data->context->ajaxBaseUrl); ?>';

	var abp01_getLookupNonce = '<?php echo esc_js($data->context->getLookupNonce); ?>';
	var abp01_addLookupNonce = '<?php echo esc_js($data->context->addLookupNonce); ?>';
	var abp01_editLookupNonce = '<?php echo esc_js($data->context->editLookupNonce); ?>';
	var abp01_deleteLookupNonce = '<?php echo esc_js($data->context->deleteLookupNonce); ?>';

	var abp01_ajaxGetLookupAction = '<?php echo esc_js($data->context->getLookupAction); ?>';
	var abp01_ajaxAddLookupAction = '<?php echo esc_js($data->context->addLookupAction); ?>';
	var abp01_ajaxEditLookupAction = '<?php echo esc_js($data->context->editLookupAction); ?>';
	var abp01_ajaxDeleteLookupAction = '<?php echo esc_js($data->context->deleteLookupAction); ?>';
</script>

<div id="abp01-admin-lookup-page" class="abp01-bootstrap abp01-page">
	<div id="abp01-admin-lookup-page-beacon"></div>	
	<h2 class="abp01-page-title"><?php echo esc_html__('Lookup data management', 'abp01-trip-summary'); ?></h2>
	
	<!-- Edit Lookup Data Item Modal -->
	<div id="abp01-edit-lookup-window" class="modal modal-lg fade abp01-modal-window" tabindex="-1" aria-labelledby="abp01-edit-lookup-window-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div id="abp01-edit-lookup-window-content" class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="abp01-edit-lookup-window-title"><?php echo esc_html__('Edit Lookup Data Item', 'abp01-trip-summary'); ?><span id="abp01-edit-lookup-window-title-extra"></span></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'abp01-trip-summary'); ?>"></button>
				</div>
				<div class="modal-body">
					<form id="abp01-edit-lookup-form">
						<input type="hidden" id="abp01-lookup-item-id" name="lookupItemId" value="0" />
						<div id="abp01-editor-action-result" class="abp01-alert-container"></div>
						<div class="mb-3">
							<label for="abp01-edit-item-default-label" class="form-label"><?php echo esc_html__('Default label', 'abp01-trip-summary'); ?> *:</label>
							<input type="text" class="form-control abp01-text-input" id="abp01-edit-item-default-label" name="defaultLabel" required="required" />
						</div>
						<div id="abp01-edit-item-translated-label-container" class="mb-3" style="display: none;">
							<label for="abp01-edit-item-translated-label" class="form-label"><?php echo esc_html__('Translated label', 'abp01-trip-summary'); ?><span class="abp01-languageDetails" rel="abp01-languageDetails"></span> *:</label>
							<input type="text" class="form-control abp01-text-input" id="abp01-edit-item-translated-label" name="translatedLabel" required="required" />
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button id="abp01-btn-save-lookup-data-item" type="button" class="btn btn-primary"><?php echo esc_html__('Save item', 'abp01-trip-summary'); ?></button>
					<button id="abp01-btn-dismiss-lookup-data-item-edit" type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'abp01-trip-summary'); ?></button>
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid px-4">
		<div class="row gx-5">
			<div class="col col-md-3 abp01-page-sidebar abp01-rounded-container">
				<h4><?php echo esc_html__('Manage for', 'abp01-trip-summary'); ?></h4>

				<div id="abp01-admin-lookup-data-menu-container" class="abp01-page-side-bar-content">
					<div class="abp01-lookup-data-mgmt-context-item-container">
						<label for="abp01-lookupTypeSelect"><?php echo esc_html__('Lookup type:', 'abp01-trip-summary'); ?></label>
						<select id="abp01-lookupTypeSelect" class="form-control form-select abp01-select abp01-lookupControl">
							<?php foreach ($data->controls->availableCategories as $key => $label): ?>
								<option value="<?php echo esc_attr($key); ?>" <?php echo $key == $data->controls->selectedCategory ? 'selected="selected"' : '' ?>><?php echo esc_html($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="abp01-lookup-data-mgmt-context-item-container-link">
						&dash; <?php echo esc_html__('and', 'abp01-trip-summary'); ?> &dash;
					</div>
					<div class="abp01-lookup-data-mgmt-context-item-container">
						<label for="abp01-lookupLangSelect"><?php echo esc_html__('Language:', 'abp01-trip-summary'); ?></label>
						<select id="abp01-lookupLangSelect" class="form-control form-select abp01-select abp01-lookupControl">
							<?php foreach ($data->controls->availableLanguages as $key => $label): ?>
								<option value="<?php echo esc_attr($key); ?>" <?php echo $key == $data->controls->selectedLanguage ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			
			<div class="col col-md-9 abp01-page-workspace">
				<div class="abp01-rounded-container abp01-page-workspace-inner">
					<h4><?php echo esc_html__('Lookup data items', 'abp01-trip-summary'); ?></h4>

					<div class="abp01-page-workspace-content">
						<div id="abp01-page-workspace-toolbar" class="abp01-page-workspace-toolbar">
							<button type="button" id="abp01-add-lookup-top"  class="btn btn-primary"><?php echo esc_html__('Add new item', 'abp01-trip-summary'); ?></button>
							<button type="button" id="abp01-reload-list-top" class="btn btn-secondary"><?php echo esc_html__('Reload list', 'abp01-trip-summary'); ?></button>
						</div>

						<div id="abp01-generic-action-result" class="abp01-alert-container"></div>

						<table id="abp01-admin-lookup-listing" class="table table-striped">
							<thead>
								<tr>
									<th width="40%"><?php echo esc_html__('Default label', 'abp01-trip-summary'); ?></th>
									<th width="40%"><?php echo esc_html__('Label', 'abp01-trip-summary'); ?></th>
									<th width="20%"><?php echo esc_html__('Actions', 'abp01-trip-summary'); ?></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
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
				<a class="btn btn-primary btn-sm" href="javascript:void(0)" rel="item-edit" data-lookupId="{{id}}"><?php echo esc_html__('Edit', 'abp01-trip-summary'); ?></a> 
				<a class="btn btn-danger btn-sm" href="javascript:void(0)" rel="item-delete" data-lookupId="{{id}}"><?php echo esc_html__('Delete', 'abp01-trip-summary'); ?></a>
			</td>
		</tr>
	{{/lookupItems}}
</script>