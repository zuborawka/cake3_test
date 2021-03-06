<?php
/**
 * PHP 5
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @since         CakePHP(tm) v 1.2.0.5550
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Test\TestCase\Database;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Database\ConnectionManager;
use Cake\Database\Driver\Sqlite;
use Cake\TestSuite\TestCase;

/**
 * ConnectionManager Test
 */
class ConnectionManagerTest extends TestCase {

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		Plugin::unload();
		ConnectionManager::drop('test_variant');
	}

/**
 * Data provider for valid config data sets.
 *
 * @return array
 */
	public static function configProvider() {
		return [
			'Array of data using classname key.' => [[
				'className' => 'Sqlite',
				'database' => ':memory:',
			]],
			'Direct instance' => [new Sqlite(['database' => ':memory:'])],
		];
	}

/**
 * Test the various valid config() calls.
 *
 * @dataProvider configProvider
 * @return void
 */
	public function testConfigVariants($settings) {
		$this->assertNotContains('test_variant', ConnectionManager::configured(), 'test_variant config should not exist.');
		ConnectionManager::config('test_variant', $settings);

		$ds = ConnectionManager::get('test_variant');
		$this->assertInstanceOf('Cake\Database\Connection', $ds);
		$this->assertContains('test_variant', ConnectionManager::configured());
	}

/**
 * Test invalid classes cause exceptions
 *
 * @expectedException Cake\Database\Exception\MissingDriverException
 */
	public function testConfigInvalidOptions() {
		ConnectionManager::config('test_variant', [
			'className' => 'HerpDerp'
		]);
		ConnectionManager::get('test_variant');
	}

/**
 * Test for errors on duplicate config.
 *
 * @expectedException Cake\Error\Exception
 * @expectedExceptionMessage Cannot reconfigure existing key "test_variant"
 * @return void
 */
	public function testConfigDuplicateConfig() {
		$settings = [
			'className' => 'Sqlite',
			'database' => ':memory:',
		];
		ConnectionManager::config('test_variant', $settings);
		ConnectionManager::config('test_variant', $settings);
	}

/**
 * Test get() failing on missing config.
 *
 * @expectedException Cake\Error\Exception
 * @expectedExceptionMessage The datasource configuration "test_variant" was not found.
 * @return void
 */
	public function testGetFailOnMissingConfig() {
		ConnectionManager::get('test_variant');
	}

/**
 * Test loading configured connections.
 *
 * @return void
 */
	public function testGet() {
		$config = ConnectionManager::config('test');
		$this->skipIf(empty($config), 'No test config, skipping');

		$ds = ConnectionManager::get('test');
		$this->assertSame($ds, ConnectionManager::get('test'));
		$this->assertInstanceOf('Cake\Database\Connection', $ds);
		$this->assertEquals('test', $ds->configName());
	}

/**
 * Test that configured() finds configured sources.
 *
 * @return void
 */
	public function testConfigured() {
		ConnectionManager::config('test_variant', [
			'className' => 'Sqlite',
			'database' => ':memory:'
		]);
		$results = ConnectionManager::configured();
		$this->assertContains('test_variant', $results);
	}

/**
 * testGetPluginDataSource method
 *
 * @return void
 */
	public function testGetPluginDataSource() {
		Plugin::load('TestPlugin');
		$name = 'test_variant';
		$config = array('className' => 'TestPlugin.TestSource');
		ConnectionManager::config($name, $config);
		$connection = ConnectionManager::get($name);

		$this->assertInstanceOf('Cake\Database\Connection', $connection);
		$this->assertInstanceOf('TestPlugin\Database\Driver\TestSource', $connection->driver());
	}

/**
 * Tests that a connection configuration can be deleted in runtime
 *
 * @return void
 */
	public function testDrop() {
		ConnectionManager::config('test_variant', [
			'datasource' => 'Sqlite',
			'database' => 'memory'
		]);
		$result = ConnectionManager::configured();
		$this->assertContains('test_variant', $result);

		$this->assertTrue(ConnectionManager::drop('test_variant'));
		$result = ConnectionManager::configured();
		$this->assertNotContains('test_variant', $result);

		$this->assertFalse(ConnectionManager::drop('probably_does_not_exist'), 'Should return false on failure.');
	}

}
