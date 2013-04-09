<?php
namespace Ag\Utility;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class TimeAspect {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * @param \TYPO3\Flow\AOP\JoinPointInterface $joinPoint
	 * @Flow\Around("methodAnnotatedWith(Ag\Utility\Annotations\Time)")
	 * @return mixed
	 */
	public function time(\TYPO3\Flow\AOP\JoinPointInterface $joinPoint) {
		$start = microtime(TRUE);

		$result = $joinPoint->getAdviceChain()->proceed($joinPoint);

		$time = (microtime(true) - $start) * 1000;

		$this->systemLogger->log('Method ' . $joinPoint->getClassName().'.'.$joinPoint->getMethodName() . ' took ' . $time .' ms to complete.');

		return $result;
	}
}
?>