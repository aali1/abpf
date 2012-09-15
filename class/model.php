<?php
	
	/**
	 *	@package ABPF
	 */
	abstract class Model extends ABPFClass {
		
		protected $_table_name;
		protected $_primary_key;
		protected $_obj_cache = array();
		
		/**
		 *	Method called before saving data to the database
		 *	@return		bool		True if model is valid
		 *	@throws		InvalidModelContentExeption
		 */
		public function validate() {
			return true;
		}
		
		
		/**
		 *	Create a new instance of the model
		 *	@param		int		$id		If set, the model will be pre-popuplated
		 *	@throws		NoSuchModelException
		 */
		public function __construct($id = null) {
			if(is_null($id)) {
				return;
			}
			$key = $this->_primary_key;
			$this->$key = $id;
			if(!$id) {
				throw new ABPFException(get_class($this) . ' has no primary key defined.');
			}
			if(!$this->find($find_first = true)) {
				throw new NoSuchModelException(get_class($this) . ' model with id ' . $id . ' not found.');
			}
		}
		
		
		/**
		 *	Commit the model to the database
		 *	@param		bool	$ignore_nulls If set, NULL values will be skipped when updating the database
		 *	@return		self	Returns itself
		 */
		public function save($ignore_nulls = false) {
			$db = ABPF::db();
			
			// Validate model
			$this->validate();
			
			// Are we updating an existing model or inserting a new one?
			$key = $this->_primary_key;
			$update = isset($this->$key);
			if($update) {
				$sql = $this->update($ignore_nulls);
			} else {
				$sql = $this->insert();
			}
			
			$result = (bool)$db->execute($sql);
			
			if($result && $this->_primary_key) {
				if(!$update) {
					$this->$key = $db->lastInsertId();
				}
			} else {
				throw new DatabaseException('Saving of model failed');
			}
			return $this;
		}
		
		
		/**
		 *	Generate an SQL statement for inserting a new record into the database
		 *	@return		string	An SQL INSERT statement
		 */
		private function insert() {
			$db = ABPF::db();
			
			// Create SET key = value pairs
			$set = array();
			foreach($this as $key => $var) {
				if (!is_null($var) && $key[0] != '_' && is_member($key, $this)) {
					
					// Cater for dates
					if($var instanceof Date) {
						$var = $var->toMySQL();
					}
					
					// Cater for bools
					if(is_bool($var)) {
						$var = (int)$var;
					}
					
					$set[] = sprintf('`%s` = %s', $key, $db->quote($var));
				}
			}
			
			// make sure we're not inserting an empty set
			if (empty($set)) {
				throw new Exception('Will not create empty item, please supply valid data.');
			}
	
			// Create the overall INSERT statement
			$query = sprintf('INSERT INTO `%s` SET %s', $this->_table_name, implode(', ', $set));
			
			return $query;
		}
		
		
		/**
		 *	Generate an SQL statement for updating an existing record in the database
		 *	@param		bool	$ignore_nulls If set, NULL values will be skipped when updating the database
		 *	@return		string	An SQL UPDATE statement
		 */
		private function update($ignore_nulls) {
			$db = ABPF::db();
			
			// Check if model is a child class
			$parent = get_parent_class($this);
			if($parent !== 'Model') {
				$parent = new $parent();
				$table_reference = sprintf('`%s` INNER JOIN `%s` USING (`%s`)', $parent->_table_name, $this->_table_name, $this->_primary_key);
				$parent_vars = class_members($parent);
			} else {
				$table_reference = wrap($this->_table_name);
				$parent = '';
			}
	
			// Create the SET key = value pairs
			$set = array();
			foreach($this as $key => $var) {
				// We don't update the primary key (or the model information fields)
				if ($key == $this->_primary_key || $key[0] == '_' || !is_member($key, $this)) {
					continue;
				}
				
				if($ignore_nulls && $var === null) {
					continue;
				}
				
				// Append table name to vars if we're updating multiple tables (because of inheritence)
				if($parent) {
					$table = sprintf('`%s`.', in_array($key, $parent_vars) ? $parent->_table_name : $this->_table_name);
				} else {
					$table = '';
				}
				
				// Cater for dates
				if($var instanceof Date) {
					$var = $var->toMySQL();
				}
				
				// Cater for bools
				if(is_bool($var)) {
					$var = (int)$var;
				}
				
				$set[] = sprintf('%s`%s` = %s', $table, $key, $db->quote($var));
			}
			
			// make sure we're not updating nothing
			if (empty($set)) {
				throw new Exception('Will not update empty item.');
			}
			
			// Create the UPDATE statement
			$key = $this->_primary_key;
			$query = sprintf('UPDATE %s SET %s WHERE `%s` = %s', $table_reference, implode(', ', $set), $key, $db->quote($this->$key));
			
			return $query;
		}
		
		
		/**
		 *	Search the database for instances of the model that match the set members. Members with null values
		 *	will be ignored in the search, if you want to search for rows with null values, set the member
		 *	to the string 'NULL'. Strings require exact matches.
		 *	$this will be populated with the contents of the first matching model found.
		 *	@param		bool					$find_first		Return the first matching model found
		 *	@param		string					$order			fields to order results by
		 *	@return		array|bool|ABPFModel	If $find_first is True, find() return true if a model was found
		 *										or false if no model is found.
		 *										Otherwise find() will return an array of mathcing models (or an empty
		 *										array if none found)
		 */
		public function find($find_first = false, $order = null, $array_key = null, Pagination $pagination = null) {
			return $this->getMatching($find_first, $order, $array_key, $pagination, $exact_strings = true);
		}

		
		/**
		 *	Search the database for instances of the model that match the set members. Members with null values
		 *	will be ignored in the search, if you want to search for rows with null values, set the member
		 *	to the string 'NULL'. Strings do not require exact matches.
		 *	$this will be populated with the contents of the first matching model found.
		 *	@param		bool					$find_first		Return the first matching model found
		 *	@param		string					$order			fields to order results by
		 *	@return		array|bool|ABPFModel	If $find_first is True, find() return true if a model was found
		 *										or false if no model is found.
		 *										Otherwise find() will return an array of mathcing models (or an empty
		 *										array if none found)
		 */
		public function search($find_first = false, $order = null, $array_key = null, Pagination $pagination = null) {
			return $this->getMatching($find_first, $order, $array_key, $pagination, $exact_strings = false);
		}		

		/**
		 *	Search the database for instances of the model that match the set members. Members with null values
		 *	will be ignored in the search, if you want to search for rows with null values, set the member
		 *	to the string 'NULL'. The function provides a flag to determine whether strings should be matched
		 *  exactly, or result in a match where the search string occurs in the haystack.
		 *	$this will be populated with the contents of the first matching model found.
		 *	@param		bool					$find_first		Return the first matching model found
		 *	@param		string					$order			fields to order results by
		 *  @param		bool					$exact_strings	Whether only match strings on exact match.
		 *	@return		array|bool|ABPFModel	If $find_first is True, find() return true if a model was found
		 *										or false if no model is found.
		 *										Otherwise find() will return an array of mathcing models (or an empty
		 *										array if none found)
		 */		
		private function getMatching($find_first = false, $order = null, $array_key = null, Pagination $pagination = null, $exact_strings = true) {	
			$db = ABPF::db();
			
			// Get field list
			$field_list = implode(', ', wrap(class_members($this)));
			
			// Generate FROM clause
			$parent = get_parent_class($this);
			if($parent != 'Model') {
				$parent = new $parent();
				$from = sprintf('`%s` INNER JOIN `%s` USING (`%s`)', $parent->_table_name, $this->_table_name, $this->_primary_key);
			} else {
				$from = sprintf('`%s`', $this->_table_name);
			}
			
			// Generate WHERE statement
			$where = array();
			foreach($this as $key => $var) {
				if (is_null($var) || $key[0] == '_') {
					continue;
				}
				
				// Cater for dates
				if($var instanceof Date) {
					$var = $var->toMySQL();
				}
				
				// Cater for bools
				if(is_bool($var)) {
					$var = (int)$var;
				}
				
				if(is_array($var)) {
					$where[] = sprintf('`%s` IN (%s)', $key, implode(', ', wrap($var, '"')));
				} else if(!$exact_strings && is_string($var)) {
					$where[] = sprintf('`%s` LIKE "%%%s%%"', $key, $db->escape($var));
				} else if(strtoupper($var) == 'NULL') {
					$where[] = sprintf('`%s` = NULL', $key, $var);
				} else {
					$where[] = sprintf('`%s` = %s', $key, $db->quote($var));
				}
			}
			if (!empty($where)) {
				$where = ' WHERE ' . implode(' AND ', $where);
			} else {
				$where = '';
			}
			
			// Generate ORDER BY clause
			if (is_null($order)) {
				$order = '';
			} elseif (is_array($order)) {
				$order = 'ORDER BY ' . implode(', ', $order);
			} else {
				$order = 'ORDER BY ' . $order;
			}
			
			$sql = sprintf('SELECT %s FROM %s %s %s', $field_list, $from, $where, $order);
			$rows = $db->loadResultArray($sql, get_class($this), $array_key, $pagination);
			
			if(empty($rows)) {
				return $find_first ? false : array();
			}
			
			// Update the current model with the first result found
			foreach(reset($rows) as $key => $var) {
				$this->$key = $var;
			}
			
			return $find_first ? true : $rows;
		}
		
		
		/**
		 *	Deletea model from the database
		 *	@return		bool	True if model was deleted successfully, false otherwise
		 */
		public function delete() {
			$db = ABPF::db();
			
			// Check for parent class
			$parent = get_parent_class($this);
			
			$db->startTransaction();
			$key = $this->_primary_key;
			$sql = sprintf('DELETE FROM `%s` WHERE `%s` = %s', $this->_table_name, $this->_primary_key, $db->quote($this->$key));
			if(!$db->execute($sql)) {
				ABPF::logger()->error('Could not delete model ' . get_class($this));
				$db->rollbackTransaction();
				return false;
			}
			
			if($parent != 'Model') {
				$parent = new $parent();
				$key = $parent->_primary_key;
				$sql = sprintf('DELETE FROM `%s` WHERE `%s` = %s', $parent->_table_name, $parent->_primary_key, $db->quote($parent->$key));
				if(!$db->execute($sql)) {
					ABPF::logger()->error('Could not delete model ' . get_class($this) . '\'s parent class ' . get_class($parent));
					$db->rollbackTransaction();
					return false;
				}
			}
			
			$db->commitTransaction();
			return true;
		}
		
		
		/**
		 *	Returns all models as options for use in an HTML select box
		 *	@param		string $label		Label for the select option
		 *	@param		string $order		Order results by
		 *	@return		array
		 */
		public function selectOptions($label, $order = null) {
			$key = $this->_primary_key;
			$default = $this->$key;
			$class = get_class($this);
			$model = new $class();
			$options = $model->find($first = false, $order, $key);
			foreach($options as &$opt) {
				$opt = $opt->$label;
			}
			return ABPF::html()->selectOptions($options, $default);
		}
		
		
		/**
		 *	Get an instance of a sub class
		 *	@param		string		$class
		 *	@return		mixed
		 */
		public function __get($class) {
			// Get the member name of the ID
			$member = $class . '_id';
			
			// Check if we've got this object cached
			if(isset($this->_obj_cache[$class])) {
				return $this->_obj_cache[$class];
			}
			
			// Convert the class name to camel case
			$class = to_camel_case($class, true);
			
			// Check we're requesting a valid model
			if(!class_exists($class)) {
				throw new NoSuchClassException('Class <code>' . $class . '</code> not found.');
			}
			
			// Check the model we're requesting is an element of this class
			if(!is_member($member, $this)) {
				throw new NoSuchMemberException('Class ' . get_class($this) . ' has no such member ' . $member);
			}
			
			$obj = new $class($this->$member);
			$this->_obj_cache[$class] = $obj;
			return $obj;
		}
		
		
		/**
		 *	Load an array of models that reference this model
		 *	@param	Model	$model		The model which you want to fetch
		 *	@return	array	Array of new $models, empty array if none are found
		 */
		protected function getModels(Model $model) {
			$primary_key = $this->_primary_key;
			$model->$primary_key = $this->$primary_key;
			return $model->find();
		}
	}
	
