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
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$this->enabled = $settings['StatsdWriter']['enabled'] === TRUE;
		$this->host = $settings['StatsdWriter']['host'];
		$this->port = $settings['StatsdWriter']['port'];
	}

	/**
	 * @param string $message
	 */
	public function send($message) {

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