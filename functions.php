<?php
	
	
	/**
	 *	Import a class definition
	 *	@param		string		$file
	 */
	function import($file) {
		static $imports = array();
		if(isset($imports[$file])) {
			return;
		}
		$parts = explode('/', $file);
		if($parts[0] == 'abpf') {
			$parts[0] = ABPF_ROOT;
		} else {
			array_unshift($parts, WEB_ROOT);
		}
		$file = implode(DS, $parts);
		
		require $file;
	}
	
	
	/**
	 *	Translates strings with underscores into camel caps E.g. first_name -> firstName
	 *	@param		string		$str						String in underscore format
	 *	@param		bool		$capitalise_first_char		If true, capitalise the first char in $str
	 *	@return		string									$str translated into camel caps
	 */
	function to_camel_case($str, $capitalise_first_char = false) {
		if($capitalise_first_char) {
			$str[0] = strtoupper($str[0]);
		}
		return preg_replace_callback('/_([a-z])/', create_function('$a', 'return strtoupper($a[1]);'), $str);
	}
	
	
	/**
	 *	Translates strings with camel caps into a string with underscores E.g. firstName -> first_name
	 *	@param		string		$str		String in camel caps format
	 *	@return		string					$str translated into underscore format
	 */
	function from_camel_case($str) {
		$str[0] = strtolower($str[0]);
		return preg_replace_callback('/([A-Z])/', create_function('$a', 'return "_" . strtolower($a[1]);'), $str);
	}
	
	
	/**
	 *	Get an array of a class's members
	 *	@param	Object $obj
	 *	@return	array	Array of member names
	 */
	function class_members($obj) {
		$members = array();
		foreach(array_keys(get_class_vars(get_class($obj))) as $member) {
			if($member[0] !== '_') {
				$members[] = $member;
			}
		}
		return $members;
	}
	
	
	/**
	 *	Check that $member is a valid member of $obj
	 *	@param	string $member
	 *	@param	Object $obj
	 *	@return	bool	True if $member is a valid member of $obj, false otherwise
	 */
	function is_member($member, $obj) {
		return property_exists($obj, $member);
	}
	
	
	/**
	 *	Wrap the value of each element of an array in a string (usually quotes)
	 * 	@param	array	$in
	 * 	@return	arrary	$in with each element's value wrapped
	 */
	function wrap($in, $wrapper = '`') {
		if(!is_array($in)) {
			return sprintf('%s%s%s', $wrapper, $in, $wrapper);
		}
		foreach($in as &$val) {
			$val = sprintf('%s%s%s', $wrapper, $val, $wrapper);
		}
		return $in;
	}
	
	
	/**
	 *	Return the current time in mysql's datetime format
	 * 	@return	string	current time in mysql's datetime format
	 */
	function datetime($time = null) {
		return $time ? date('Y-m-d H:i:s', $time) : date('Y-m-d H:i:s');
	}
	
	
	/**
	 *	Redirect a user with PHP's header() function
	 * 	@param	array|string		$url	Either a url or a url array as defined in globals/urls.php
	 *	@param	string|null			$status	Option HTTP status to send
	 */
	function header_redirect($url, $status = null) {
		ABPF::logger()->debug('Header Redirect: ' . $url);
		
		ob_end_clean();
		
		switch($status) {
			case '301':
				header('HTTP/1.1 301 Moved Permanently');
				break;
		}
		
		header('Location: ' . $url);
		exit;
	}
	
	
	/**
	 *	Flatten a multidimensional array
	 * 	@param	array	$multi_array
	 * 	@return	array	Flattened version of $multi_array
	 */
	function array_flatten($in_array) {
		$flat_array = array();
		foreach($in_array as $key => $val) {
			if(is_array($val)) {
				$flat_array = array_merge($flat_array, array_flatten($val));
			} else {
				$flat_array[$key] = $val;
			}
		}
		return $flat_array;
	}
	
	
	/**
	*	Hash a given string using the SHA64 algorithm.
	*	@param	string		The password to hash.
	*	@return	string		The password's SHA64 hash.
	*/
	function hash_password($password) {
		return sha1($password);
	}
	
	
	/**
	 *	Pluck the $member member from $obj and return them all as an array
	 *	@param		object $obj
	 *	@param		string $member
	 *	@return 	array	
	 */
	function array_pluck(array $objs, $member) {
		$results = array();
		foreach($objs as $obj) {
			$results[] = $obj->$member;
		}
		return $results;
	}
	