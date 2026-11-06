<?php
declare(strict_types=1);

namespace WpTripSummary\Tools\CompatInfo {

	use Bartlett\CompatInfo\Application\DataCollector\ErrorHandler\Collecting;
	use Bartlett\CompatInfo\Application\Event\BeforeFileAnalysisEvent;
	use Bartlett\CompatInfo\Application\Event\BeforeFileAnalysisInterface;
	use Bartlett\CompatInfo\Application\Extension\ExtensionInterface;

	/**
	 * Work around PHP CompatInfo 7.2.7 retaining errors between files.
	 *
	 * CompatibilityQueryHandler supplies the same collecting handler to the parser.
	 * Previous errors have already been recorded by the time this event fires.
	 */
	final class ResetFileErrorsExtension implements ExtensionInterface, BeforeFileAnalysisInterface {
		private Collecting $_errorHandler;

		public function __construct(Collecting $errorHandler) {
			$this->_errorHandler = $errorHandler;
		}

		public function getName(): string {
			return 'wpts-reset-file-errors';
		}

		public function beforeAnalyzeFile(BeforeFileAnalysisEvent $event): void {
			$this->_errorHandler->clearErrors();
		}
	}
}
