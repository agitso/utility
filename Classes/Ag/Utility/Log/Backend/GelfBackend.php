<?php
namespace Ag\Utility\Log\Backend;

class GelfBackend extends \TYPO3\Flow\Log\Backend\AbstractBackend {

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string
	 */
	protected $site;

	/**
	 * @var array
	 */
	protected $severityMap;

	/**
	 * Carries out all actions necessary to prepare the logging backend, such as opening
	 * the log file or opening a database connection.
	 *
	 * @return void
	 * @api
	 */
	public function open() {
		$this->severityMap = array(
			LOG_EMERG => '600',
			LOG_ALERT => '550',
			LOG_CRIT => '500',
			LOG_ERR => '400',
			LOG_WARNING => '300',
			LOG_NOTICE => '250',
			LOG_INFO => '200',
			LOG_DEBUG => '100',
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

		$shortMessage = $message;
		$fullMessage = $message;

		if (!empty($additionalData)) {
			$fullMessage .= PHP_EOL .PHP_EOL .PHP_EOL . $this->getFormattedVarDump($additionalData);
		}

		if($severity <= LOG_WARNING) {
			$fullMessage .= PHP_EOL .PHP_EOL . 'GET:' . PHP_EOL . $this->getFormattedVarDump($_GET);
			$fullMessage .= PHP_EOL .PHP_EOL .'POST:' . PHP_EOL . $this->getFormattedVarDump($_POST);
			$fullMessage .= PHP_EOL .PHP_EOL .'FILES:' . PHP_EOL . $this->getFormattedVarDump($_FILES);
			$fullMessage .= PHP_EOL .PHP_EOL .'COOKIE:' . PHP_EOL . $this->getFormattedVarDump($_COOKIE);
			$fullMessage .= PHP_EOL .PHP_EOL .'SERVER:' . PHP_EOL . $this->getFormattedVarDump($_SERVER);
		}

		$publisher = new \Gelf\MessagePublisher($this->getHost());

		$message = new \Gelf\Message();
		$message->setFile($className.'::'.$methodName.'()');
		$message->setLevel($severity);
		$message->setHost($this->getSite());
		$message->setFacility($packageKey);
		$message->setVersion(1);
		$message->setShortMessage($shortMessage);
		$message->setFullMessage($fullMessage);

		$publisher->publish($message);
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
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param string $site
	 */
	public function setSite($site) {
		$this->site = $site;
	}

	/**
	 * @return string
	 */
	public function getSite() {
		return $this->site;
	}


}

?>