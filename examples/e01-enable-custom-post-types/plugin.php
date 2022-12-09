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