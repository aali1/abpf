<?php
	
	define('LOGGER_ERROR',	1);
	define('LOGGER_DEBUG',	2);
	define('LOGGER_INFO',	4);
	define('LOGGER_TRACE',	8);
	define('LOGGER_FUNC',	16);
	define('LOGGER_SQL',	32);
	
	/**
	 *	@package ABPF
	 */
	class Logger extends ABPFClass {
		
		private $level = 0;
		private $location = null;
		
		/**
		 *	Instanciate a new Logger
		 *	@param	int $level			Set the site logging level
		 *	@param	string $location	Set the location of the logging file (defaults to php error log)
		 */
		public function __construct($level, $location = null) {
			$this->level = $level;
			$this->location = $location;
		}
		
		/**
		 *	Log an error
		 *	@param		string $msg
		 *	@return		bool		True if ERROR logging is enabled, false otherwise
		 */
		public function error($msg = '') {
			return $this->log($msg, LOGGER_ERROR);
		}
		
		/**
		 *	Log a debug message
		 *	@param		string $msg
		 *	@return		bool		True if DEBUG logging is enabled, false otherwise
		 */
		public function debug($msg = '') {
			return $this->log($msg, LOGGER_DEBUG);
		}
		
		/**
		 *	Log a system message
		 *	@param		string $msg
		 *	@return		bool		True if INFO logging is enabled, false otherwise
		 */
		public function info($msg = '') {
			return $this->log($msg, LOGGER_INFO);
		}
		
		/**
		 *	Log an error
		 *	@param		string $msg
		 *	@return		bool		True if TRACE logging is enabled, false otherwise
		 */
		public function trace($msg = '') {
			return $this->log($msg, LOGGER_TRACE);
		}
		
		/**
		 *	Log an error
		 *	@param		string $msg
		 *	@return		bool		True if FUNC logging is enabled, false otherwise
		 */
		public function func() {
			if(!($this->level & LOGGER_FUNC)) {
				return;
			}
			return $this->log($msg, LOGGER_FUNC);
		}
		
		/**
		 *	Log an error
		 *	@param		string $msg
		 *	@return		bool		True if SQL logging is enabled, false otherwise
		 */
		public function sql($sql = '') {
			return $this->log($sql, LOGGER_DEBUG);
		}
		
		/**
		 *	Output strings to the log
		 *	@param		string		$msg
		 *	@param		int			$level
		 *	@return		bool		True if $level logging is enabled, false otherwise
		 */
		private function log($msg, $level) {
			if(!($this->level & $level)) {
				return false;
			}
			if(empty($msg)) {
				return true;
			}
			
			if($this->location == 'echo') {
				echo sprintf('%s: %s', $this->getLevelString($level), $msg);
			} else if($this->location) {
				$msg = sprintf('[%s] %s: %s', date('d-M-Y H:i:s'), $this->getLevelString($level), $msg);
				error_log($msg, 3, $this->location);
			} else {
				$msg = sprintf('%s: %s', $this->getLevelString($level), $msg);
				error_log($msg);
			}
			return true;
		}
		
		private function getLevelString($level) {
			switch($level) {
				case LOGGER_ERROR:
					return 'ERROR';
				case LOGGER_DEBUG:
					return 'DEBUG';
				case LOGGER_INFO:
					return 'INFO';
				case LOGGER_TRACE:
					return 'TRACE';
				case LOGGER_FUNC:
					return 'FUNC';
				case LOGGER_SQL:
					return 'SQL';
			}
			return '';
		}
	}
	