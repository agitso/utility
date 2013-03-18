<?php
namespace Ag\Utility\Log;

class Logger extends \TYPO3\Flow\Log\Logger {

	/**
	 * Log the full exception including backtrace etc. to the log
	 *
	 * @param \Exception $exception The exception to log
	 * @param array $additionalData Additional data to log
	 * @return void
	 */
	public function logException(\Exception $exception, array $additionalData = array()) {
		$backTrace = $exception->getTrace();
		$className = isset($backTrace[0]['class']) ? $backTrace[0]['class'] : '?';
		$methodName = isset($backTrace[0]['function']) ? $backTrace[0]['function'] : '?';
		$message = $this->getExceptionLogMessage($exception);

		$additionalData['backtrace'] = \TYPO3\Flow\Error\Debugger::getBacktraceCode($backTrace, FALSE, TRUE);

		$explodedClassName = explode('\\', $className);
		$packageKey = (isset($explodedClassName[1])) ? $explodedClassName[1] : NULL;

		$prefix = '';
		while ($exception->getPrevious() !== NULL) {
			$exception = $exception->getPrevious();
			$prefix .= '-';

			$additionalData[$prefix.'exception'] = $this->getExceptionLogMessage($exception->getPrevious());
		}

		$this->log($message, LOG_CRIT, $additionalData, $packageKey, $className, $methodName);
	}
}

?>