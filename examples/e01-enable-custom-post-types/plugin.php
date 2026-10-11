<?php
/**
 * Plugin Name: WP Trip Summary - E01 - Enable WPTS for custom post types
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.0.1
 * Description: How to enable WP Trip Summary editor for custom post types (other than 'page' and 'post')
 * License: New BSD License
 * License URI: https://github.com/alexboia/WP-Trip-Summary/blob/master/LICENSE.md
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Requires PHP: 7.4.0
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

function e01_register_sample_custom_post_type() {
	register_post_type('e01_sample_post_type', array(
		'labels' => array(
			'name' => __('E01 Sample Post Type'),
			'singular_name' => __('E01 Sample Post Type')
		),
		'public' => true,
		'has_archive' => false,
		'rewrite' => array(
			'slug' => 'e01_posts'
		),
		'show_in_rest' => false
	));
}

function e01_get_trip_summary_available_for_post_types($postTypes) {
	//Of course, you could also remove 
	//	an existing post type over here

	$newPostTypes = $postTypes;
	if (!in_array('e01_sample_post_type', $newPostTypes)) {
		$newPostTypes[] = 'e01_sample_post_type';
	}

	return $newPostTypes;
}

function e01_init() {
	e01_register_sample_custom_post_type();
	
	add_filter('abp01_trip_summary_available_for_post_types', 
		'e01_get_trip_summary_available_for_post_types', 
		1);
}

add_action('init', 'e01_init');