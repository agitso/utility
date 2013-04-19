<?php

namespace Ag\Utility\Service;

require_once(FLOW_PATH_PACKAGES . '/Libraries/pda/pheanstalk/pheanstalk_init.php');

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
	 * @var \Pheanstalk_Pheanstalk
	 */
	protected $pheanstalk;

	/**
	 * @param $command
	 * @param string $tube
	 * @param int $priority
	 */
	public function put($command, $tube, $priority = 1024) {
		$this->initializePheanstalk();

		$serializedCommand = serialize($command);
		$this->pheanstalk->putInTube($tube, $serializedCommand, $priority);

		$this->systemLogger->log('Put command in tube "'.$tube.'"', LOG_DEBUG, $serializedCommand);
	}

	/**
	 * @param string $tube
	 * @return Object
	 */
	public function get($tube) {
		$this->systemLogger->log('Wait for job from "'.$tube.'"', LOG_DEBUG);

		$this->initializePheanstalk();
		$job = $this->pheanstalk
				->watch($tube)
				->ignore('default')
				->reserve();

		$command = unserialize($job->getData());

		$this->systemLogger->log('Got job from "'.$tube.'" ('.get_class($command).')', LOG_DEBUG, serialize($command));

		$this->pheanstalk->delete($job);

		return $command;
	}

	protected function initializePheanstalk() {
		if ($this->pheanstalk === NULL) {
			$this->pheanstalk = new \Pheanstalk_Pheanstalk('127.0.0.1');
		}
	}
}