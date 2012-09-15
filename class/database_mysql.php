<?php
	
	import('abpf/class/database.php');
	
	/**
	 *	@package ABPF
	 *	@subpackage Database
	 */
	class MysqlDatabase extends ABPFClass implements Database {
		
		public $connection_id;
		private $result;
		
		/**
		 *	Create a new database instance - connect to a MySQL database
		 */
		public function __construct() {
			$this->connect();
		}
		
		
		/**
		 *	Close database connection when cleaning up
		 */
		public function __destruct() {
			$this->close();
		}
		
		
		/**
		 *	Open a new connection to a MySQL database
		 *	@throws		DatabaseConnectionException
		 */
		public function connect() {
			$host 	= ABPF::config('db_hostname');
			$user 	= ABPF::config('db_username');
			$pass 	= ABPF::config('db_password');
			$schema = ABPF::config('db_schema');
			
			$this->connection_id = mysql_connect($host, $user, $pass, $new_link = true);
			if(!$this->connection_id) {
				throw new DatabaseConnectionException('Could not open connection to database server ' . $host);
			}
			
			$this->selectSchema($schema);
		}
		
		
		/**
		 *	Close the current connection to the database
		 *	@throws		DatabaseConnectionException
		 */
		public function close() {
			$result = mysql_close($this->connection_id);
			if(!$result) {
				throw new DatabaseConnectionException('Could not close database connection #' . $this->connection_id);
			}
		}
		
		
		/**
		 *	Select a database schema to use
		 * 	@param		string $schema		The name of the schema to use
		 * 	@throws		DatabaseConnectionException
		 */
		public function selectSchema($schema) {
			$result = mysql_select_db($schema, $this->connection_id);
			if(!$result) {
				throw new DatabaseConnectionException('Could select schema ' . $schema);
			}
		}
		
		
		/**
		 *	Execute a MySQL query
		 *	@param	string		$query
		 *	@param	Pagination	$pagination
		 */
		public function execute($query, Pagination $pagination = null) {
			
			// Pagination - check $pagination passed is instance of Pagination
			$paginate = !is_null($pagination);
			if($paginate) {
				// Inject SQL_CALC_ROWS_FOUND into query
				$query = preg_replace('/SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $query, $limit = 1);
				
				// Add limiting sql at end of query
				$query .= $pagination->calcSQLLimitClause();
			}
			
			ABPF::logger()->sql($query);
			
			$result = mysql_query($query);
			$this->result = $result;
			
			if (!$result) {			
				$msg = 
					"DB query failed: $query - " . mysql_error() . "\n" . 
					print_r(debug_backtrace(), true);
				
				if(ABPF::config('die_on_fail')) {
					die('<pre style="color: red;">' . $msg . '</pre>');
				}
				
				ABPF::logger()->error($msg);
				return false;
			}
			
			if($paginate) {
				// calculate the total number of records (without sql limiting) and hence 
				// total number of pages
				$pagination->calcTotalRecords($this->numRows());
			}
			
			return $result;
		}
		
		
		/**
	     *	Prepare a variable for insertion into a database
	     * 	@param	mixed	$v
	     * 	@param	bool	$empty_is_null	If true, anything that is empty() will return the string 'NULL'
	     * 	@return	mixed	$v ready for insertion into a database
	     */
	    public function quote($v, $empty_is_null = false) {
	    	if(is_null($v) || ($empty_is_null && empty($v))) {
	    		return 'NULL';
	    	}
	    	if(preg_match('/^[1-9]\d*(\.\d+)?$/', $v)) {
	    		return $v;
	    	}
	    	return sprintf('"%s"', $this->escape($v));
	    }
		
		
		/**
		 *	Prepare a string for insertion into the database
		 *	@param	string $str		The string to prep
		 *	@return	string			A db sanitised version of $str 
		 */
		public function escape($str) {
			return mysql_real_escape_string($str);
		}
		
		
		/**
		 *	Load an object resulting from a database query
		 *	@param		string $query		The SQL to execute
		 *	@param		string $class_name
		 *	@return		Object|false		Object, else false if query fails.
		 */
		public function loadResult($query, $class_name = null) {
			$result = $this->execute($query);
			if(!$result) {
				return false;
			}
			if($class_name) {
				$obj = mysql_fetch_object($result, $class_name);
				$this->castObjectMembers($obj);
				return $obj;
			}
			$obj = mysql_fetch_object($result, $class_name);
			$this->castObjectMembers($obj);
			return $obj;
		}
		
		
		/**
		 *	Load an array of objects resulting from a database query
		 *	@param		string $query		The SQL to execute
		 *	@param		string $class_name
		 *	@param		array $array_key
		 *	@param		Pagination $pagination
		 *	@return		array				Array of objects, empty array if query fails or no results found
		 */
		public function loadResultArray($query, $class_name = null, $array_key = null, Pagination $pagination = null) {
			$result = $this->execute($query, $pagination);
			if(!$result) {
				return array();
			}
			$results = array();
			$i = 0;
			if($class_name) {
				while($row = mysql_fetch_object($result, $class_name)) {
					$this->castObjectMembers($row);
					$key = $array_key ? $row->$array_key : $i++;
					$results[$key] = $row;
				}
			} else {
				while($row = mysql_fetch_object($result)) {
					$this->castObjectMembers($row);
					$key = $array_key ? $row->$array_key : $i++;
					$results[$key] = $row;
				}
			}
			return $results;
		}
		
		
		/**
		 *	Load a single result from a query.
		 *	@param	string $query
		 *	@param	Pagination $pagination
		 *	@return	mixed	Result on success, false otherwise
		 */
		public function loadSingleResult($query) {
			$result = $this->execute($query);
			if(!$result) {
				return false;
			}
			list($val) = mysql_fetch_row($result);
			return $val;
		}
		
		
		/**
		 *	Load an array of single results from a query.
		 *	@param	string $query
		 *	@param	Pagination $pagination
		 *	@return	array	Array of results
		 */
		public function loadSingleResultArray($query, Pagination $pagination = null) {
			$result = $this->execute($query, $pagination);
			if(!$result) {
				return array();
			}
			$results = array();
			$i = 0;
			while(list($val) = mysql_fetch_row($result)) {
				$results[] = $val;
			}
			return $results;
		}
		
		
		/**
		 *	Get the AUTO_INCREMENT ID generated from the last INSERT command
		 *	@param	int	Last ID generated
		 */
		public function lastInsertId() {
			return mysql_insert_id($this->connection_id);
		}
		
		
		/**
		 *	Get the number of rows returned by a SELECT statement
		 *	@return	int	number of rows.
		 */
		public function numRows() {
			return mysql_num_rows($this->result);
		}
		
		
		/**
		 *	Get the number of rows affected by an INERT, UPDATE or DELETE query
		 * 	@return	int	number of rows.
		 */
		public function affectedRows() {
			return mysql_affected_rows($this->connection_id);
		}
		
		
		/**
		 *	Start a Database Transaction
		 */
		public function startTransaction() {
			ABPF::logger()->sql('STARTING TRANSACTION');
			return $this->execute('start transaction');
		}

		/**
		 *	Commits the currently open transaction
		 */
		public function commitTransaction() {
			ABPF::logger()->sql('COMMITTING TRANSACTION');
			return $this->execute('commit');
		}

		/**
		 *	Rollback (cancel) the current transaction
		 */
		public function rollbackTransaction() {
			ABPF::logger()->sql('ROLLING BACK TRANSACTION');
			return $this->execute('rollback');
		}
		
		
		/**
		 *	Convert all $obj members that are x_date to a date object
		 *	@param	Object $obj
		 */
		private function castObjectMembers($obj) {
			foreach($obj as $member => $var) {
				if((substr($member, -5) == '_date' || substr($member, -5) == '_time') && !is_null($var)) {
					$obj->$member = new Date($obj->$member);
				} elseif(substr($member, 0, 3) == 'is_') {
					$obj->$member = (bool)$obj->$member;
				}
			}
		}
	}
	