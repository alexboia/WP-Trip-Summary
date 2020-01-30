<?php

/**
 * Resets various `$_SERVER` variables that can get altered during tests.
 */
function tests_reset__SERVER() {}

/**
 * For adding hooks before loading WP
 */
function tests_add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}

/**
 * Generate a random string of a given length
 * 
 * @param int The length of the string
 * 
 * @return string The generated string
 */
function rand_long_str($length) {}

/**
 * Strip leading and trailing whitespace from each line in the string
 * 
 * @param string $txt The text to process
 * 
 * @return string The processed text
 */
function strip_ws($txt) {}