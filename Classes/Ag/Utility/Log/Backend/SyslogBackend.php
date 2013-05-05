<?php
namespace Ag\Utility\Log\Backend;

use TYPO3\Flow\Annotations as Flow;

class SyslogBackend extends \TYPO3\Flow\Log\Backend\AbstractBackend {

	/**
	 * @var string
	 */
	protected $site;

	/**
	 * Carries out all actions necessary to prepare the logging backend, such as opening
	 * the log file or opening a database connection.
	 *
	 * @return void
	 * @api
	 */
	public function open() {
		$this->severityLabels = array(
			LOG_EMERG => 'EMERGENCY',
			LOG_ALERT => 'ALERT    ',
			LOG_CRIT => 'CRITICAL ',
			LOG_ERR => 'ERROR    ',
			LOG_WARNING => 'WARNING  ',
			LOG_NOTICE => 'NOTICE   ',
			LOG_INFO => 'INFO     ',
			LOG_DEBUG => 'DEBUG    ',
		);
	}

	/**
	 * Appends the given message along with the additional information into the log.
	 *
	 * @param string $message The message to log
	 * @param integer $severity One of the LOG_* constants
	 * @param mixed $additionalData A variable containing more information about the event to be logged
	 * @param string $packageKey Key of the package triggering the log (determined automatically if not specified)
	 * @param string $className Name of the class triggering the log (determined automatically if not specified)
	 * @param string $methodName Name of the method triggering the log (determined automatically if not specified)
	 * @return void
	 * @api
	 */
	public function append($message, $severity = LOG_INFO, $additionalData = NULL, $packageKey = NULL, $className = NULL, $methodName = NULL) {
		if ($severity > $this->severityThreshold || empty($this->key)) {
			return;
		}

		$severityLabel = (isset($this->severityLabels[$severity])) ? $this->severityLabels[$severity] : 'UNKNOWN  ';
		$output = strftime('%y-%m-%d %H:%M:%S', time()) . '    ' . $severityLabel . ' ' . str_pad($packageKey, 20) . ' ' . $message;

		if ($className !== NULL || $methodName !== NULL) {
			$output .= ' [logged in ' . $className . '::' . $methodName . '()]';
		}

		openlog($this->site, LOG_NDELAY, LOG_USER);
		syslog($severity, $output);
		closelog();
	}

	/**
	 * Carries out all actions necessary to cleanly close the logging backend, such as
	 * closing the log file or disconnecting from a database.
	 *
	 * @return void
	 * @api
	 */
	public function close() {
	}

	/**
	 * @param string $site
	 * @return SyslogBackend
	 */
	public function setSite($site) {
		$this->site = trim($site);
		return $this;
	}
}

?>