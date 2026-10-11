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