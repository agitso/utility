<?php
namespace TYPO3\Surf\Application\TYPO3;

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
* @TYPO3\Flow\Annotations\Proxy(false)
 */
class AgitsoFlow extends \TYPO3\Surf\Application\TYPO3\Flow {

	/**
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);
		$workflow->removeTask('typo3.surf:typo3:flow:setfilepermissions');
	}
}
?>