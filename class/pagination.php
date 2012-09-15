<?php
	
	/**
	 *	@package ABPF
	 *	@subpackage Database
	 */
	class Pagination extends ABPFClass {
		
		public $page_number;
		public $total_pages;
		public $total_records;
		public $records_per_page;
		public $records_this_page;
		
		public $start_record;
		public $end_record;
		public $next_page;
		public $prev_page;
		
		
		/**
		 *	Constructor - setup pagination.
		 *	@param int	$page_number - which page number you wish to get
		 * 	@param int	$records_per_page - how many rows to return per page
		 */
		public function __construct($page_number = 1, $records_per_page = 10) {
			$this->page_number = (int)$page_number > 0 ? (int)$page_number : 1;
			$this->records_per_page = $records_per_page;
		}
		
		
		/**
		 *	This will calculate and return the SQL LIMIT clause necessary to
		 * 	return the correct number and set of rows
		 * 	You don't need to call this - the base dao does everything for you
		 */
		public function calcSQLLimitClause() {
			$limit = $this->records_per_page;
			$offset = ($this->page_number - 1) * $this->records_per_page;
			return " LIMIT " . $offset . ", " . $limit . " ";
		}
		
		
		/**
		 *	This will calculate the total number of records, and hence the 
		 * 	total number of pages.
		 * 	You don't need to call this - the base dao does everything for you
		 */
		public function calcTotalRecords($num_rows) {
			// Get the total number of records returned by the query
			$this->total_records = ABPF::db()->loadSingleResult('SELECT FOUND_ROWS()');
			
			// Calculate the total number of pages
			$this->total_pages 			= ceil($this->total_records / $this->records_per_page);
			if($this->total_pages == 0) {
				$this->total_pages = 1;
			}
			
			$this->records_this_page = $num_rows;
			
			// Calculate the record number of the first and last result in the current page
			$this->start_record	= (($this->page_number - 1) * $this->records_per_page);
			$this->end_record 	= $this->start_record + $this->records_this_page;
			if($this->records_this_page > 0) {
				$this->start_record++;
			}
			
			// Calc the previous and next page numbers
			$this->next_page = $this->page_number + 1;
			$this->prev_page = $this->page_number - 1;
		}
		
		
		/**
		 *	Check if the 'next page' link should be shown (i.e. if the current page isn't the last page)
		 * 	@return	bool	True if we should show the 'next page' button
		 */
		public function showNextButton() {
			return $this->page_number < $this->total_pages;
		}
		
		
		/**
		 *	Check if the 'previous page' link should be shown (i.e. if the current page isn't the first page)
		 * 	@return	bool	True if we should show the 'prev page' button
		 */
		public function showPrevButton() {
			return $this->page_number > 1 && $this->total_pages > 1;
		}
		
		
		/**
		 *	Get the text to display at the bottom of the table saying what record numbers are shown
		 * 	@return		string	Text to display
		 */
		public function resultRangeText() {
			if($this->total_records == 0) {
				return 'No results found';
			}
			if($this->total_records == 1) {
				return 'Displaying the only result';
			}
			return sprintf('Currently displaying <strong>%d - %d</strong> of <strong>%d</strong> results',
				$this->start_record,
				$this->end_record,
				$this->total_records);
		}
	}
	