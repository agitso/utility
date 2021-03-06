<?php

namespace Ag\Utility\Statsd;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class StatsdWriter {

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * @var bool
	 */
	protected $logToSystemLog;

	/**
	 * @var string
	 */
	protected $keyPrefix;

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$this->enabled = $settings['StatsdWriter']['enabled'] === TRUE;
		$this->logToSystemLog = array_key_exists('logToSystemLog', $settings['StatsdWriter']) ? $settings['StatsdWriter']['logToSystemLog'] === TRUE : FALSE;
		$this->host = array_key_exists('host', $settings['StatsdWriter']) ? $settings['StatsdWriter']['host'] : NULL;
		$this->port = array_key_exists('port', $settings['StatsdWriter']) ? $settings['StatsdWriter']['port'] : NULL;
		$this->keyPrefix = array_key_exists('keyPrefix', $settings['StatsdWriter']) ?  $settings['StatsdWriter']['keyPrefix'] : NULL;
	}

	/**
	 * @param string $key
	 */
	public function count($key) {
		$this->send($this->keyPrefix.'.'.$key.':1|c');
	}

	/**
	 * @param string $key
	 * @param int $ms
	 */
	public function time($key, $ms) {
		$this->send($this->keyPrefix.'.'.$key.':'.intval($ms).'|ms');
	}

	/**
	 * @param string $key
	 * @param int $value
	 */
	public function gauge($key, $value) {
		$this->send($this->keyPrefix.'.'.$key.':'.intval($value).'|g');
	}


	/**
	 * @param string $message
	 */
	protected function send($message) {
		if(empty($message)) {
			return;
		}

		if ($this->logToSystemLog) {
			$this->systemLogger->log('Statsd Writer send: ' . $message);
		}

		if($this->enabled) {
			// Wrap this in a try/catch - failures in any of this should be silently ignored
			try {
				$fp = fsockopen("udp://$this->host", $this->port);
				if (!$fp) {
					return;
				}

				fwrite($fp, $message);

				fclose($fp);
			} catch (\Exception $e) {
			}
		}
	}
}