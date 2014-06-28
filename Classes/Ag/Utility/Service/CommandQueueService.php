<?php

namespace Ag\Utility\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CommandQueueService {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @var \Pheanstalk\Pheanstalk
	 */
	protected $pheanstalk;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param $command
	 * @param string $tube
	 * @param int $priority
	 */
	public function put($command, $tube, $priority = 1024) {
		$this->initializePheanstalk();

		$serializedCommand = serialize($command);
		$this->pheanstalk->putInTube($tube, $serializedCommand, $priority);

		if($this->loggingEnabled()) {
			$this->systemLogger->log('Put command in tube "' . $tube . '"', LOG_DEBUG, $serializedCommand);
		}
	}

	/**
	 * @param string $tube
	 * @param int|null $timeout
	 * @return Object|bool
	 */
	public function get($tube, $timeout = NULL) {
		if($this->loggingEnabled()) {
			$this->systemLogger->log('Wait for job from "' . $tube . '"', LOG_DEBUG);
		}

		$this->initializePheanstalk();
		$job = $this->pheanstalk
				->watch($tube)
				->ignore('default')
				->reserve($timeout);

		if ($job instanceof \Pheanstalk\Job) {
			$command = unserialize($job->getData());
			if($this->loggingEnabled()) {
				$this->systemLogger->log('Got job from "' . $tube . '" (' . get_class($command) . ')', LOG_DEBUG, serialize($command));
			}
			$this->pheanstalk->delete($job);

			return $command;
		}

		return FALSE;
	}

	protected function initializePheanstalk() {
		if ($this->pheanstalk === NULL) {
			$this->pheanstalk = new \Pheanstalk\Pheanstalk('127.0.0.1');
		}
	}

	/**
	 * @return bool
	 */
	protected function loggingEnabled() {
		return array_key_exists('CommandQueue', $this->settings) &&
				array_key_exists('Log', $this->settings['CommandQueue']) &&
				$this->settings['CommandQueue']['Log'] === TRUE;
	}
}