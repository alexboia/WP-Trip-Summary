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
?>

<?php if (!empty($data->result['directives'])): ?>
	<p><?php echo esc_html__('These rules are intended to block direct web access to the plugin storage directory and its contents. Generating or copying them does not change your server configuration.', 'abp01-trip-summary'); ?></p>

	<div id="wpts-nginx-directives-container" class="wpts-code-container wpts-nginx-directives-container">
		<div class="wpts-code-toolbar">
			<span class="wpts-code-copy-status" role="status" aria-live="polite" aria-atomic="true"></span>
			<button type="button" class="wpts-code-copy"
				aria-label="<?php echo esc_attr__('Copy code to clipboard', 'abp01-trip-summary'); ?>"
				title="<?php echo esc_attr__('Copy code to clipboard', 'abp01-trip-summary'); ?>"
				data-copy-success="<?php echo esc_attr__('Copied to clipboard.', 'abp01-trip-summary'); ?>"
				data-copy-error="<?php echo esc_attr__('Could not copy. Select the code and copy it manually.', 'abp01-trip-summary'); ?>">
				<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
			</button>
		</div>
		<pre tabindex="0" role="region" aria-label="<?php echo esc_attr__('Nginx access rules', 'abp01-trip-summary'); ?>"><code class="wpts-code"><?php echo esc_html($data->result['directives']); ?></code></pre>
	</div>

	<h4><?php echo esc_html__('How to apply these rules', 'abp01-trip-summary'); ?></h4>
	<ol>
		<li><?php echo esc_html__('Back up your Nginx configuration before editing it. If you cannot manage it yourself, send these rules to your hosting provider or server administrator.', 'abp01-trip-summary'); ?></li>
		<li>
			<?php
				/* translators: 1: Nginx server block, 2: Nginx location block, 3: Apache configuration filename. */
				echo sprintf(esc_html__('Add both rules directly inside each %1$s block that serves the storage URL, including HTTP and HTTPS where applicable. Do not put them inside another %2$s block or in a %3$s file.', 'abp01-trip-summary'),
					'<code>server { ... }</code>',
					'<code>location { ... }</code>',
					'<code>.htaccess</code>');
			?>
		</li>
		<li><?php echo esc_html__('Confirm that the paths match the public storage URL. Have your administrator check existing rules for conflicts, including duplicate or more specific locations.', 'abp01-trip-summary'); ?></li>
		<li>
			<?php
				/* translators: 1: Nginx configuration test command, 2: Nginx configuration reload command. */
				echo sprintf(esc_html__('Test the configuration with %1$s. Only if the test succeeds, apply it with %2$s or the equivalent hosting control panel action. Use the commands and permissions required by your host.', 'abp01-trip-summary'),
					'<code>nginx -t</code>',
					'<code>nginx -s reload</code>');
			?>
		</li>
		<li><?php echo esc_html__('After reloading, request the direct URL of a known existing file in the storage directory without signing in and confirm an HTTP 403 response. Also check that your website and normal plugin features still work. A successful configuration test alone does not confirm access protection.', 'abp01-trip-summary'); ?></li>
	</ol>
	<p>
		<a href="https://nginx.org/en/docs/beginners_guide.html" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('Nginx configuration documentation (opens in a new tab)', 'abp01-trip-summary'); ?></a>
	</p>

	<div class="alert alert-warning" role="note">
		<p><strong><?php echo esc_html__('Disclaimer', 'abp01-trip-summary'); ?></strong></p>
		<p><?php echo esc_html__('These rules are provided as-is and require review for your server setup. The plugin neither applies them nor verifies that access is blocked. Incorrect configuration may expose files or disrupt your website.', 'abp01-trip-summary'); ?></p>
		<p class="mb-0"><?php echo esc_html__('Protection is limited to the URL paths covered by the rules on the servers where they are applied. Custom log directories, alternate URLs, aliases and CDN copies may require separate protection. These rules do not replace access checks in the plugin.', 'abp01-trip-summary'); ?></p>
	</div>
<?php endif; ?>
