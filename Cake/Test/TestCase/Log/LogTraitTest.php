<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Log;

use Cake\Log\Log;
use Cake\Log\LogInterface;
use Cake\Log\LogTrait;
use Cake\TestSuite\TestCase;

/**
 * Test case for LogTrait
 *
 * @package Cake.Test.TestCase.Log
 */
class LogTraitTest extends TestCase {

	public function tearDown() {
		parent::tearDown();
		Log::drop('trait_test');
	}

/**
 * Test log method.
 *
 * @return void
 */
	public function testLog() {
		$mock = $this->getMock('Cake\Log\LogInterface');
		$mock->expects($this->at(0))
			->method('write')
			->with('error', 'Testing');

		$mock->expects($this->at(1))
			->method('write')
			->with('debug', print_r(array(1, 2), true));

		Log::config('trait_test', ['engine' => $mock]);
		$subject = $this->getObjectForTrait('Cake\Log\LogTrait');

		$subject->log('Testing');
		$subject->log(array(1, 2), 'debug');
	}

}
