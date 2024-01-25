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

	/** @var stdClass $data */
?>

<div class="wpts-log-container">
	<?php if ($data->hasLogEntries): ?>
		<div class="wpts-log-entries-container">
			<?php $i = 1; ?>
			<?php foreach ($data->log->logEntries as $logEntry): ?>
				<div class="wpts-log-entry">
					<h4 class="wpts-log-entry-title">#<?php echo $i ++ ?> - <?php echo abp01_format_db_date($logEntry->date, false); ?></h4>
					<div class="wpts-log-entry-meta-container">
						<span class="wpts-log-entry-meta wpts-log-entry-rider"><span class="dashicons dashicons-buddicons-activity"></span> <?php echo esc_html($logEntry->rider); ?></span>
						<span class="wpts-log-entry-meta wpts-log-entry-vehicle"><span class="dashicons dashicons-share-alt"></span> <?php echo esc_html($logEntry->vehicle); ?></span>
						<?php if (!empty($logEntry->timeInHours)): ?>
							<span class="wpts-log-entry-meta wpts-log-entry-timeInHours"><span class="dashicons dashicons-clock"></span> <?php echo abp01_format_time_in_hours($logEntry->timeInHours); ?></span>
						<?php endif; ?>
						<?php if (!empty($logEntry->gear)): ?>
							<span class="wpts-log-entry-meta wpts-log-entry-gear"><span class="dashicons dashicons-cart"></span> <?php echo esc_html($logEntry->gear); ?></span>
						<?php endif; ?>
					</div>
					<?php if (!empty($logEntry->notes)): ?>
						<div class="wpts-log-entry-notes">
							<?php echo esc_html($logEntry->notes); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<div class="wpts-no-log-entries">
			<?php echo esc_html__('There are no log entries yet', 'abp01-trip-summary'); ?>
		</div>
	<?php endif; ?>
</div>