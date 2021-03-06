<?php
/**
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @since         CakePHP(tm) v 1.2.0.4667
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\TestSuite;

use Cake\Core\Configure;
use Cake\Database\ConnectionManager;
use Cake\Model\Model;
use Cake\TestSuite\Fixture\TestFixture;
use Cake\TestSuite\TestCase;
use Cake\Utility\ClassRegistry;

/**
 * ArticleFixture class
 *
 * @package       Cake.Test.TestCase.TestSuite
 */
class ArticleFixture extends TestFixture {

/**
 * Table property
 *
 * @var string
 */
	public $table = 'articles';

/**
 * Fields array
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'integer'],
		'name' => ['type' => 'string', 'length' => '255'],
		'created' => ['type' => 'datetime'],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']]
		]
	];

/**
 * Records property
 *
 * @var array
 */
	public $records = array(
		array('name' => 'Gandalf', 'created' => '2009-04-28 19:20:00'),
		array('name' => 'Captain Picard', 'created' => '2009-04-28 19:20:00'),
		array('name' => 'Chewbacca', 'created' => '2009-04-28 19:20:00')
	);
}

/**
 * StringFieldsTestFixture class
 *
 * @package       Cake.Test.Case.TestSuite
 * @subpackage    cake.cake.tests.cases.libs
 */
class StringsTestFixture extends TestFixture {

/**
 * Table property
 *
 * @var string
 */
	public $table = 'strings';

/**
 * Fields array
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer'),
		'name' => array('type' => 'string', 'length' => '255'),
		'email' => array('type' => 'string', 'length' => '255'),
		'age' => array('type' => 'integer', 'default' => 10)
	);

/**
 * Records property
 *
 * @var array
 */
	public $records = array(
		array('name' => 'Mark Doe', 'email' => 'mark.doe@email.com'),
		array('name' => 'John Doe', 'email' => 'john.doe@email.com', 'age' => 20),
		array('email' => 'jane.doe@email.com', 'name' => 'Jane Doe', 'age' => 30)
	);
}


/**
 * ImportFixture class
 *
 * @package       Cake.Test.Case.TestSuite
 */
class ImportFixture extends TestFixture {

/**
 * Import property
 *
 * @var mixed
 */
	public $import = ['table' => 'posts', 'connection' => 'test'];
}

/**
 * Test case for TestFixture
 *
 * @package       Cake.Test.Case.TestSuite
 */
class TestFixtureTest extends TestCase {

/**
 * Fixtures for this test.
 *
 * @var array
 */
	public $fixtures = ['core.post'];

/**
 * test initializing a static fixture
 *
 * @return void
 */
	public function testInitStaticFixture() {
		$Fixture = new ArticleFixture();
		$this->assertEquals('articles', $Fixture->table);

		$Fixture = new ArticleFixture();
		$Fixture->table = null;
		$Fixture->init();
		$this->assertEquals('articles', $Fixture->table);

		$schema = $Fixture->schema();
		$this->assertInstanceOf('Cake\Database\Schema\Table', $schema);

		$fields = $Fixture->fields;
		unset($fields['_constraints'], $fields['_indexes']);
		$this->assertEquals(
			array_keys($fields),
			$schema->columns(),
			'Fields do not match'
		);
		$this->assertEquals(array_keys($Fixture->fields['_constraints']), $schema->constraints());
		$this->assertEmpty($schema->indexes());
	}

/**
 * test import fixture initialization
 *
 * @return void
 */
	public function testInitImport() {
		$fixture = new ImportFixture();
		$fixture->fields = $fixture->records = null;
		$fixture->import = [
			'table' => 'posts',
			'connection' => 'test',
		];
		$fixture->init();

		$expected = [
			'id',
			'author_id',
			'title',
			'body',
			'published',
		];
		$this->assertEquals($expected, $fixture->schema()->columns());
	}

/**
 * test create method
 *
 * @return void
 */
	public function testCreate() {
		$fixture = new ArticleFixture();
		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$table = $this->getMock('Cake\Database\Schema\Table', [], ['articles']);
		$table->expects($this->once())
			->method('createSql')
			->with($db)
			->will($this->returnValue(['sql', 'sql']));
		$fixture->schema($table);

		$db->expects($this->exactly(2))->method('execute');
		$this->assertTrue($fixture->create($db));
	}

/**
 * test create method, trigger error
 *
 * @expectedException PHPUnit_Framework_Error
 * @return void
 */
	public function testCreateError() {
		$fixture = new ArticleFixture();
		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$table = $this->getMock('Cake\Database\Schema\Table', [], ['articles']);
		$table->expects($this->once())
			->method('createSql')
			->with($db)
			->will($this->throwException(new \Exception('oh noes')));
		$fixture->schema($table);

		$fixture->create($db);
	}

/**
 * test the insert method
 *
 * @return void
 */
	public function testInsert() {
		$fixture = new ArticleFixture();

		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$query = $this->getMock('Cake\Database\Query', [], [$db]);
		$db->expects($this->once())
			->method('newQuery')
			->will($this->returnValue($query));

		$query->expects($this->once())
			->method('insert')
			->with('articles', ['name', 'created'], ['string', 'datetime'])
			->will($this->returnSelf());
		$expected = [
			['name' => 'Gandalf', 'created' => '2009-04-28 19:20:00'],
			['name' => 'Captain Picard', 'created' => '2009-04-28 19:20:00'],
			['name' => 'Chewbacca', 'created' => '2009-04-28 19:20:00']
		];
		$query->expects($this->at(1))
			->method('values')
			->with($expected[0])
			->will($this->returnSelf());
		$query->expects($this->at(2))
			->method('values')
			->with($expected[1])
			->will($this->returnSelf());
		$query->expects($this->at(3))
			->method('values')
			->with($expected[2])
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('execute')
			->will($this->returnValue(true));

		$this->assertTrue($fixture->insert($db));
	}

/**
 * test the insert method
 *
 * @return void
 */
	public function testInsertStrings() {
		$fixture = new StringsTestFixture();

		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$query = $this->getMock('Cake\Database\Query', [], [$db]);
		$db->expects($this->once())
			->method('newQuery')
			->will($this->returnValue($query));

		$query->expects($this->once())
			->method('insert')
			->with('strings', ['name', 'email', 'age'], ['string', 'string', 'integer'])
			->will($this->returnSelf());

		$expected = [
			['name' => 'Mark Doe', 'email' => 'mark.doe@email.com', 'age' => null],
			['name' => 'John Doe', 'email' => 'john.doe@email.com', 'age' => 20],
			['name' => 'Jane Doe', 'email' => 'jane.doe@email.com', 'age' => 30],
		];
		$query->expects($this->at(1))
			->method('values')
			->with($expected[0])
			->will($this->returnSelf());
		$query->expects($this->at(2))
			->method('values')
			->with($expected[1])
			->will($this->returnSelf());
		$query->expects($this->at(3))
			->method('values')
			->with($expected[2])
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('execute')
			->will($this->returnValue(true));

		$this->assertTrue($fixture->insert($db));
	}

/**
 * Test the drop method
 *
 * @return void
 */
	public function testDrop() {
		$fixture = new ArticleFixture();

		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$db->expects($this->once())
			->method('execute')
			->with('sql');

		$table = $this->getMock('Cake\Database\Schema\Table', [], ['articles']);
		$table->expects($this->once())
			->method('dropSql')
			->with($db)
			->will($this->returnValue(['sql']));
		$fixture->schema($table);

		$this->assertTrue($fixture->drop($db));
	}

/**
 * Test the truncate method.
 *
 * @return void
 */
	public function testTruncate() {
		$fixture = new ArticleFixture();

		$db = $this->getMock('Cake\Database\Connection', [], [], '', false);
		$db->expects($this->once())
			->method('execute')
			->with('sql');

		$table = $this->getMock('Cake\Database\Schema\Table', [], ['articles']);
		$table->expects($this->once())
			->method('truncateSql')
			->with($db)
			->will($this->returnValue(['sql']));
		$fixture->schema($table);

		$this->assertTrue($fixture->truncate($db));
	}

}
