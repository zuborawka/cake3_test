<?php
/**
 * PagesControllerTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case.Controller
 * @since         CakePHP(tm) v 1.2.0.5436
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Test\TestCase\Controller;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use TestApp\Controller\PagesController;

/**
 * PagesControllerTest class
 *
 * @package       Cake.Test.Case.Controller
 */
class PagesControllerTest extends TestCase {

/**
 * testDisplay method
 *
 * @return void
 */
	public function testDisplay() {
		$Pages = new PagesController(new Request(), new Response());

		$Pages->viewPath = 'Posts';
		$Pages->display('index');
		$this->assertRegExp('/posts index/', $Pages->response->body());
		$this->assertEquals('index', $Pages->viewVars['page']);

		$Pages->viewPath = 'Themed';
		$Pages->display('TestTheme', 'Posts', 'index');
		$this->assertRegExp('/posts index themed view/', $Pages->response->body());
		$this->assertEquals('TestTheme', $Pages->viewVars['page']);
		$this->assertEquals('Posts', $Pages->viewVars['subpage']);
	}

/**
 * Test that missing view renders 404 page in production
 *
 * @expectedException Cake\Error\NotFoundException
 * @expectedExceptionCode 404
 * @return void
 */
	public function testMissingView() {
		Configure::write('debug', 0);
		$Pages = new PagesController(new Request(), new Response());
		$Pages->display('non_existing_page');
	}

/**
 * Test that missing view in debug mode renders missing_view error page
 *
 * @expectedException Cake\Error\MissingViewException
 * @expectedExceptionCode 500
 * @return void
 */
	public function testMissingViewInDebug() {
		Configure::write('debug', 1);
		$Pages = new PagesController(new Request(), new Response());
		$Pages->display('non_existing_page');
	}
}
