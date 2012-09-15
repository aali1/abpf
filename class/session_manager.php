<?php
	
	/**
	 *	@package ABPF
	 */
	class SessionManager extends ABPFClass {
		
		/**
		 *	Create a session manager and start a new session
		 */
		public function __construct() {
			$session = session_id();
			if(empty($session)) {
				session_start();
			}
		}
		
		
		/**
		 *	Set a session variable
		 *	@param		string	$key
		 *	@param		mixed	$default_val	If $key is not set in the session, set 
		 *										it as $default_val and return it
		 *	@return		mixed 					Value stored in the session
		 */
		public function get($key, $default_val = null) {
			return isset($_SESSION['__abpf'][$key]) ? $_SESSION['__abpf'][$key] : $this->set($key, $default_val);
		}
		
		
		/**
		 *	Set a session variable
		 *	@param		string	$key
		 *	@param		mixed	$val
		 *	@return		mixed	$val
		 */
		public function set($key, $val) {
			$_SESSION['__abpf'][$key] = $val;
			return $val;
		}
		
	}
	