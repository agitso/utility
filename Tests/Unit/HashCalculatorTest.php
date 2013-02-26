<?php
namespace Ag\Utility\Tests\Unit;

class HashCalculatorTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function canHashArrayWithoutToken() {
		$input = array('this', 'is', 'some', 'content', '18', '@');

		$this->assertEquals(hash('md5', 'thisissomecontent18@'), \Ag\Utility\HashCalculator::hash($input));
	}

	/**
	 * @test
	 */
	public function canHashArrayWithToken() {
		$input = array('this', 'is', 'some', 'content', '18', '@');
		$token = 'eirae7Ai';

		$this->assertEquals(hash('md5', 'thisissomecontent18@'.$token), \Ag\Utility\HashCalculator::hash($input, $token));

	}
}

?>