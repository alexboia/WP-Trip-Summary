<?php
/**
 * Plugin Name: WP Trip Summary - E02 - Customize lookup type labels
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.0.1
 * Description: How to customize WP Trip Summary editor lookup type labels
 * License: New BSD License
 * License URI: https://github.com/alexboia/WP-Trip-Summary/blob/master/LICENSE.md
 * Plugin URI: https://github.com/alexboia/WP-Trip-Summary
 * Requires PHP: 7.4.0
 * Requires at least: 6.0.0
 */

function e02_get_customized_lookup_type_label($existingLabel, $lookupType) {
	$returnLabel = $existingLabel;
	$returnLabel = sprintf('%s (MODIFIED)', $returnLabel);

	return $returnLabel;
}

function e02_init() {
	add_filter('abp01_get_lookup_type_label', 
		'e02_get_customized_lookup_type_label', 
		10, 
		2);
}

e02_init();