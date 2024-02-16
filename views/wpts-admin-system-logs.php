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
	var abp01_getLogFileNonce = '<?php echo esc_js($data->getLogFileNonce); ?>';
	var abp01_ajaxGetLogFileAction = '<?php echo esc_js($data->ajaxGetLogFileAction); ?>';
	var abp01_downloadLogFileNonce = '<?php echo esc_js($data->downloadLogFileNonce); ?>';
	var abp01_ajaxDownloadLogFileAction = '<?php echo esc_js($data->ajaxDownloadLogFileAction); ?>';
	var abp01_ajaxDeleteLogFileAction = '<?php echo esc_js($data->ajaxDeleteLogFileAction); ?>';
	var abp01_deleteLogFileNonce = '<?php echo esc_js($data->deleteLogFileNonce); ?>';
	var abp01_ajaxBaseUrl = '<?php echo esc_js($data->ajaxUrl); ?>';
</script>

<div id="abp01-system-logs-page" class="abp01-bootstrap abp01-page">
	<h2 class="abp01-page-title"><?php echo esc_html__('System logs', 'abp01-trip-summary'); ?></h2>
	<div class="container-fluid px-4">
		<div class="row gx-5">
			<div class="col col-md-3 abp01-page-sidebar abp01-rounded-container">
				<h4><?php echo esc_html__('System log files', 'abp01-trip-summary'); ?></h5>
				<div id="abp01-log-file-lists-container" class="abp01-page-side-bar-content">
					<h5><?php echo __('Debug logs', 'abp01-trip-summary'); ?></h5>

					<?php if (!$data->isDebugLoggingEnabled): ?>
						<div class="alert alert-primary" role="alert">
							<?php echo esc_html__('Debug logging is currently disabled. Only showing existing debug log files.', 'abp01-trip-summary'); ?>
						</div>
					<?php endif; ?>

					<div id="abp01-no-debug-log-files-found" class="alert alert-warning" role="alert" style="<?php echo $data->hasDebugLogFiles ? 'display:none;' : ''; ?>">
						<?php echo esc_html__('There are no debug log files available', 'abp01-trip-summary'); ?>
					</div>

					<?php if ($data->hasDebugLogFiles): ?>
						<div class="list-group">
							<?php foreach ($data->debugLogFiles as $key => $df): ?>
								<?php $isSelected = $key == 0 ?>
								<a href="javascript:void (0)" data-file-type="debug-log" data-file-id="<?php echo esc_attr($df->id); ?>" class="list-group-item list-group-item-action <?php echo $isSelected ? 'active' : ''; ?>" <?php echo $isSelected ? 'aria-current="true"' : ''; ?>>
									<span><strong><?php echo esc_html($df->fileName) ?></strong> (<?php echo esc_html($df->fomattedLastModified); ?>)</span>
									<span class="badge bg-primary rounded-pill"><?php echo esc_html($df->formattedSize); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h5 class="error-heading"><?php echo __('Error logs', 'abp01-trip-summary'); ?></h5>

					<?php if (!$data->isErrorLoggingEnabled): ?>
						<div class="alert alert-primary" role="alert">
							<?php echo esc_html__('Error logging is currently disabled. Only showing existing error log files.', 'abp01-trip-summary'); ?>
						</div>
					<?php endif; ?>

					<div id="abp01-no-error-log-files-found" class="alert alert-warning" role="alert" style="<?php echo $data->hasErrorLogFiles ? 'display:none;' : ''; ?>">
						<?php echo esc_html__('There are no error log files available', 'abp01-trip-summary'); ?>
					</div>

					<?php if ($data->hasErrorLogFiles): ?>
						<div class="list-group">
							<?php foreach ($data->errorLogFiles as $key => $errf): ?>
								<?php $isSelected = !$data->hasDebugLogFiles && $key == 0 ?>
								<a href="javascript:void (0)" data-file-type="error-log" data-file-id="<?php echo esc_attr($errf->id); ?>" class="list-group-item list-group-item-action <?php echo $isSelected ? 'active' : ''; ?>" <?php echo $isSelected ? 'aria-current="true"' : ''; ?>>
									<span><strong><?php echo esc_html($errf->fileName) ?></strong> (<?php echo esc_html($errf->fomattedLastModified); ?>)</span>
									<span class="badge bg-primary rounded-pill"><?php echo esc_html($errf->formattedSize); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="col col-md-9 abp01-page-workspace">
				<div class="abp01-rounded-container abp01-page-workspace-inner">
					<h4><?php echo esc_html__('Log file contents', 'abp01-trip-summary'); ?></h5>
					<div class="abp01-page-workspace-content">
						<div id="abp01-page-workspace-toolbar" class="abp01-page-workspace-toolbar">
							<?php if ($data->hasErrorLogFiles || $data->hasDebugLogFiles): ?>
								<button id="abp01-download-current-log" type="button" class="btn btn-primary abp01-log-action-btn"><span class="dashicons dashicons-download"></span> <?php echo esc_html__('Download', 'abp01-trip-summary'); ?></button>
								<button id="abp01-refresh-current-log" type="button" class="btn btn-secondary abp01-log-action-btn"><span class="dashicons dashicons-image-rotate"></span> <?php echo esc_html__('Reload', 'abp01-trip-summary'); ?></button>
								<button id="abp01-delete-current-log" type="button" class="btn btn-danger abp01-log-action-btn"><span class="dashicons dashicons-trash"></span> <?php echo esc_html__('Delete', 'abp01-trip-summary'); ?></button>
							<?php endif; ?>
						</div>

						<div id="abp01-log-action-result" class="abp01-alert-container"></div>

						<div id="abp01-log-file-too-large-warning" class="alert alert-warning" role="alert" style="display: none;">
							<?php echo esc_html__('The log file is too large. Only displaying the last 200 lines.', 'abp01-trip-summary'); ?>
						</div>

						<textarea id="abp01-log-file-contents" class="abp01-code-reading-area" readonly="readonly"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>