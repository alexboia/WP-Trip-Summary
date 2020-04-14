<?php

/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
if ( defined( 'WP_RUN_CORE_TESTS' ) && constant('WP_RUN_CORE_TESTS') == true ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/build/' );
} else {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

/*
 * Path to the theme to test with.
 *
 * The 'default' theme is symlinked from test/phpunit/data/themedir1/default into
 * the themes directory of the WordPress installation defined above.
 */
define( 'WP_DEFAULT_THEME', 'default' );

/*
 * Test with multisite enabled.
 * Alternatively, use the tests/phpunit/multisite.xml configuration file.
 */
// define( 'WP_TESTS_MULTISITE', true );

/*
 * Force known bugs to be run.
 * Tests with an associated Trac ticket that is still open are normally skipped.
 */
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode (default).
define( 'WP_DEBUG', true );

// ** MySQL settings ** //

/*
 * This configuration file will be used by the copy of WordPress being tested.
 * wordpress/wp-config.php will be ignored.
 *
 * WARNING WARNING WARNING!
 * These tests will DROP ALL TABLES in the database with the prefix named below.
 * DO NOT use a production database or one that is shared with something else.
 */

define( 'DB_NAME', 'rlived89_abnet_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 */
define('AUTH_KEY',         'Ka-%X!DSHeJfIw6!3:uv^GV]j5#PdJ>mV=Q!Dn`EV0+g8]$0PaO4EGjM9?Y):jHx');
define('SECURE_AUTH_KEY',  '&84WgX}U1BTv~6&:{@PnSgsR_jLU2!-5>zXnP=mL)[?tz~UOE+E{Z-fH8SIiuR^o');
define('LOGGED_IN_KEY',    'D?K+De`Rl[!U>n+RXK^hw.52&ipC]~T= -+OBK~[PcF{B]8V|k<Ri,NK+kCu};#7');
define('NONCE_KEY',        'r-W^/x];J_g -l]lFCtIkf?$~}A+&onii}lCOA[9r+B%yLDHVEF}I.5.U|g2uLo-');
define('AUTH_SALT',        'gqwKe45|P^,4?tE=N2DYBj-2vV6CV<l-?0 4_-R-A9OH5#G1J~z|6ocL|Lgq;_^y');
define('SECURE_AUTH_SALT', 'qXnn^b1<R#{Iupp<GnV<pC1AT2?Pf[fq9A:hukh)6[6*e#6}8WAhR+{]U!ZyZ|%J');
define('LOGGED_IN_SALT',   'E6-Yc.,qer9]#[7Wr..U&OZ|F[xsgs@2l:SwT&Z]L<Yl#~0Dvt(B0Aj(XYrLmi|A');
define('NONCE_SALT',       'cf|W03V#x~>peAWS|x$(E0vtIawhDxij9E4&`%g$_NUZ~SutZq ZbP@r10j|GHPN');

$table_prefix = 'wptests_';   // Only numbers, letters, and underscores please!

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
