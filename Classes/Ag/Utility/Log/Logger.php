<?php
namespace Ag\Utility\Log;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * The default logger of the Flow framework
 *
 * @api
 */
class Logger implements \TYPO3\Flow\Log\SystemLoggerInterface, \TYPO3\Flow\Log\SecurityLoggerInterface {

	/**
	 * @var array
	 */
	protected $backends;

	/**
	 * Constructs the logger
	 *
	 */
	public function __construct($options) {
		$this->backends = array(
			new \Ag\Utility\Log\Backend\GelfBackend($options[0]),
			new \Ag\Utility\Log\Backend\EmailBackend($options[1])
		);
	}


	/**
	 * Adds a backend to which the logger sends the logging data
	 *
	 * @param \TYPO3\Flow\Log\Backend\BackendInterface $backend A backend implementation
	 * @return void
	 * @api
	 */
	public function addBackend(\TYPO3\Flow\Log\Backend\BackendInterface $backend) {
		// TODO: Implement addBackend() method.
	}

	/**
	 * Runs the close() method of a backend and removes the backend
	 * from the logger.
	 *
	 * @param \TYPO3\Flow\Log\Backend\BackendInterface $backend The backend to remove
	 * @return void
	 * @throws \TYPO3\Flow\Log\Exception\NoSuchBackendException if the given backend is unknown to this logger
	 * @api
	 */
	public function removeBackend(\TYPO3\Flow\Log\Backend\BackendInterface $backend) {
		// TODO: Implement removeBackend() method.
	}


	/**
	 * Writes the given message along with the additional information into the log.
	 *
	 * @param string $message The message to log
	 * @param integer $severity An integer value, one of the LOG_* constants
	 * @param mixed $additionalData A variable containing more information about the event to be logged
	 * @param string $packageKey Key of the package triggering the log (determined automatically if not specified)
	 * @param string $className Name of the class triggering the log (determined automatically if not specified)
	 * @param string $methodName Name of the method triggering the log (determined automatically if not specified)
	 * @return void
	 * @api
	 */
	public function log($message, $severity = LOG_INFO, $additionalData = NULL, $packageKey = NULL, $className = NULL, $methodName = NULL) {
		if ($packageKey === NULL) {
			$backtrace = debug_backtrace(FALSE);
			$className = isset($backtrace[1]['class']) ? $backtrace[1]['class'] : NULL;
			$methodName = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : NULL;
			$explodedClassName = explode('\\', $className);
				// FIXME: This is not really the package key:
			$packageKey = isset($explodedClassName[1]) ? $explodedClassName[1] : '';
		}
		foreach ($this->backends as $backend) {
			$backend->append($message, $severity, $additionalData, $packageKey, $className, $methodName);
		}
	}

	/**
	 * Writes information about the given exception into the log.
	 *
	 * @param \Exception $exception The exception to log
	 * @param array $additionalData Additional data to log
	 * @return void
	 * @api
	 */
	public function logException(\Exception $exception, array $additionalData = array()) {
		$backTrace = $exception->getTrace();
		$className = isset($backTrace[0]['class']) ? $backTrace[0]['class'] : '?';
		$methodName = isset($backTrace[0]['function']) ? $backTrace[0]['function'] : '?';
		$message = $this->getExceptionLogMessage($exception);

		if ($exception->getPrevious() !== NULL) {
			$additionalData['previousException'] = $this->getExceptionLogMessage($exception->getPrevious());
		}

		$additionalData['backtrace'] = \TYPO3\Flow\Error\Debugger::getBacktraceCode($backTrace, FALSE, TRUE);

		$explodedClassName = explode('\\', $className);
			// FIXME: This is not really the package key:
		$packageKey = (isset($explodedClassName[1])) ? $explodedClassName[1] : NULL;

		$this->log($message, LOG_CRIT, $additionalData, $packageKey, $className, $methodName);
	}

	/**
	 * @param \Exception $exception
	 * @return string
	 */
	protected function getExceptionLogMessage(\Exception $exception) {
		$exceptionCodeNumber = ($exception->getCode() > 0) ? ' #' . $exception->getCode() : '';
		$backTrace = $exception->getTrace();
		$line = isset($backTrace[0]['line']) ? ' in line ' . $backTrace[0]['line'] . ' of ' . $backTrace[0]['file'] : '';
		return 'Uncaught exception' . $exceptionCodeNumber . $line . ': ' . $exception->getMessage() ;
	}

	/**
	 * Cleanly closes all registered backends before destructing this Logger
	 *
	 * @return void
	 */
	public function shutdownObject() {
	}
}
?>