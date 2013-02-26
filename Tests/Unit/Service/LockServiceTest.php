<?php
namespace Ag\Utility\Tests\Unit\Service;

use org\bovigo\vfs\vfsStream;

class LockServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	const TIMEOUT = 3;

	/**
	 * @var \Ag\Utility\Service\LockService
	 */
	protected $fixture;

	protected function setUp() {
		$this->fixture = new \Ag\Utility\Service\LockService(self::TIMEOUT);
		vfsStream::setup('Foo');

		$environment = $this->getMock('TYPO3\Flow\Utility\Environment', array(), array(), '', FALSE);
		$environment
				->expects($this->any())
				->method('getPathToTemporaryDirectory')
				->will($this->returnValue('vfs://Foo/'));

		$this->fixture->injectEnvironment($environment);
	}

	/**
	 * @test
	 */
	public function canAquireLock() {
		$this->fixture->acquire('lock');
		$this->assertTrue(TRUE);

	}

	/**
	 * @test
	 */
	public function canRelaseLock() {
		$this->fixture->acquire('lock');
		$this->fixture->release('lock');
		$this->assertTrue(TRUE);
	}

	/**
	 * @test
	 * @expectedException \Ag\Utility\Exception\LockCouldNotBeAquiredException
	 */
	public function cannotAcquireLockTwice() {
		$this->fixture->acquire('lock');
		$this->fixture->acquire('lock');
	}

	/**
	 * @test
	 */
	public function canAcquireLockWhenReleased(){
		$this->fixture->acquire('lock');
		$this->fixture->release('lock');
		$this->fixture->acquire('lock');
		$this->assertTrue(TRUE);
	}

	/**
	 * Should wait ~2 seconds for lock and then get an exception.
	 * Shows that the lock waits
	 *
	 * @test
	 */
	public function canWaitForLock() {
		$this->fixture->acquire('lock');

		$start = microtime(TRUE);

		try {
			$this->fixture->waitAndAcquire(1);
		} catch(\Ag\Utility\Exception\LockCouldNotBeAquiredException $e) {
			$this->assertEquals(1, microtime(TRUE)-$start, '', 0.1);
		}
	}

	/**
	 * If we wait for the lock we expect to wait approx. timeout time
	 *
	 * @test
	 */
	public function lockTimesOut() {
		$this->fixture->acquire('lock');
		$start = microtime(TRUE);

		$this->fixture->waitAndAcquire('lock');

		$this->assertEquals(3, microtime(TRUE)-$start, '', 0.1);
	}

	/**
	 * @test
	 */
	public function supportsMultipleLocks() {
		$this->fixture->acquire('lock1');
		$this->fixture->acquire('lock2');

		$this->assertTrue(TRUE);
	}
}
?>