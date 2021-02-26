<?php
/**
 * Defines a basic fixture to run multiple tests.
 *
 * Resets the state of the WordPress installation before and after every test.
 *
 * Includes utility functions and assertions useful for testing WordPress.
 *
 * All WordPress unit tests should inherit from this class.
 */
class WP_UnitTestCase extends PHPUnit_Framework_TestCase { 
    public function __construct() {}
    
    /**
	 * Fetches the factory object for generating WordPress fixtures.
	 *
	 * @return WP_UnitTest_Factory The fixture factory.
	 */
    protected static function factory() {}

    /**
     * Asserts that the given variable is a WP Error. 
     * 
     * @param mixed $actual The value to test
     * @param string $message
     */
    public function assertWPError($actual, $message = '') {}

    /**
     * Asserts that the given variable is not a WP Error
     * 
     * @param mixed $actual The value to thest
     * @param string $message
     */
    public function assertNotWPError($actual, $message = '') {}

    /**
     * Asserts that the given variable is an IXR Error
     * 
     * @param mixed $actual The value to test
     * @param string $message
     */
    public function assertIXRError($actual, $message = '') {}

    /**
     * Asserts that the given variable is not an IXR Error
     * 
     * @param mixed $actual The value to test
     * @param string $message
     */
    public function assertNotIXRError($actual, $message = '') {}

    /**
     * Asserts that a given object's specified fields are equal to the given values.
     * 
     * @param object $object The object to test
     * @param array $fields The fields to be tested (as array's keys) and the values to test for (as array's values)
     */
    public function assertEqualFields($object, $fields) {}

    /**
     * Asserts the two values are equal, after discarding their whitespaces
     * 
     * @param string $expected The expected value
     * @param string $actual The actual value
     */
    public function assertDiscardWhitespace($expected, $actual) {}

    /**
	 * Asserts that the given variable is a multidimensional array, and that all arrays are non-empty.
	 *
	 * @param array $array
	 */
    public function assertNonEmptyMultidimensionalArray($array) {}

    /**
	 * Asserts that a condition is not false.
	 *
	 * @param bool   $condition
	 * @param string $message
	 *
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
    public static function assertNotFalse($condition, $message = '') {}

    /**
	 * Modify WordPress's query internals as if a given URL has been requested.
	 *
	 * @param string $url The URL for the request.
	 */
    public function go_to($url) {}

    /**
	 * Check each of the WP_Query is_* functions/properties against expected boolean value.
	 *
	 * Any properties that are listed by name as parameters will be expected to be true; all others are
	 * expected to be false. For example, assertQueryTrue('is_single', 'is_feed') means is_single()
	 * and is_feed() must be true and everything else must be false to pass.
	 *
	 * @param string $prop,... Any number of WP_Query properties that are expected to be true for the current request.
	 */
    public function assertQueryTrue(/* ... */) {}

    /**
     * Remove all the files in the WordPress upload directory
     * 
     * @return void
     */
    public function remove_added_uploads() {}

    /**
     * Multisite-agnostic way to delete a user from the database.
     * 
     * @param int $user_id The identifier of the user that must be removed
     * 
     * @return boolean True when finished.
     */
    public static function delete_user($user_id) {}

    /**
	 * Update a post's modification date.
     * There's no way to change post_modified through WP functions.
     * 
     * @param int $post_id The identifier of the post to update
     * @param string $date The new post modification date
     * 
     * @return int|boolean The number of rows updated, or false on error.
	 */
    protected function update_post_modified($post_id, $date) {}
}