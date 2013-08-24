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
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @Flow\AfterReturning("methodAnnotatedWith(Ag\Utility\Statsd\Annotations\Count)")
	 * @return string
	 */
	public function count(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$this->statsdWriter->count($this->getKeyFromJoinPoint($joinPoint));
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

		$this->statsdWriter->time($this->getKeyFromJoinPoint($joinPoint), $time);

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
			$this->statsdWriter->gauge($this->getKeyFromJoinPoint($joinPoint), $result);
		}

		return $result;
	}

	/**
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @return string
	 */
	protected function getKeyFromJoinPoint(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$key = $joinPoint->getClassName().'.'.$joinPoint->getMethodName();

		return str_replace('\\', '.', $key);
	}
}
?>