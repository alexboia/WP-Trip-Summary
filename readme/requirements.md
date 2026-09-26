# Requirements
<a name="wpts-requirements"></a>  

## For running the plug-in itself

1. PHP version 8.0.0 or greater;
2. MySQL version 5.7 or greater (with spatial support);
3. WordPress 6.0.0 or greater;
4. libxml extension;
5. SimpleXml extension;
6. mysqli extension;
7. mbstring - not strictly required, but recommended;
8. zlib - not strictly required, but recommended.

## For development

All of the above, with the following amendments:

1. xdebug extension is recommended;
2. Composer development dependencies installed with `composer install`, including PHPUnit, for running the tests;
3. wp (wp-cli) version 2.x installed and available in your $PATH, for initializing the test environment, if needed
4. phpcompatinfo version 5.x installed and available in your $PATH, for generating the compatibility information files
5. cygwin, for Windows users (or Windows Linux Subsystem, in which case it pretty much works out of the box), such as myself, for setting up the development environment, running unit tests and the build scripts, with the following requirements itself:
   - wget command;
   - curl command;
   - gettext libraries;
   - php core engine and the above-mentioned php extensions;
   - mysql command line client;
   - subversion command line client;
   - zip command.

## Running tests from PowerShell

The test bootstrap uses `WP_TESTS_DIR` when set, otherwise `/tmp/wordpress-tests-lib`
when present, then the WordPress test library installed by Composer in
`vendor/wp-phpunit/wp-phpunit`. This library is separate from the WordPress core
installation needed to run the tests.

Set `WP_CORE_DIR` to the WordPress core installation to use for testing. Use a
WordPress version matching the `wp-phpunit/wp-phpunit` version in `composer.lock`.
Without this variable, the test configuration looks for `wordpress` under PHP's
temporary directory.

Configure the test database connection in `tests/wp-tests-config.php` before
running the suite. Use a dedicated test database: WordPress recreates its test
tables during setup. The bootstrap loads this configuration directly.

From the plugin directory:

```powershell
composer install
$env:WP_CORE_DIR = 'D:\path\to\wordpress'
.\vendor\bin\phpunit -c .\phpunit.xml
```
