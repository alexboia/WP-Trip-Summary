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
	if (window['abp01_postId']) {
		window['abp01_postId'] = '<?php echo $data->postId; ?>';
	}

	if (!window['abp01_imgBase']) {
		window['abp01_imgBase'] = '<?php echo esc_js($data->imgBaseUrl); ?>';
	}

	if (!window['abp01_ajaxUrl']) {
		window['abp01_ajaxUrl'] = '<?php echo esc_js($data->ajaxUrl); ?>';
	}
	
    var abp01_saveRouteLogEntryNonce = '<?php echo esc_js($data->saveRouteLogEntryNonce); ?>';
	var abp01_ajaxSaveRouteLogEntryAction = '<?php echo esc_js($data->ajaxSaveRouteLogEntryAction); ?>';

	var abp01_deleteRouteLogEntryNonce = '<?php echo esc_js($data->deleteRouteLogEntryNonce) ?>';
	var abp01_ajaxDeleteRouteLogEntryAction = '<?php echo esc_js($data->ajaxDeleteRouteLogEntryAction); ?>';

	var abp01_deleteAllRouteLogEntriesNonce = '<?php echo esc_js($data->deleteAllRouteLogEntriesNonce); ?>';
	var abp01_deleteAllRouteLogEntriesAction = '<?php echo esc_js($data->ajaxDeleteAllRouteLogEntriesAction); ?>';

	var abp01_getAdminLogEntryByIdNonce = '<?php echo esc_js($data->getAdminLogEntryByIdNonce); ?>';
	var abp01_getAdminlogEntryByIdAction = '<?php echo esc_js($data->ajaxGetAdminlogEntryByIdAction); ?>';

	if (!window['abp01_postId']) {
		window['abp01_postId'] = '<?php echo $data->postId; ?>';
	}
</script>

<div id="abp01-tripSummaryLog-adminRoot" class="hide-if-no-js">
	<div class="wpts-trip-summary-log-listingContainer">
		<table id="abp01-trip-summary-log-listingTable" 
				class="wp-list-table widefat fixed striped" 
				style="display: <?php echo $data->hasLogEntries ? 'table' : 'none'; ?>;">
			<thead>
				<tr>
					<th><?php echo esc_html__('Who', 'abp01-trip-summary') ?></th>	
					<th><?php echo esc_html__('When', 'abp01-trip-summary') ?></th>
					<th><?php echo esc_html__('Time', 'abp01-trip-summary') ?></th>
					<th><?php echo esc_html__('Vehicle', 'abp01-trip-summary') ?></th>
					<th><?php echo esc_html__('Gear', 'abp01-trip-summary') ?></th>
					<th><?php echo esc_html__('Is public', 'abp01-trip-summary') ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($data->hasLogEntries): ?>
					<?php foreach ($data->log->logEntries as $logEntry): ?>
						<tr id="abp01-trip-summary-log-listingRow-<?php echo esc_attr($logEntry->id); ?>">
							<td class="wpts-cell-rider"><?php echo !empty($logEntry->rider) 
								? esc_html($logEntry->rider) 
								: '-'; ?></td>
							<td class="wpts-cell-date"><?php echo !empty($logEntry->date) 
								? esc_html(abp01_format_db_date($logEntry->date, false)) 
								: '-'; ?></td>
							<td class="wpts-cell-timeInHours"><?php echo abp01_format_time_in_hours($logEntry->timeInHours); ?></td>
							<td class="wpts-cell-vehicle"><?php echo !empty($logEntry->vehicle) 
								? esc_html($logEntry->vehicle) 
								: '-'; ?></td>
							<td class="wpts-cell-gear"><?php echo !empty($logEntry->gear) 
								? esc_html($logEntry->gear) 
								: '-'; ?></td>
							<td class="wpts-cell-isPublic"><?php echo $logEntry->isPublic 
								? esc_html__('Yes', 'abp01-trip-summary') 
								: esc_html__('No', 'abp01-trip-summary'); ?></td>
							<td>
								<a href="javascript:void(0)" rel="logentry-item-edit" data-log-entry-id="<?php echo esc_attr($logEntry->id); ?>"><?php echo esc_html__('Edit', 'abp01-trip-summary'); ?></a> |
								<a href="javascript:void(0)" rel="logentry-item-delete" data-log-entry-id="<?php echo esc_attr($logEntry->id); ?>"><?php echo esc_html__('Delete', 'abp01-trip-summary'); ?></a>
							</td>
						</tr>
						<tr id="abp01-trip-summary-log-listingRowAux-<?php echo esc_attr($logEntry->id); ?>">
							<td colspan="6" class="wpts-cell-notes">
								<?php echo !empty($logEntry->notes) ? esc_html($logEntry->notes) : '-'; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<p class="wpts-no-log-entries" 
			id="abp01-tripSummaryLog-noLogEntries" 
			style="display: <?php echo $data->hasLogEntries ? 'none' : 'block'; ?>;">
				<?php echo esc_html__('No log entries', 'abp01-trip-summary'); ?>
		</p>
	</div>
	<div class="wpts-trip-summary-log-controls">
		<button id="abp01-addTripSummary-logEntry" 
			class="button" 
			type="button"><?php echo esc_html__('Add log entry', 'abp01-trip-summary'); ?></button>

		<button id="abp01-clearTripSummary-log" 
			class="button" 
			type="button"
			style="display: <?php echo $data->hasLogEntries ? 'inline' : 'none'; ?>;"><?php echo esc_html__('Clear all log entries', 'abp01-trip-summary'); ?></button>
	</div>
</div>

<div id="abp01-tripSummaryLog-formContainer" class="abp01-window-container" style="display: none;">
	<div id="abp01-tripSummaryLog-formContainer-header" class="abp01-window-container-header">
		<h3 id="abp01-tripSummaryLog-formTitle"><?php echo __('Add trip summary log entry', 'abp01-trip-summary'); ?></h3>
		<a href="javascript:void(0)" class="abp01-close-window abp01-close-tripSummaryLog-form">
			<span class="dashicons dashicons-dismiss"></span>
		</a>
		<div class="abp01-clear"></div>
	</div>
	<div id="abp01-tripSummaryLog-formContainer-inner" class="wpts-tripSummaryLog-form-fields">
		<div id="abp01-tripSummaryLog-form">
			<input type="hidden" id="abp01-route-log-entry-id" name="abp01_route_log_entry_id" value="0" />
			<div class="abp01-form-line">
				<label for="abp01-log-rider"><?php echo esc_html__('Who (rider)', 'abp01-trip-summary') ?>*:</label>
				<input type="text" id="abp01-log-rider" name="abp01_log_rider" class="abp01-input-text" value="<?php echo esc_attr($data->defaultRider); ?>" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-date"><?php echo esc_html__('When (date - yyyy-mm-dd)', 'abp01-trip-summary') ?>*:</label>
				<input type="text" id="abp01-log-date" name="abp01_log_date" class="abp01-input-text" value="<?php echo esc_attr($data->defaultDate); ?>" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-time"><?php echo esc_html__('Time (how many hours spent)', 'abp01-trip-summary') ?>:</label>
				<input type="text" id="abp01-log-time" name="abp01_log_time" class="abp01-input-text" value="1" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-vehicle"><?php echo esc_html__('Vehicle used (e.g. bike make and model)', 'abp01-trip-summary') ?>:</label>
				<input type="text" id="abp01-log-vehicle" name="abp01_log_vehicle" class="abp01-input-text" value="<?php echo esc_attr($data->defaultVehicle); ?>" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-gear"><?php echo esc_html__('Gear (notes about what equipment was used)', 'abp01-trip-summary') ?>:</label>
				<input type="text" id="abp01-log-gear" name="abp01_log_gear" class="abp01-input-text" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-notes"><?php echo esc_html__('Other notes', 'abp01-trip-summary') ?>:</label>
				<input type="text" id="abp01-log-notes" name="abp01_log_notes" class="abp01-input-text" />
			</div>
			<div class="abp01-form-line">
				<label for="abp01-log-is-public"><?php echo esc_html__('Display publicly', 'abp01-trip-summary') ?>:</label>
				<input type="checkbox" id="abp01-log-is-public" name="abp01_log_ispublic" value="yes" />
			</div>
		</div>
	</div>
	<div class="abp01-tripSummaryLog-form-controls">
		<a id="abp01-save-logEntry" href="javascript:void(0)" class="button button-primary button-large"><?php echo esc_html__('Save entry', 'abp01-trip-summary'); ?></a>
		<a id="abp01-cancel-logEntry" href="javascript:void(0)" class="button button-large"><?php echo esc_html__('Cancel', 'abp01-trip-summary'); ?></a>
	</div>
</div>

<script id="tpl-abp01-logEntryRow" type="text/x-kite">
	<tr id="abp01-trip-summary-log-listingRow-{{id}}">
		<td class="wpts-cell-rider">
			{{? rider }}
				{{rider|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
		<td class="wpts-cell-date">
			{{? date }}
				{{date|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
		<td class="wpts-cell-timeInHours">
			{{? timeInHours }}
				{{timeInHours|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
		<td class="wpts-cell-vehicle">
			{{? vehicle }}
				{{vehicle|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
		<td class="wpts-cell-gear">
			{{? gear }}
				{{gear|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
		<td class="wpts-cell-isPublic">
			{{? isPublic }}
				<?php echo esc_html__('Yes', 'abp01-trip-summary'); ?>
			{{^?}}
				<?php echo esc_html__('No', 'abp01-trip-summary'); ?>
			{{/?}}
		</td>
		<td>
			<a href="javascript:void(0)" rel="logentry-item-edit" data-log-entry-id="{{id}}"><?php echo esc_html__('Edit', 'abp01-trip-summary'); ?></a> |
			<a href="javascript:void(0)" rel="logentry-item-delete" data-log-entry-id="{{id}}"><?php echo esc_html__('Delete', 'abp01-trip-summary'); ?></a>
		</td>
	</tr>
	<tr id="abp01-trip-summary-log-listingRowAux-{{id}}">
		<td colspan="6" class="wpts-cell-notes">
			{{? notes }}
				{{notes|esc-html}}
			{{^?}}
				-
			{{/?}}
		</td>
	</tr>
</script>