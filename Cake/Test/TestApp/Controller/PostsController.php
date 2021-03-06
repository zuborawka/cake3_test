<?php
/**
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
namespace TestApp\Controller;

use TestApp\Controller\AppController;

/**
 * PostsController class
 *
 * @package TestApp.Controller
 */
class PostsController extends AppController {

	public $name = 'Posts';

/**
 * Components array
 *
 * @var array
 */
	public $components = array(
		'RequestHandler',
		'Auth'
	);
}
