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

</script>

<div id="abp01-system-logs-page" class="abp01-bootstrap abp01-page">
	<h2 class="abp01-page-title"><?php echo esc_html__('System logs', 'abp01-trip-summary'); ?></h2>
	<div class="container-fluid px-4">
		<div class="row gx-5">
			<div class="col col-md-3 abp01-page-sidebar abp01-rounded-container">
				<h4><?php echo esc_html__('System log files', 'abp01-trip-summary'); ?></h5>
				<div class="abp01-page-side-bar-content">
					<h5><?php echo __('Debug logs', 'abp01-trip-summary'); ?></h5>
					<div class="alert alert-primary" role="alert">
						Debug logging is currently disabled. Only showing existing debug log files.
					</div>

					<div class="alert alert-warning" role="alert">
						There are no log files available
					</div>


					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action active" aria-current="true">
							The current link item
						</a>
						<a href="#" class="list-group-item list-group-item-action">A second link item</a>
						<a href="#" class="list-group-item list-group-item-action">A third link item</a>
						<a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
						<a class="list-group-item list-group-item-action disabled" aria-disabled="true">A disabled link item</a>
					</div>

					<h5 class="error-heading"><?php echo __('Error logs', 'abp01-trip-summary'); ?></h5>
					<div class="list-group">
						<a href="#" class="list-group-item list-group-item-action active" aria-current="true">
							The current link item
						</a>
						<a href="#" class="list-group-item list-group-item-action">A second link item</a>
						<a href="#" class="list-group-item list-group-item-action">A third link item</a>
						<a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
						<a class="list-group-item list-group-item-action disabled" aria-disabled="true">A disabled link item</a>
					</div>
				</div>
			</div>
			<div class="col col-md-9 abp01-page-workspace">
				<div class="abp01-rounded-container abp01-page-workspace-inner">
					<h4><?php echo esc_html__('Log file contents', 'abp01-trip-summary'); ?></h5>
					<div class="abp01-page-workspace-content">
						<div class="abp01-page-workspace-toolbar">
							<button id="abp01-download-current-log" type="button" class="btn btn-primary abp01-log-action-btn">Download</button>
							<button id="abp01-refresh-current-log" type="button" class="btn btn-secondary abp01-log-action-btn">Reload</button>
							<button id="abp01-delete-current-log" type="button" class="btn btn-danger abp01-log-action-btn">Delete</button>
						</div>

						<div id="abp01-log-file-too-large-warning" class="alert alert-warning" role="alert">
							The log file is larger than size X, currently displaying last N lines.
						</div>

						<textarea id="abp01-log-file-contents" class="abp01-code-reading-area" readonly="readonly"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>