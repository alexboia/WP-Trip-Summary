<?php
declare(strict_types=1);

namespace WpTripSummary\Tools\CompatInfo {

	use Bartlett\CompatInfo\Application\DataCollector\ErrorHandler\Collecting;
	use Bartlett\CompatInfo\Application\DataCollector\ErrorHandler\Throwing;
	use Bartlett\CompatInfo\Application\PhpParser\Parser;
	use Bartlett\CompatInfo\Application\Profiler\Profile;
	use Bartlett\CompatInfo\Application\Query\Analyser\Compatibility\GetCompatibilityQuery;
	use Bartlett\CompatInfo\Application\Query\QueryHandlerInterface;
	use Bartlett\CompatInfo\Application\Service\SourceProvider;
	use RuntimeException;

	/**
	 * Replace the upstream query handler's inline construction of Collecting with
	 * a shared service. Keep source discovery and stop-on-failure behavior intact.
	 */
	final class CompatibilityQueryHandler implements QueryHandlerInterface {
		private Parser $_parser;

		private Collecting $_errorHandler;

		public function __construct(Parser $parser, Collecting $errorHandler) {
			$this->_parser = $parser;
			$this->_errorHandler = $errorHandler;
		}

		public function __invoke(GetCompatibilityQuery $query): Profile {
			$source = realpath($query->getSource());
			if ($source === false) {
				throw new RuntimeException(sprintf('No data source match (%s)', $query->getSource()));
			}

			$this->_errorHandler->clearErrors();
			$errorHandler = $query->isStopOnFailure() ? new Throwing() : $this->_errorHandler;
			$finder = SourceProvider::getFinder($source, $query->getExclude());

			return $this->_parser->parse($source, $finder, $errorHandler, $query->getVersion());
		}
	}
}
