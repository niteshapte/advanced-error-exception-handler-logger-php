<?php
namespace Utilities;
use SingletonTrait;

if(!defined('DIRECT_ACCESS')) {
	die("Direct access is forbidden.");
}

/**
 * ERROR EXCEPTION HANDLER
 *
 * This class handles and logs the errors and exception occurs in the project.
 *  
 * @package Utilities
 * @author Nitesh Apte <me@niteshapte.com>
 * @copyright 2015 Nitesh Apte
 * @version 2.0.0
 * @since 1.0.0
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
class ErrorExceptionHandler implements IUtilities {
	
	// Singleton instance
	use SingletonTrait;
	
	/**
	 * @var $MAXLENGTH Maximum length for backtrace message
	 * @see debugBacktrace()
	 */
	private $MAXLENGTH = 64;
	
	/**
	 * @var $errorType PHP defined errors
	 * @see customError()
	 */
	private $errorType = array (
			E_ERROR           	=> 'ERROR',
			E_WARNING         	=> 'WARNING',
			E_PARSE           	=> 'PARSING ERROR',
			E_NOTICE          	=> 'NOTICE',
			E_CORE_ERROR      	=> 'CORE ERROR',
			E_CORE_WARNING    	=> 'CORE WARNING',
			E_COMPILE_ERROR   	=> 'COMPILE ERROR',
			E_COMPILE_WARNING 	=> 'COMPILE WARNING',
			E_USER_ERROR      	=> 'USER ERROR',
			E_USER_WARNING    	=> 'USER WARNING',
			E_USER_NOTICE     	=> 'USER NOTICE',
			E_STRICT 		  	=> 'STRICT',
			E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
			E_DEPRECATED 		=> 'DEPRECATED',
			E_USER_DEPRECATED 	=> 'USER_DEPRECATED'
	);
	
	/**
	 * @var array $exceptionType Custom exception number
	 * @see exceptionHandler
	 */
	private $exceptionType = array (
			1001				=> 'INCORRECT FORMAT',
			1002				=> 'INVALID FILE'
			// Put whatever you want. But make sure you are following a pattern of code that can be understood easily.
	);
	
	/**
	 * Initiate the handlers
	 * 
	 * @param none
	 * @return none
	 */
	private function __construct() {
		$this->enableHandler();
	}
	
	/**
	 * Set custom error handler and exception handler
	 *
	 * @param String $requestFrom
	 * @return none
	 */
	public function enableHandler() {
		\error_reporting(1);
		\set_error_handler(array($this,'errorHandler'), APP_ERROR);
		\set_exception_handler(array($this,	'exceptionHandler'));
		\register_shutdown_function(array($this, 'fatalError'));
	}
	
	/**
	 * Custom error logging in custom format
	 *
	 * @param int $errNo Error number
	 * @param string $errStr Error string
	 * @param string $errFile Error file
	 * @param int $errLine Error line
	 * @return void
	 */
	public function errorHandler($errNo, $errStr, $errFile, $errLine) {
	
		if(error_reporting() == 0) {
			return;
		}
		$backTrace = $this->debugBacktrace(2);
		
		$logMessage = $this->_toStringForLogging($errNo, $errStr, $errFile, $errLine, $backTrace, 'Error', $this->errorType[$errNo]);
		$webMessage = $this->_toStringForWeb($errNo, $errStr, $errFile, $errLine, $backTrace, 'Error', $this->errorType[$errNo]);
		
		$this->debug($logMessage, $webMessage);
	}
	
	/**
	 * Custom exception handler
	 * 
	 * @param \Exception $exception
	 * @return void
	 */
	public function exceptionHandler(\Exception $exception) {
		while ($e = $exception->getPrevious()) {
			$exception = $e;
		}
		
		$logMessage = $this->_toStringForLogging($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString(), 'Exception', $this->exceptionType[$exception->getCode()]);
		$webMessage = $this->_toStringForWeb($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString(), 'Exception', $this->exceptionType[$exception->getCode()]);
		$this->debug($logMessage, $webMessage);
	}
	
	/**
	 * Display the error
	 * 
	 * @param string $logMessage Message for log file
	 * @param string $webMessage Message to show on browser
	 * @return void
	 */
	private function debug($logMessage, $webMessage) {
		SEND_ERROR_MAIL === TRUE ? error_log($logMessage, 1, ADMIN_ERROR_MAIL, "From: ".SEND_ERROR_FROM."\r\nTo: ".ADMIN_ERROR_MAIL) : "";
		ERROR_LOGGING === TRUE ? error_log($logMessage, 3, LOG_FILE_PATH) : "";
		echo DEBUGGING === TRUE ? $webMessage : SITE_GENERIC_ERROR_MSG;
		MODE == 'DEVELOPMENT' ? exit() : "";
	}

	/**
	 * Build backtrace message
	 *
	 * @param $entriesMade Irrelevant entries in debug_backtrace, first two characters
	 * @return string
	 */
	private function debugBacktrace($entriesMade) {
	
		$traceArray = debug_backtrace();
		$argsDefine = array();
	
		$traceMessage = '';
	
		for($i=0;$i<$entriesMade;$i++) {
			array_shift($traceArray);
		}
	
		foreach($traceArray as $newArray) {
			if(isset($newArray['class'])) {
				$traceMessage .= $newArray['class'].'.';
			}
			if(!empty($newArray['args'])) {
	
				foreach($newArray['args'] as $newValue) {
					if(is_null($newValue)) {
						$argsDefine[] = NULL;
					} elseif(is_array($newValue)) {
						$argsDefine[] = 'Array['.sizeof($newValue).']';
					} elseif(is_object($newValue)) {
						$argsDefine[] = 'Object: '.get_class($newValue);
					} elseif(is_bool($newValue)) {
						$argsDefine[] = $newValue ? 'TRUE' : 'FALSE';
					} else {
						$newValue = (string)@$newValue;
						$stringValue = htmlspecialchars(substr($newValue, 0, $this->MAXLENGTH));
						if(strlen($newValue)>$this->MAXLENGTH) {
							$stringValue = '...';
						}
						$argsDefine[] = "\"".$stringValue."\"";
					}
				}
			}
			$traceMessage .= $newArray['function'].'('.implode(',', $argsDefine).')';
			$lineNumber = (isset($newArray['line']) ? $newArray['line']:"unknown");
			$fileName = (isset($newArray['file']) ? $newArray['file']:"unknown");
	
			$traceMessage .= sprintf(" # line %4d. file: %s", $lineNumber, $fileName, $fileName);
			$traceMessage .= "\n";
		}
		return $traceMessage;
	}
	
	/**
	 * Method to catch fatal and parse error
	 *
	 * @param none
	 * @return none
	 */
	public function fatalError() {
		$lastError =  error_get_last();
		if($lastError['type'] == 1 || $lastError['type'] == 4 || $lastError['type'] == 16 || $lastError['type'] == 64 || $lastError['type'] == 256 || $lastError['type'] == 4096) {
			$this->errorHandler($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
		}
	}
	
	/**
	 * Decorate the message for browser
	 *
	 * @param int $errNo
	 * @param string $errStr
	 * @param string $errFile
	 * @param int $errLine
	 * @param string $backTrace
	 * @param string $category
	 * @return string
	 */
	private function _toStringForWeb($errNo, $errStr, $errFile, $errLine, $backTrace, $category, $type) {
		$css = <<<EOT
		<style>
		.errormessage {
			margin:0px;padding:0px;
			width:100%;
		}
		.errormessage table{
		    border-collapse: collapse;
		    border-spacing: 0;
			width:100%;
			margin:0px;padding:0px;
		}
		.errormessage tr:last-child td:last-child {
			-moz-border-radius-bottomright:0px;
			-webkit-border-bottom-right-radius:0px;
			border-bottom-right-radius:0px;
		}
		.errormessage table tr:first-child td:first-child {
			-moz-border-radius-topleft:0px;
			-webkit-border-top-left-radius:0px;
			border-top-left-radius:0px;
		}
		.errormessage table tr:first-child td:last-child {
			-moz-border-radius-topright:0px;
			-webkit-border-top-right-radius:0px;
			border-top-right-radius:0px;
		}
		.errormessage tr:last-child td:first-child{
			-moz-border-radius-bottomleft:0px;
			-webkit-border-bottom-left-radius:0px;
			border-bottom-left-radius:0px;
		}
		.errormessage tr:hover td{
	
		}
		.errormessage tr:nth-child(odd){
			background-color:#e5e5e5;
		}
		.errormessage tr:nth-child(even) {
			background-color:#ffffff;
		}.errormessage td {
			vertical-align:middle;
			border:1px solid #000000;
			border-width:0px 1px 1px 0px;
			text-align:left;
			padding:5px;
			font-size:12px;
			font-family:Arial;
			font-weight:normal;
			color:#000000;
		}.errormessage tr:last-child td{
			border-width:0px 1px 0px 0px;
		}.errormessage tr td:last-child{
			border-width:0px 0px 1px 0px;
		}.errormessage tr:last-child td:last-child{
			border-width:0px 0px 0px 0px;
		}
		.errorhead {
			font: 20px Arial;
			margin: 5px 0 10px 0;
			font-weight: bold;
		}
		</style>
EOT;
	
		$errorMessage = "<title>Website Generic Error and Exception Application - {$type}</title><div class='errorhead'>Website Generic Error and Exception Application</div><div class='errormessage'><table border=1>";
		$errorMessage .= "<tr><td><b>CATEGORY : </b></td><td><font color='red'>{$category}</font></td></tr>";
		$errorMessage .= "<tr><td><b>ERROR NO : </b></td><td><font color='red'>{$errNo}</font></td></tr>";
		$errorMessage .= "<tr><td><b>ERROR TYPE : </b></td><td><i><b><font color='red'>{$type}</font></b></i></td></tr>";
		$errorMessage .= "<tr><td><b>TEXT : </b></td><td><font color='red'>{$errStr}</font></td></tr>";
		$errorMessage .= "<tr><td><b>LOCATION : </b></td><td><font color='red'>{$errFile}</font>, <b>line</b> {$errLine}, at ".date("F j, Y, g:i a")."</td></tr>";
		$errorMessage .= "<tr><td width='120px'><b>Showing Backtrace : </b></td><td>{$backTrace} </td></tr></table></div>";
		$webMessage = str_replace("#", "<br />", $errorMessage);
		$webMessage = $css.$webMessage;
		return $webMessage;
	}
	
	/**
	 * Decorate the message for log file
	 *
	 * @param int $errNo
	 * @param string $errStr
	 * @param string $errFile
	 * @param int $errLine
	 * @param string $backTrace
	 * @param string $category
	 * @return string
	 */
	private function _toStringForLogging($errNo, $errStr, $errFile, $errLine, $backTrace, $category, $type) {
		$logMessage = "=====================================================================================================================================\n";
		$logMessage .= "Website Generic Error!\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "CATEGORY : {$category}\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "ERROR NO : {$errNo}\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "ERROR TYPE : {$type}\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "TEXT : {$errStr}\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "LOCATION : {$errFile}, line {$errLine}, at ".date("F j, Y, g:i a")."\n";
		$logMessage .= "=====================================================================================================================================\n";
		$logMessage .= "Showing Backtrace : \n{$backTrace} \n";
		$logMessage .= "=====================================================================================================================================\n\n";
		return $logMessage;
	}
}
