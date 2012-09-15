<?php

	class Date {

		public $raw;
		public $time;
		public $valid;

		/**
		 *	Create a new date object
		 *	@param	int|string		$date	Date may be passed as unix timestamp, or most date formatted strings
		 */
		public function __construct($date = null) {
			if(is_null($date)) {
				$this->raw = datetime();
				$this->time = time();
				return;
			}
			$this->raw = $date;
			$this->valid = true;
			switch(true) {
				case preg_match('/^\d{4}(\/|-)\d{2}(\/|-)\d{2}( \d{2}:\d{2}:\d{2})?$/', $date):
					// YYYY-MM-DD
					$this->time = strtotime($date);
					break;

				case preg_match('/^\d{2}(\/|-)\d{2}(\/|-)\d{4}$/', $date):
					// DD-MM-YYYY
					$this->time = strtotime($this->reverseDate($date));
					break;

				case preg_match('/^\d+$/', $date):
					// Unix Timestamp
					$this->time = $date;
					break;

				default:
					if($time = strtotime($date)) {
						$this->time = $time;
					} else {
						$this->valid = false;
						ABPF::logger()->debug("Unknown date format passed to Date::__construct() - '$date'");
					}
			}
		}


		/**
		 *	Output this object as a string - will print the raw $date that was passed to the constructor
		 */
		public function __toString() {
			return (string)$this->raw;
		}

		/**
		 *	Reverse a date - i.e. 1970-01-01 -> 01-01-1970
		 */
		private function reverseDate($date, $delimiter = '-') {
			return implode($delimiter, array_reverse(preg_split('/\/|-/', $date)));
		}

		/**
		 *	Set this date object to a MySQL-standard DateTime.
		 */
		public function toMySQL() {
			return $this->toMySQLDatetime();
		}

		/**
		 *	Output date as a mysql date time
		 */
		public function toMySQLDatetime() {
			return $this->toFormattedOutput('Y-m-d H:i:s');
		}

		/**
		 *	Output the date formatted for mysql
		 */
		public function toMySQLDate() {
			return $this->toFormattedOutput('Y-m-d');
		}

		/**
		 *	Return date as a Unix Timestamp.
		 */
		public function getTimeStamp() {
			return $this->time;
		}

        /**
         *	Return date as a string formatted as specified. Format as accepted by php's date() function
         *	@see http://au.php.net/manual/en/function.date.php
         */
        public function toFormattedOutput($format) {
            return date($format, $this->time);
        }

        /**
         *  Return date as a string formatted as specified. Format as accepted by php's date() function
         *	@see http://au.php.net/manual/en/function.date.php
         */
        public function output($format) {
			return $this->toFormattedOutput($format);
        }

        /**
         *    Return a string with an colloquial English description of where this
         *    date is relative to now (eg '3 days ago' or 'in 3 days').
         *    @return   string  '3 days ago'
         */
        public function colloquial() {
            $period_array = array(  "second" => 1,
                                    "minute" => 60,
                                    "hour"   => 60*60,
                                    "day"    => 60*60*24,
                                    "month"  => 60*60*24*30,
                                    "year"   => 60*60*24*365 );

            $second_difference = abs(time() - $this->time);

            foreach($period_array as $period => $seconds) {
                if($second_difference < $seconds) {
                    $period_quantity = round($second_difference / $period_array[$last_period]) ;
                    $time_interval = $period_quantity . ' ' . $last_period;
                    $time_interval .= ($period_quantity==1) ? '' : 's';
                    break;
                }
                $last_period = $period;
                $time_interval = 'over a ' . $last_period;
            }

            return (time() > $this->time) ? $time_interval . ' ago' : 'in ' . $time_interval;
        }

        /**
         *	Return a new date that is 24hours ahead of the value of this date
         *	@return		Date
         */
        public function getTomorrowDate() {
        	return new Date($this->time + (24 * 60 * 60));
        }

        /**
         * Reverse a date (e.g. YYYY-MM-DD -> DD-MM-YYYY)
         * @param string $date
         * @param string $sep
         * @return string
         */
		private function reverse($date, $sep = '-') {
			return implode($sep, array_reverse(explode($sep, $date)));
		}

		/**
		 *	Return an array of Month strings
		 *	@return	array
		 */
		public static function months() {
			return array(
				1  => array('full' => 'January',		'abbr' => 'Jan'),
				2  => array('full' => 'February',		'abbr' => 'Feb'),
				3  => array('full' => 'March',			'abbr' => 'Mar'),
				4  => array('full' => 'April',			'abbr' => 'Apr'),
				5  => array('full' => 'May',			'abbr' => 'May'),
				6  => array('full' => 'June',			'abbr' => 'Jun'),
				7  => array('full' => 'July',			'abbr' => 'Jul'),
				8  => array('full' => 'August',			'abbr' => 'Aug'),
				9  => array('full' => 'September',		'abbr' => 'Sep'),
				10 => array('full' => 'October',		'abbr' => 'Oct'),
				11 => array('full' => 'November',		'abbr' => 'Nov'),
				12 => array('full' => 'December',		'abbr' => 'Dec')
			);
		}
	}
