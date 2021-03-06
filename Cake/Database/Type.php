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
namespace Cake\Database;

use Cake\Database\Driver;
use Exception;
use PDO;

/**
 * Encapsulates all conversion functions for values coming from database into PHP and
 * going from PHP into database.
 */
class Type {

/**
 * List of supported database types. A human readable
 * identifier is used as key and a complete namespaced class name as value
 * representing the class that will do actual type conversions.
 *
 * @var array
 */
	protected static $_types = [
		'binary' => 'Cake\Database\Type\BinaryType',
		'date' => 'Cake\Database\Type\DateType',
		'datetime' => 'Cake\Database\Type\DateTimeType',
		'timestamp' => 'Cake\Database\Type\DateTimeType',
		'time' => 'Cake\Database\Type\TimeType'
	];

/**
 * List of basic type mappings, used to avoid having to instantiate a class
 * for doing conversion on these
 *
 * @var array
 */
	protected static $_basicTypes = [
		'float' => ['callback' => 'floatval'],
		'decimal' => ['callback' => 'floatval'],
		'integer' => ['callback' => 'intval', 'pdo' => PDO::PARAM_INT],
		'biginteger' => ['callback' => 'intval', 'pdo' => PDO::PARAM_INT],
		'string' => ['callback' => 'strval'],
		'uuid' => ['callback' => 'strval'],
		'text' => ['callback' => 'strval'],
		'boolean' => [
			'callback' => ['\Cake\Database\Type', 'boolval'],
			'pdo' => PDO::PARAM_BOOL
		],
	];

/**
 * Contains a map of type object instances to be reused if needed
 *
 * @var array
 */
	protected static $_builtTypes = [];

/**
 * Identifier name for this type
 *
 * @var string
 */
	protected $_name = null;

/**
 * Constructor
 *
 * @param string $name The name identifying this type
 * @return void
 */
	public function __construct($name = null) {
		$this->_name = $name;
	}

/**
 * Returns a Type object capable of converting a type identified by $name
 *
 * @param string $name type identifier
 * @throws \InvalidArgumentException If type identifier is unknown
 * @return Type
 */
	public static function build($name) {
		if (isset(static::$_builtTypes[$name])) {
			return static::$_builtTypes[$name];
		}
		if (isset(static::$_basicTypes[$name])) {
			return static::$_builtTypes[$name] = new static($name);
		}
		if (!isset(static::$_types[$name])) {
			throw new \InvalidArgumentException(__d('cake_dev', 'Unknown type "%s"', $name));
		}
		return static::$_builtTypes[$name] = new static::$_types[$name]($name);
	}

/**
 * Registers a new type identifier and maps it to a fully namespaced classname,
 * If called with no arguments it will return current types map array
 * If $className is omitted it will return mapped class for $type
 *
 * @param string|array $type if string name of type to map, if array list of arrays to be mapped
 * @param string $className
 * @return array|string|null if $type is null then array with current map, if $className is null string
 * configured class name for give $type, null otherwise
 */
	public static function map($type = null, $className = null) {
		if ($type === null) {
			return self::$_types;
		}
		if (!is_string($type)) {
			self::$_types = $type;
			return;
		}
		if ($className === null) {
			return isset(self::$_types[$type]) ? self::$_types[$type] : null;
		}
		self::$_types[$type] = $className;
	}

/**
 * Clears out all created instances and mapped types classes, useful for testing
 *
 * @return void
 */
	public static function clear() {
		self::$_types = [];
		self::$_builtTypes = [];
	}

/**
 * Returns type identifier name for this object
 *
 * @return string
 */
	public function getName() {
		return $this->_name;
	}

/**
 * Casts given value from a PHP type to one acceptable by database
 *
 * @param mixed $value value to be converted to database equivalent
 * @param Driver $driver object from which database preferences and configuration will be extracted
 * @return mixed
 */
	public function toDatabase($value, Driver $driver) {
		return $this->_basicTypeCast($value, $driver);
	}

/**
 * Casts given value from a database type to PHP equivalent
 *
 * @param mixed $value value to be converted to PHP equivalent
 * @param Driver $driver object from which database preferences and configuration will be extracted
 * @return mixed
 */
	public function toPHP($value, Driver $driver) {
		return $this->_basicTypeCast($value, $driver);
	}

/**
 * Checks whether this type is a basic one and can be converted using a callback
 * If it is, returns converted value
 *
 * @param mixed $value value to be converted to PHP equivalent
 * @param Driver $driver object from which database preferences and configuration will be extracted
 * @return mixed
 */
	protected function _basicTypeCast($value, Driver $driver) {
		if (is_null($value)) {
			return null;
		}

		if (!empty(self::$_basicTypes[$this->_name])) {
			$typeInfo = self::$_basicTypes[$this->_name];
			if (isset($typeInfo['callback'])) {
				return $typeInfo['callback']($value);
			}
		}
		return $value;
	}

/**
 * Casts give value to Statement equivalent
 *
 * @param mixed $value value to be converted to PHP equivalent
 * @param Driver $driver object from which database preferences and configuration will be extracted
 * @return mixed
 */
	public function toStatement($value, Driver $driver) {
		if (is_null($value)) {
			return PDO::PARAM_NULL;
		}

		if (!empty(self::$_basicTypes[$this->_name])) {
			$typeInfo = self::$_basicTypes[$this->_name];
			return isset($typeInfo['pdo']) ? $typeInfo['pdo'] : PDO::PARAM_STR;
		}

		return PDO::PARAM_STR;
	}

/**
 * Type converter for boolean values.
 *
 * Will convert string true/false into booleans.
 *
 * @param mixed $value The value to convert to a boolean.
 * @return boolean
 */
	public static function boolval($value) {
		if (is_string($value)) {
			return strtolower($value) === 'true' ? true : false;
		}
		return !empty($value);
	}

}
