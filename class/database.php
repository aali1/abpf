<?php
	
	/**
	 *	@package ABPF
	 *	@subpackage Database
	 */
	interface Database {
		
		public function connect();
		public function close();
		public function selectSchema($schema);
		
		public function execute($query, Pagination $pagination = null);
		public function escape($str);
		public function quote($v, $empty_is_null = false);
		public function lastInsertId();
		public function numRows();
		public function affectedRows();
		
		public function loadResult($query, $class_name = null);
		public function loadResultArray($query, $class_name = null, $array_key = null, Pagination $pagination = null);
		public function loadSingleResult($query);
		public function loadSingleResultArray($query, Pagination $pagination = null);
		
		public function startTransaction();
		public function commitTransaction();
		public function rollbackTransaction();
		
	}
	