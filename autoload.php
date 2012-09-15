<?php
	
	/**
	 *	Try and autoload a class definition
	 */
	function __autoload($class) {
		if(!defined('MODEL_DIR')) {
			ABPF::logger()->debug('MODEL_DIR not defined.');
			return;
		}
		$class_file = sprintf('%s/%s.php', MODEL_DIR, from_camel_case($class));
		if(file_exists($class_file)) {
			include $class_file;
			ABPF::logger()->debug($class . ' autoloaded');
			return;
		}
		ABPF::logger()->debug($class . ' could not be autoloaded');
	}
	