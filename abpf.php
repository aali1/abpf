<?php

	error_reporting(E_ALL);

	if(!defined('ABPF_LOADED')) {
		define('ABPF_LOADED', 		true);

		// Set ABPF Definitions
		define('WEB_ROOT', 			$_SERVER['DOCUMENT_ROOT']);
		define('BASE_URL', 			'http://' . $_SERVER['SERVER_NAME']);
		define('ABPF_ROOT',			dirname(__FILE__));
		define('DS', 				DIRECTORY_SEPARATOR);
	}

	require_once ABPF_ROOT . DS . 'functions.php';

	import('abpf/class/class.php');
	import('abpf/class/exception.php');
	import('abpf/class/model.php');
	import('abpf/class/date.php');
	import('abpf/class/pagination.php');
	import('abpf/autoload.php');

	/**
	 *	The ABPF Class
	 *	@package ABPF
	 */
	class ABPF {

		/**
		 *	Get a config variable
		 *	@return	string|null	Return the value of $var as defined in config.ini or NULL if not defined
		 *	@static
		 */
		public static function config($var) {
			static $config = null;
			if(is_null($config)) {
				if(!defined('CONFIG_FILE')) {
					define('CONFIG_FILE', ABPF_ROOT . DS . 'config.ini');
				}
				$config = @parse_ini_file(CONFIG_FILE);
				if($config === false) {
					throw new Exception('Could not open configuration file "' . CONFIG_FILE . '"');
				}
			}
			if(!isset($config[$var])) {
				return null;
			}
			return $config[$var];
		}


		/**
		 *	Get a database connection
		 * 	@return	MysqlDatabase
		 *	@static
		 */
		static public function db() {
			static $db_instance = null;
			if(is_null($db_instance)) {
				import('abpf/class/database_mysql.php');
				$db_instance = new MysqlDatabase();
			}
			return $db_instance;
		}


		/**
		 *	Get a logger instance
		 * 	@return	Logger
		 *	@static
		 */
		static public function logger() {
			static $logger_instance = null;
			if(is_null($logger_instance)) {
				import('abpf/class/logger.php');
				$logger_instance = new Logger(ABPF::config('debug_level'), ABPF::config('log_location'));
			}
			return $logger_instance;
		}


		/**
		 *	Handle session variables
		 *	@return	SessionManager
		 *	@static
		 */
		static public function session() {
			static $session_manager_instance = null;
			if(is_null($session_manager_instance)) {
				import('abpf/class/session_manager.php');
				$session_manager_instance = new SessionManager();
			}
			return $session_manager_instance;
		}

		/**
		 *	HTML formatter
		 *	@return		HTML
		 *	@static
		 */
		static public function html() {
			static $instance = null;
			if(is_null($instance)) {
				import('abpf/class/html.php');
				$instance = new HTML();
			}
			return $instance;
		}

		/**
		 *	SMS Sender - smsm.co
		 *	@return		SMSM
		 *	@static
		 */
		static public function sms() {
			static $instance = null;
			if(is_null($instance)) {
				import('abpf/class/sms_smsm.php');
				$instance = new SMSM();
			}
			return $instance;
		}
	}
