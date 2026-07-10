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
	 * @var \Abp01_ViewModel_PostAuditLogVm $data
	 */
?>

<?php do_action('abp01_before_trip_summary_audit_log', 
	$data->postId, 
	$data); ?>

<table class="abp01-admin-trip-summary-audit-log">
	<tbody>
		<tr class="abp01-trip-summary-audit-log-section-title">
			<td colspan="2"><h4><?php echo esc_html__('Trip summary info', 'abp01-trip-summary'); ?></h4></td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Date created', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasInfoCreatedAt()
					? esc_html(abp01_format_db_date($data->auditLogData->getInfoCreatedAt()))
					: '-'; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Date last updated', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasInfoLastModifiedAt()
					? esc_html(abp01_format_db_date($data->auditLogData->getInfoLastModifiedAt()))
					: '-'; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Last updated by', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasInfoLastModifiedByUserName()
					? esc_html($data->auditLogData->getInfoLastModifiedByUserName())
					: '-'; ?>
			</td>
		</tr>

		<?php do_action('abp01_after_trip_summary_info_audit_log', 
			$data->postId, 
			$data); ?>

		<tr class="abp01-trip-summary-audit-log-section-title">
			<td colspan="2"><h4><?php echo esc_html__('Trip summary track', 'abp01-trip-summary'); ?></h4></td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Date created', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasTrackCreatedAt()
					? esc_html(abp01_format_db_date($data->auditLogData->getTrackCreatedAt()))
					: '-'; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Date last updated', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasTrackLastModifiedAt()
					? esc_html(abp01_format_db_date($data->auditLogData->getTrackLastModifiedAt()))
					: '-'; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__('Last updated by', 'abp01-trip-summary'); ?></th>
			<td><?php 
				echo $data->auditLogData->hasTrackLastModifiedByUserName() 
					? esc_html($data->auditLogData->getTrackLastModifiedByUserName())
					: '-'; ?>
			</td>
		</tr>

		<?php do_action('abp01_after_trip_summary_track_audit_log', 
			$data->postId, 
			$data); ?>
	</tbody>
</table>

<?php do_action('abp01_after_trip_summary_audit_log', 
	$data->postId, 
	$data); ?>