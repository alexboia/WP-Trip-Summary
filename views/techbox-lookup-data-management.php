<script type="text/javascript">
	var abp01_nonce = '<?php echo esc_js($data->context->nonce); ?>';
	var abp01_ajaxUrl = '<?php echo esc_js($data->context->ajaxBaseUrl); ?>';
	var abp01_ajaxGetLookupAction = '<?php echo esc_js($data->context->getLookupAction); ?>';
	var abp01_ajaxAddLookupAction = '<?php echo esc_js($data->context->addLookupAction); ?>';
	var abp01_ajaxEditLookupAction = '<?php echo esc_js($data->context->editLookupAction); ?>';
	var abp01_ajaxDeleteLookupAction = '<?php echo esc_js($data->context->deleteLookupAction); ?>';
</script>
<h2><?php echo __('Lookup data management', 'abp01-trip-summary'); ?></h2>
<div id="abp01-admin-lookup-page-beacon"></div>
<div id="abp01-admin-lookup-container">
	<div id="abp01-admin-lookup-control-container">
		<div class="abp01-lookupControl-item">
			<label for="abp01-lookupTypeSelect"><?php echo __('Lookup type:', 'abp01-trip-summary'); ?></label>
			<select id="abp01-lookupTypeSelect" class="abp01-lookupControl">
				<?php foreach ($data->controllers->availableTypes as $value => $label): ?>
					<option value="<?php echo esc_attr($value); ?>" <?php echo $value == $data->controllers->selectedType ? 'selected="selected"' : '' ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="abp01-lookupControl-item">
			<label for="abp01-lookupLangSelect"><?php echo __('Language:', 'abp01-trip-summary'); ?></label>
			<select id="abp01-lookupLangSelect" class="abp01-lookupControl">
				<?php foreach ($data->controllers->availableLanguages as $value => $label): ?>
					<option value="<?php echo esc_attr($value); ?>" <?php echo $value == $data->controllers->selectedLanguage ? 'selected="selected"' : ''; ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="abp01-clear"></div>
	</div>
	<div id="abp01-admin-lookup-listing-container">
		<table id="abp01-admin-lookup-listing" class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th width="50%"><?php echo __('Label', 'abp01-trip-summary'); ?></th>
					<th width="20%"><?php echo __('Language', 'abp01-trip-summary'); ?></th>
					<th width="30%"><?php echo __('Actions', 'abp01-trip-summary'); ?></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script id="tpl-abp01-lookupDataRow" type="text/x-kite">
	{{#lookupData}}
		<tr data-lookupId="{{ID}}">
			<th width="50%">{{label}}</th>
			<th width="20%">{{language}}</th>
			<th width="30%">
				
			</th>
		</tr>
	{{/lookupData}}
</script>