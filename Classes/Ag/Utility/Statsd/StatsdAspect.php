<?php
namespace Ag\Utility\Statsd;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class StatsdAspect {

	/**
	 * @var \Ag\Utility\Statsd\StatsdWriter
	 * @Flow\Inject
	 */
	protected $statsdWriter;

	/**
	 * @var string
	 */
	protected $keyPrefix;

	/**
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$keyPrefix = trim($settings['StatsdWriter']['keyPrefix']);
		if(!empty($keyPrefix)) {
			$this->keyPrefix = $keyPrefix;
		}
	}

	/**
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @Flow\AfterReturning("methodAnnotatedWith(Ag\Utility\Statsd\Annotations\Count)")
	 * @return string
	 */
	public function count(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$this->statsdWriter->send($this->getKeyFromJoinPoint($joinPoint).':1|c');
	}

	/**
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @Flow\Around("methodAnnotatedWith(Ag\Utility\Statsd\Annotations\Time)")
	 * @return mixed
	 */
	public function time(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$start = microtime(TRUE);

		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);

		$time = (microtime(true) - $start) * 1000;

		$this->statsdWriter->send($this->getKeyFromJoinPoint($joinPoint).':'.$time.'|ms');

		return $result;
	}

	/**
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @Flow\Around("methodAnnotatedWith(Ag\Utility\Statsd\Annotations\Gauge)")
	 * @return mixed
	 */
	public function gauge(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);

		// Only gauge integers
		$intResult = intval($result);

		if(('' . $result) === ('' . $intResult)) {
			$this->statsdWriter->send($this->getKeyFromJoinPoint($joinPoint).':'.$result.'|g');
		}

		return $result;
	}

	/**
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @return string
	 */
	protected function getKeyFromJoinPoint(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$key = !empty($this->keyPrefix) ? $this->keyPrefix . '.' : '';
		$key .= $joinPoint->getClassName().'.'.$joinPoint->getMethodName();

		return str_replace('\\', '.', $key);
	}
}
?>