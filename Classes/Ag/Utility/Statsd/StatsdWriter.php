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
	 * @var string
	 */
	protected $keyPrefix;

	/**
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$this->enabled = $settings['StatsdWriter']['enabled'] === TRUE;
		$this->host = array_key_exists('host', $settings['StatsdWriter']) ? $settings['StatsdWriter']['host'] : NULL;
		$this->port = array_key_exists('post', $settings['StatsdWriter']) ? $settings['StatsdWriter']['port'] : NULL;
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

		if (!$this->enabled || empty($message)) {
			return;
		}

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