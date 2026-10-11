<?php
/**
 * Plugin Name: WP Trip Summary - E03 - Custom frontend viewer tabs
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.0.1
 * Description: How to add frontend viewer tabs with icons, text and images to WP Trip Summary.
 * License: New BSD License
 * License URI: https://github.com/alexboia/WP-Trip-Summary/blob/master/LICENSE.md
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Text Domain: abp01-trip-summary
 * Requires PHP: 8.0.0
 * Requires at least: 6.0.0
 */

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

if (!defined('ABSPATH')) {
	exit;
}

// Override in wp-config.php, or edit this default: -1 = all, 0 = none, N = first N tabs.
if (!defined('WPTS_E03_TAB_COUNT')) {
	define('WPTS_E03_TAB_COUNT', -1);
}

/**
 * A small, ordered data source shared by every post in this example.
 * Replace this array with your own data source when adapting the example.
 * The keys are unique viewer tab IDs; label and icon are consumed by the viewer.
 * The remaining fields belong to this example's content renderer.
 *
 * @return array<string, array<string, string>>
 */
function wpts_e03_get_tabs_data_source(): array {
	return array(
		'wpts-e03-tab-overview' => array(
			'label' => __('Overview', 'abp01-trip-summary'),
			'icon' => 'dashicons-info-outline',
			'title' => __('A day in the mountains', 'abp01-trip-summary'),
			'text' => __('This sample trip follows a quiet valley before climbing to an open ridge. Leave time for a picnic and a few stops to enjoy the scenery.', 'abp01-trip-summary'),
			'image' => 'mountains.svg',
			'imageAlt' => __('Illustration of mountain peaks under a sunny sky.', 'abp01-trip-summary')
		),
		'wpts-e03-tab-access' => array(
			'label' => __('Getting there', 'abp01-trip-summary'),
			'icon' => 'dashicons-location',
			'title' => __('Start at the trailhead', 'abp01-trip-summary'),
			'text' => __('The example route begins at the edge of the village. Follow the winding path towards the hills and use the signpost as a meeting point.', 'abp01-trip-summary'),
			'image' => 'trailhead.svg',
			'imageAlt' => __('Illustration of a signpost beside a winding trail.', 'abp01-trip-summary')
		),
		'wpts-e03-tab-highlights' => array(
			'label' => __('Highlights', 'abp01-trip-summary'),
			'icon' => 'dashicons-camera',
			'title' => __('Pause by the lake', 'abp01-trip-summary'),
			'text' => __('A small lake makes a pleasant halfway stop on this imaginary itinerary. Look for the reflection of the surrounding peaks and take a photograph before continuing.', 'abp01-trip-summary'),
			'image' => 'lake.svg',
			'imageAlt' => __('Illustration of a blue lake surrounded by mountains.', 'abp01-trip-summary')
		),
		'wpts-e03-tab-equipment' => array(
			'label' => __('Equipment', 'abp01-trip-summary'),
			'icon' => 'dashicons-admin-tools',
			'title' => __('Pack for the day', 'abp01-trip-summary'),
			'text' => __('Use this tab for a short packing list: a water bottle, a snack, a waterproof layer and a map. Replace this sample text with equipment notes for your own trip.', 'abp01-trip-summary'),
			'image' => 'backpack.svg',
			'imageAlt' => __('Illustration of a hiking backpack and a water bottle.', 'abp01-trip-summary')
		),
		'wpts-e03-tab-notes' => array(
			'label' => __('Field notes', 'abp01-trip-summary'),
			'icon' => 'dashicons-flag',
			'title' => __('Remember the little things', 'abp01-trip-summary'),
			'text' => __('Finish the example with a few personal impressions: the quiet forest, the view from the ridge and a favourite rest stop. This final tab also helps exercise a crowded viewer navigation bar.', 'abp01-trip-summary'),
			'image' => 'camp.svg',
			'imageAlt' => __('Illustration of a tent in a clearing beneath the evening sky.', 'abp01-trip-summary')
		)
	);
}

/**
 * Keep associative keys intact: the viewer uses them to connect tabs to panels.
 * Counts above the source size simply select all available entries.
 *
 * @return array<string, array<string, string>>
 */
function wpts_e03_get_selected_tabs(): array {
	$tabCount = (int) WPTS_E03_TAB_COUNT;
	if ($tabCount === 0 || $tabCount < -1) {
		return array();
	}

	$tabs = wpts_e03_get_tabs_data_source();
	return $tabCount === -1
		? $tabs
		: array_slice($tabs, 0, $tabCount, true);
}

function wpts_e03_add_frontend_viewer_tabs(array $additionalTabs, ?int $postId): array {
	// $postId is null in settings, where all available tab choices are requested.
	// This example offers the same selected tabs in settings and on every post.
	// Preserve tabs registered by other modules, including any conflicting IDs.
	return $additionalTabs + wpts_e03_get_selected_tabs();
}

function wpts_e03_render_frontend_viewer_tab_content(string $tabId, array $tabInfo, stdClass $viewerData): void {
	$tabs = wpts_e03_get_selected_tabs();
	if (!isset($tabs[$tabId])) {
		return;
	}

	// $viewerData->postId is available here for content specific to the current post.
	$tab = $tabs[$tabId];
	$imageUrl = plugins_url('assets/' . $tab['image'], __FILE__);
	?>
	<div class="abp01-e03-tab-content">
		<h3><?php echo esc_html($tab['title']); ?></h3>
		<p><?php echo esc_html($tab['text']); ?></p>
		<img src="<?php echo esc_url($imageUrl); ?>"
			alt="<?php echo esc_attr($tab['imageAlt']); ?>"
			width="800" height="400" loading="lazy" decoding="async" />
	</div>
	<?php
}

function wpts_e03_enqueue_frontend_styles(): void {
	if (!is_singular() || empty(wpts_e03_get_selected_tabs())) {
		return;
	}

	// Dashicons must also be available to visitors who are not logged in.
	wp_enqueue_style('dashicons');
	wp_enqueue_style('wpts-e03-viewer-tabs',
		plugins_url('assets/tabs.css', __FILE__),
		array(),
		'0.0.1');
}

// Register immediately so settings and frontend requests both see the tabs.
// No WP Trip Summary classes are needed, so plugin load order does not matter.
add_filter('abp01_additional_frontend_viewer_tabs',
	'wpts_e03_add_frontend_viewer_tabs',
	10,
	2);

add_action('abp01_additional_frontend_viewer_tab_content',
	'wpts_e03_render_frontend_viewer_tab_content',
	10,
	3);

add_action('wp_enqueue_scripts', 'wpts_e03_enqueue_frontend_styles');
