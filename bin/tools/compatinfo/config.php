<?php
declare(strict_types=1);

use Bartlett\CompatInfo\Application\DataCollector\ErrorHandler\Collecting;
use Bartlett\CompatInfo\Application\PhpParser\Parser;
use Bartlett\CompatInfo\Application\Query\Analyser\Compatibility\GetCompatibilityHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use WpTripSummary\Tools\CompatInfo\CompatibilityQueryHandler;
use WpTripSummary\Tools\CompatInfo\ResetFileErrorsExtension;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * These CLI-only classes are outside Composer's autoload map. Load them while
 * compiling the container; the file() entries below also load them when using
 * the cached container, which does not execute this configuration again.
 * @see https://symfony.com/doc/6.4/service_container/definitions.html#requiring-files
 */
require_once __DIR__ . '/CompatibilityQueryHandler.php';
require_once __DIR__ . '/ResetFileErrorsExtension.php';

return static function(ContainerConfigurator $config): void {
	/*
	 * Load CompatInfo's standard services before applying our overrides. Symfony
	 * replaces an earlier definition when a later definition uses the same ID.
	 * @see ../../../vendor/bartlett/php-compatinfo/docs/configs/README.md
	 * @see https://symfony.com/doc/current/service_container.html#control-the-import-order
	 */
	$config->import(__DIR__ . '/../../../vendor/bartlett/php-compatinfo/config/set/default.php');
	
	$services = $config->services();
	/*
	 * Services are shared by default: the query handler and extension must receive
	 * the same Collecting instance so the extension can clear the parser's errors.
	 * @see https://symfony.com/doc/current/service_container/advanced_definitions.html#non-shared-services-getting-a-new-instance-every-time
	 */
	$services->set(Collecting::class);

	/*
	 * CompatInfo 7.2.7 constructs Collecting inside GetCompatibilityHandler. Replace
	 * that service with our handler, which passes the shared instance to parse().
	 * Keep its query.bus registration explicit; --stop-on-failure still uses Throwing.
	 * @see ../../../vendor/bartlett/php-compatinfo/src/Application/Query/Analyser/Compatibility/GetCompatibilityHandler.php
	 * @see https://symfony.com/doc/current/messenger.html#restrict-handlers-per-bus
	 */
	$services->set(GetCompatibilityHandler::class, CompatibilityQueryHandler::class)
		->file(__DIR__ . '/CompatibilityQueryHandler.php')
		->arg('$parser', service(Parser::class))
		->arg('$errorHandler', service(Collecting::class))
		->tag('messenger.message_handler', array('bus' => 'query.bus'));
		
	/*
	 * CompatInfo discovers extensions through app.extension. This one implements
	 * BeforeFileAnalysisInterface to clear the shared collection before each file,
	 * after the preceding file's errors have been copied to its own collector.
	 * @see ../../../vendor/bartlett/php-compatinfo/docs/components/extensions/README.md
	 */
	$services->set(ResetFileErrorsExtension::class)
		->file(__DIR__ . '/ResetFileErrorsExtension.php')
		->arg('$errorHandler', service(Collecting::class))
		->tag('app.extension');
};
