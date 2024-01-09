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

<div id="abp01-about-page">
	<h2><?php echo esc_html__('About WP Trip Summary', 'abp01-trip-summary'); ?></h2>

	<div id="abp01-about-summary">
		<div id="abp01-about-logo">
			<div id="abp01-about-logo-inner">
				<img src="<?php echo esc_attr($data->pluginLogoPath); ?>" class="abp01-about-logo" />
			</div>
		</div>
		<div id="abp01-about-info">
			<table id="abp01-about-info-listing" class="wp-list-table widefat fixed striped">
				<tbody>
					<tr>
						<th scope="row"><?php echo esc_html__('Current Version', 'abp01-trip-summary'); ?></th>
						<td><?php echo esc_html($data->pluginData['Version']); ?> / <a target="_blank" href="https://www.wikipedia.org/search-redirect.php?family=wikipedia&language=en&search=<?php echo esc_attr($data->pluginData['WPTS Version Name']); ?>&language=en"><?php echo esc_html($data->pluginData['WPTS Version Name']); ?></a></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('License', 'abp01-trip-summary') ?></th>
						<td><a href="<?php echo esc_attr($data->pluginData['License URI']) ?>" target="_blank"><?php echo esc_html($data->pluginData['License']); ?></a></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Author', 'abp01-trip-summary'); ?></th>
						<td>
							<a href="<?php echo esc_attr($data->pluginData['AuthorURI']); ?>" target="_blank"><?php echo esc_html($data->pluginData['AuthorName']); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Minimum WordPress Version', 'abp01-trip-summary'); ?></th>
						<td><?php echo esc_html($data->pluginData['RequiresWP']); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Your WordPress Version', 'abp01-trip-summary'); ?></th>
						<td><?php echo esc_html($data->envData['CurrentWP']) ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Minimum PHP Version', 'abp01-trip-summary'); ?></th>
						<td><?php echo esc_html($data->pluginData['RequiresPHP']); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Your PHP Version', 'abp01-trip-summary') ?></th>
						<td><?php echo esc_html($data->envData['CurrentPHP']) ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Project source', 'abp01-trip-summary'); ?></th>
						<td><a href="<?php echo esc_attr($data->pluginData['PluginURI']); ?>" target="_blank"><?php echo esc_html__('Github', 'abp01-trip-summary'); ?></a></td>
					</tr>
				</tbody>
			</table>
			<div id="abp01-about-actions">
				<a id="abp01-about-get-support" href="https://wordpress.org/support/plugin/wp-trip-summary/" 
					target="_blank" 
					class="button button-large"><?php echo esc_html__('Get Support', 'abp01-trip-summary') ?></a>
				<a id="abp01-about-action-supportme" href="https://ko-fi.com/Q5Q01KGLM" 
					target="_blank"
					class="button button-large"><?php echo esc_html__('Buy me a coffee :)', 'abp01-trip-summary'); ?></a>
			</div>
		</div>
		<div class="abp01-clear"></div>
	</div>

	<div id="abp01-about-changelog">
		<h3><?php echo esc_html__('Changelog', 'abp01-trip-summary'); ?></h3>
		<?php foreach ($data->changelog as $version => $items): ?>
			<div class="abp01-about-changelog-version">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<th><?php echo esc_html($version); ?></th>
					</thead>
					<tbody>
						<?php foreach ($items as $item): ?>
							<tr>
								<td><?php echo esc_html($item); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endforeach; ?>
	</div>
</div>