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

<?php if (!empty($data->result['posts'])): ?>
	<table id="abp01-admin-missing-tracks-posts" class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th width="20%"><?php echo esc_html__('ID', 'abp01-trip-summary'); ?></th>
				<th width="50%"><?php echo esc_html__('Title', 'abp01-trip-summary'); ?></th>
				<th width="30%"><?php echo esc_html__('Actions', 'abp01-trip-summary'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data->result['posts'] as $p): ?>
				<tr>
					<td><?php echo esc_html($p['id']) ?></td>
					<td><?php echo esc_html($p['title']) ?></td>
					<td>
						<a href="<?php echo esc_attr($p['permalink_url']) ?>" target="_blank"><?php echo esc_html__('[View]', 'abp01-trip-summary'); ?></a>
						<a href="<?php echo esc_attr($p['edit_url']) ?>" target="_blank"><?php echo esc_html__('[Edit]', 'abp01-trip-summary'); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php echo esc_html__('No posts with missing track files found!', 'abp01-trip-summary'); ?></p>
<?php endif; ?>