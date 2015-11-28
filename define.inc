<?php 
if(!defined('DIRECT_ACCESS')) {
	die("Direct access is forbidden.");
}

/**
 * Global Default values that to be used throughout the project
 *
 * @author Nitesh Apte
 * @copyright 2015
 * @version 2.0
 * @license GPL v3
 */

#-- SETTING FOR FRAMEWORK STARTS --#

define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']."/");

#-- SETTING FOR FRAMEWORK ENDS --#

########################################################################################################

#-- SETTINGS FOR FRAMEWORK HANDLER STARTS --#

define('MODE', 'DEVELOPMENT');
define('APP_ERROR', E_ALL); // Development mode
define('DEBUGGING', TRUE); // Development mode
define('ADMIN_ERROR_MAIL', 'administrator@example.com');
define('SEND_ERROR_MAIL', FALSE);
define('SEND_ERROR_FROM', 'errors@example.com');
define('ERROR_LOGGING', TRUE);
define('LOG_FILE_PATH', BASE_PATH.'errors.log'); // Please provide 777 permission to this folder
define('SITE_GENERIC_ERROR_MSG', '<h1>Something wrong, sorry. Error!</h1>');

#-- SETTINGS FOR FRAMEWORK HANDLER ENDS --#

########################################################################################################
?>
