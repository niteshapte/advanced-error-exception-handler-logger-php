<?php
namespace Utilities;
use FrameworkException;

if(!defined('DIRECT_ACCESS')) {
	die("Direct access is forbidden.");
}

/**
 * 
 * SINGLETON TRAIT
 * 
 * Singleton trait for the singleton classes.
 *  
 * @package Utilities
 * @author Nitesh Apte <me@niteshapte.com>
 * @copyright 2015 Nitesh Apte
 * @since 1.0.0
 * @version 1.0.1
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
trait SingletonTrait {
	
	private static $instance;
	
	/**
	 * Create the single instance of class
	 *
	 * @param none
	 * @return Object self::$singleInstance Instance
	 */
	public static function getInstance() {
		if(!self::$instance instanceof self) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Keep the constructor private
	 */
	private function __construct() { }
	
	/**
	 * Stop serialization
	 *
	 * @throws ApplicationException
	 */
	public function __sleep() {
		throw new FrameworkException('Serializing instances of this class is forbidden.');
	}
	
	/**
	 * Stop serialization
	 *
	 * @throws ApplicationException
	 */
	public function __wakeup() {
		throw new FrameworkException('Unserializing instances of this class is forbidden.');
	}
	
	/**
	 * Override clone method to stop cloning of the object
	 *
	 * @throws ApplicationException
	 */
	private function __clone() {
		throw new FrameworkException("Cloning is not supported in singleton class.");
	}
}
