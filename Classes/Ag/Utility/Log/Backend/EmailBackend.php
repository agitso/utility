<?php
namespace Ag\Utility\Log\Backend;

use TYPO3\Flow\Annotations as Flow;

class EmailBackend extends \TYPO3\Flow\Log\Backend\AbstractBackend {

	/**
	 * @var string|array
	 */
	protected $emails;

	/**
	 * @var string
	 */
	protected $from;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * Carries out all actions necessary to prepare the logging backend, such as opening
	 * the log file or opening a database connection.
	 *
	 * @return void
	 * @api
	 */
	public function open() {
		$this->severityLabels = array(
			LOG_EMERG   => 'EMERGENCY',
			LOG_ALERT   => 'ALERT    ',
			LOG_CRIT    => 'CRITICAL ',
			LOG_ERR     => 'ERROR    ',
			LOG_WARNING => 'WARNING  ',
			LOG_NOTICE  => 'NOTICE   ',
			LOG_INFO    => 'INFO     ',
			LOG_DEBUG   => 'DEBUG    ',
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
		if ($severity > $this->severityThreshold) {
			return;
		}

		$severityLabel = (isset($this->severityLabels[$severity])) ? $this->severityLabels[$severity] : 'UNKNOWN  ';
		$output = strftime('%y-%m-%d %H:%M:%S', time()) . '    ' . $severityLabel . ' ' . str_pad($packageKey, 20) . ' ' . $message;

		if ($className !== NULL || $methodName !== NULL) {
			$output .= ' [logged in ' . $className . '::' . $methodName . '()]';
		}
		if (!empty($additionalData)) {
			$output .= PHP_EOL . $this->getFormattedVarDump($additionalData);
		}

		if(is_string($this->getEmails())) {
			mail($this->getEmails(), $this->getSubject(), $output, 'From: ' . $this->getFrom());
		}
		elseif(is_array($this->getEmails())) {
			foreach($this->getEmails() as $email) {
				mail($email, $this->getSubject(), $output, 'From: '. $this->getFrom());
			}
		}
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
	 * @param string|array $emails
	 * @return EmailBackend
	 */
	public function setEmails($emails) {
		$this->emails = $emails;
		return $this;
	}

	/**
	 * @return string|array
	 */
	public function getEmails() {
		return $this->emails;
	}

	/**
	 * @param string $from
	 * @return EmailBackend
	 */
	public function setFrom($from) {
		$this->from = $from;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param string $subject
	 * @return EmailBackend
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}
}
?>