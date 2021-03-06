<?php
/**
 * PHP Version 5.4
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Database\Schema;

use Cake\Core\Configure;
use Cake\Database\ConnectionManager;
use Cake\Database\Schema\Collection;
use Cake\Database\Schema\Table;
use Cake\TestSuite\TestCase;

/**
 * Test case for Collection
 */
class CollectionTest extends TestCase {

/**
 * Setup function
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->connection = ConnectionManager::get('test');
	}

/**
 * Teardown function
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->connection);
	}

/**
 * Test that describing non-existent tables fails.
 *
 * Tests for positive describe() calls are in each platformSchema
 * test case.
 *
 * @expectedException Cake\Database\Exception
 * @return void
 */
	public function testDescribeIncorrectTable() {
		$schema = new Collection($this->connection);
		$this->assertNull($schema->describe('derp'));
	}

}
