<?php

if( !class_exists( 'LibDate' ) ){
	/**
	 * Simple class for carrying around date info and converting from one format to another
	 */
	class LibDate{
		public $day;
		public $month;
		public $year;
		public $hours = '00';
		public $minutes = '00';
		public $seconds = '00';

		/**
		 * Constructor
		 *
		 * @param		boolean		$set_now		[Optional] Set date as 'now'
		 */
		public function __construct( $set_now = false ){
			if( $set_now ){
				$this->SetFromTimestamp( date( 'U' ) );
			}
		}

		/**
		 * Check to see if a value has been set
		 *
		 * @return		boolean
		 */
		public function HasValue(){
			return (( $this->year )?true:false);
		}

		/**
		 * Set date from MySQL DATETIME string
		 *
		 * @param		string		$mysql_string		In format YYYY-MM-DD hh:mm:ss
		 */
		public function SetFromMysqlDateTime( $mysql_string ){
			if( $mysql_string ){
				// Check if it's DATETIME or just DATE
				if( strpos( $mysql_string, ' ' ) !== false ){
					list( $date, $time ) = explode( ' ', $mysql_string );
					list( $this->hours, $this->minutes, $this->seconds ) = explode( ':', $time );
				} else {
					$date = $mysql_string;
				}
				list( $this->year, $this->month, $this->day ) = explode( '-', $date );

				if( $this->year == '0000'){
					$this->year = null;
					$this->month = null;
					$this->day = null;
				}
			}
		}

		/**
		 * Set the date using the output from a form date element
		 *
		 * @param		array		$form_value		Fields: day, month, year
		 */
		public function SetFromFormValue( $form_value ){
			if( is_array( $form_value ) ){
				$this->day = str_pad( $form_value[ 'day' ], 2, '0', STR_PAD_LEFT );
				$this->month = str_pad( $form_value[ 'month' ], 2, '0', STR_PAD_LEFT );
				$this->year = $form_value[ 'year' ];
			}
		}

		/**
		 * Set the date from a UNIX timestamp
		 *
		 * @param		int		$timestamp
		 */
		public function SetFromTimestamp( $timestamp ){
			if( $timestamp !== null ){
				$this->year = date( 'Y', $timestamp );
				$this->month = date( 'm', $timestamp );
				$this->day = date( 'd', $timestamp );
				$this->hours = date( 'H', $timestamp );
				$this->minutes = date( 'i', $timestamp );
				$this->seconds = date( 's', $timestamp );
			}
		}

		/**
		 * Return the date formatted (as per PHP's date() command)
		 *
		 * @param		string		$format		PHP date() format string
		 */
		public function Format( $format ){
			$timestamp = $this->GetAsTimestamp();
			$date = null;
			if( $timestamp !== null ){
				$date = date( $format, $timestamp );
			}
			return $date;
		}

		/**
		 * Return date as a UNIX timestamp
		 *
		 * @return		int
		 */
		public function GetAsTimestamp(){
			$timestamp = null;
			if( $this->year !== null && $this->month !== null  && $this->day !== null ){
				$timestamp = strtotime( $this->year . '/' . $this->month . '/' . $this->day . ' ' . $this->hours . ':' . $this->minutes . ':' . $this->seconds );
			}
			return $timestamp;
		}

		/**
		 * Return date as a MySQL date 'YYYY-MM-DD hh:mm:ss'
		 *
		 * @return		string
		 */
		public function GetAsMySQLDateTime(){
			if( $this->day > 0 ){
				return $this->Format( 'Y-m-d H:i:s' );
			} else {
				return null;
			}
		}
		
		/**
		 * Return a PHP DateTime object
		 */
		public function GetAsDateTime(){
			return new DateTime( $this->GetAsMySQLDateTime() );
		}

		/**
		 * Change the date by a relative amount (i.e. number of days/weeks/months -- use negative values to go backwards)
		 *
		 * @param		int		$days
		 * @param		int		$months
		 * @param		int		$years
		 */
		public function SetRelativeDate( $days = false, $months = false, $years = false ){
			$current_day = $this->day;
			$base = strtotime($this->format( 'Y-m-01 00:00:01'));
			$set_day = false;
			if( $months ){
				if( strpos( $months, '-' ) === false && strpos( $months, '+' ) === false ){
					$months = '+' . $months;
				}
				$this->SetFromTimestamp
				(
					strtotime(
						$months . ' months',
						$base
					)
				);
				$base = strtotime($this->format( 'Y-m-01 00:00:01'));
			}
			if( $years ){
				if( strpos( $years, '-' ) === false && strpos( $years, '+' ) === false ){
					$years = '+' . $years;
				}
				$this->SetFromTimestamp
				(
					strtotime(
						$years . ' years',
						$base
					)
				);
				$base = strtotime($this->format( 'Y-m-01 00:00:01'));
			}
			if( $days ){
				if( strpos( $days, '-' ) === false && strpos( $days, '+' ) === false ){
					$days = '+' . $days;
				} else {
					$base = strtotime($this->format( 'Y-m-d 00:00:01'));
				}
				$this->SetFromTimestamp
				(
					strtotime(
						$days . ' days',
						$base
					)
				);
			} else {
				$this->day = min( $current_day, (int)$this->format( 't' ) );
			}
		}

		/**
		 * Set a date from a string formatted dd/mm/yy
		 *
		 * User + or - to specify relative dates, otherwise absolute date assumed
		 */
		public function SetRelativeDateFromString( $date_string ){
			// First, set date to 'now'
			$this->SetFromTimestamp( date( 'U' ) );
			$this->hours = $this->minutes = $this->seconds = 0;

			$date = array();

			// Split and match for + and - (relative dates)
			preg_match( '/(\+|-|~|)(\d*)\/(\+|-|)(\d*)\/(\+|-|)(\d*)/', $date_string, $matches );

			if( count( $matches ) == 0 ){
				throw new Exception( 'Invalid date' );
			}

			// Convert to numbers
			$day = (int) $matches[2];
			$month = (int) $matches[4];
			$year = (int) $matches[6];

			$relative_year = $relative_month = $relative_day = false;

			// Year
			if( $year > 0 ){
				switch( $matches[ 5 ] ){
					case '+':
					case '-':{
						$relative_year = $matches[ 5 ] . $year;
						break;
					}
					default:{
						$this->year = $year;
						break;
					}
				}
			}

			// Month
			if( $month > 0 ){
				switch( $matches[ 3 ] ){
					case '+':
					case '-':{
						$relative_month = $matches[ 3 ] . $month;
						break;
					}
					default:{
						$this->month = $month;
						break;
					}
				}
			}
			if( $day > 0 ){
				// Day
				switch( $matches[ 1 ] ){
					case '+':
					case '-':{
						$relative_day = $matches[ 1 ] . $day;
						break;
					}
					default:{
						$this->day = 1;
						$this->day = min( $day, (int)$this->format( 't' ) );
						break;
					}
				}
			}

			// Do any relative stuff
			$this->SetRelativeDate( $relative_day, $relative_month, $relative_year, true );

			if( $matches[ 1 ] == '~'){
				$this->day = (int)$this->format( 't' );
			}
		}

		/**
		 * Return the date of the first day of the week which contains current date
		 *
		 * @param		int		$start_day		[optional] Define which say of the week is the start day (default = 1 = Monday )
		 *
		 * @return		int						UNIX timestamp
		 */
		public function GetWeekStartDate( $start_day = 1 ){
			$date_timestamp = $this->GetAsTimestamp();
			$week_start = date('U', mktime(1, 0, 0, date('m', $date_timestamp), date('d', $date_timestamp)-(date('w', $date_timestamp)-$start_day), date('Y', $date_timestamp)) - 3600 );

			return $week_start;
		}

		/**
		 * Return difference (in days) between two dates
		 *
		 * @param		LibDate
		 *
		 * @return		int
		 */
		public function Diff( LibDate $date ){
			$this_date = new LibDate();
			$this_date->year = $this->year;
			$this_date->month = $this->month;
			$this_date->day = $this->day;

			$diff_date = new LibDate();
			$diff_date->year = $date->year;
			$diff_date->month = $date->month;
			$diff_date->day = $date->day;

			$result = floor( ( $diff_date->Format( 'U' ) - $this_date->Format( 'U' ) ) / (60 * 60 * 24) );
			return $result;
		}

		/**
		 * Set value from a DateTime object
		 *
		 * @param		DateTime		$date_time
		 */
		public function SetFromDateTime( DateTime $date_time ){
			$this->SetFromTimestamp( $date_time->format( 'U' ) );
		}
	} // END class LibDate
}

?>
