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
/**
 * Plugin Name: WP Trip Summary - E04 - Overriding viewer subtitle
 * Author: Alexandru Boia
 * Author URI: https://alexboia.net
 * Version: 0.0.1
 * Description: How to override the WP Trip Summary viewer subtitle using the current post's data.
 * License: Modified BSD License
 * License URI: https://github.com/alexboia/WP-Trip-Summary/blob/master/LICENSE.md
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Text Domain: abp01-trip-summary
 * Requires PHP: 8.3.0
 * Requires at least: 7.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Build a plain-text subtitle for the post represented by the viewer data.
 * Keep the incoming value when no post ID or title is available.
 *
 * @param string|null $subtitle The incoming subtitle; null by default.
 * @param stdClass $data The complete viewer data, including the current post ID.
 */
function wpts_e04_override_viewer_subtitle(?string $subtitle, stdClass $data): ?string {
	$postId = isset($data->postId) ? intval($data->postId) : 0;
	if ($postId <= 0) {
		return $subtitle;
	}

	$postTitle = get_the_title($postId);
	if ($postTitle === '') {
		return $subtitle;
	}

	// Return text, not markup: the viewer escapes the subtitle when rendering it.
	return sprintf(
		/* translators: %s: The title of the post displayed by the trip viewer. */
		__('Exploring: %s', 'abp01-trip-summary'),
		$postTitle);
}

add_filter('abp01_show_frontend_viewer_subtitle',
	'wpts_e04_override_viewer_subtitle',
	10,
	2);
