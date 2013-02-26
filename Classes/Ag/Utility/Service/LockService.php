<?php
namespace Ag\Utility\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class LockService {

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * Injects the environment utility
	 *
	 * @param \TYPO3\Flow\Utility\Environment $environment
	 * @return void
	 */
	public function injectEnvironment(\TYPO3\Flow\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @var int
	 */
	protected $timeout;

	/**
	 * @param int $timeout
	 */
	public function __construct($timeout = 90) {
		$this->timeout = $timeout;
	}

	/**
	 * @param string $key
	 * @throws \Ag\Utility\Exception\LockCouldNotBeAquiredException
	 * @return void
	 */
	public function acquire($key) {
		if(file_exists($this->getLockFile($key)) && filemtime($this->getLockFile($key)) > (time() - $this->timeout)) {
			throw new \Ag\Utility\Exception\LockCouldNotBeAquiredException();
		} else {
			file_put_contents($this->getLockFile($key), '');
		}
	}

	/**
	 * @param string $key
	 * @param int $tries
	 * @throws \Ag\Utility\Exception\LockCouldNotBeAquiredException
	 */
	public function waitAndAcquire($key, $tries = 10) {
		for($i=0; $i<$tries; $i++) {
			try {
				$this->acquire($key);
				return;
			} catch(\Ag\Utility\Exception\LockCouldNotBeAquiredException $e) {
				sleep(1);
			}
		}

		throw new \Ag\Utility\Exception\LockCouldNotBeAquiredException();
	}

	/**
	 * @param string $key
	 * @return void
	 */
	public function release($key) {
		if(file_exists($this->getLockFile($key))) {
			unlink($this->getLockFile($key));
		}
	}


	/**
	 * @param string $key
	 * @return string
	 */
	protected function getLockFile($key) {
		return $this->environment->getPathToTemporaryDirectory().'lock_'.$key.'.lock';
	}

}

?>