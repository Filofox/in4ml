<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check value's minimum and/or maximum length
 */
class in4mlValidatorDateRange extends in4mlValidator{
	
	public $min = null;
	public $max = null;
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		$output = true;

		$value = $field->GetValue();

		if( $value ){
			// Min and max date
			require_once( in4ml::GetPathLibrary() . 'LibDate.class.php' );
			$min_date = new LibDate();
			$min_date->SetRelativeDateFromString( $this->min );
			$max_date = new LibDate();
			$max_date->SetRelativeDateFromString( $this->max );
	
			if( $min_date->GetAsTimestamp() > $value->GetAsTimestamp() ){
				$field->SetError( $this->GetErrorText( 'date:min', array( 'min' => $min_date->format( 'jS F Y' ) ) ) );
				$output = false;
			}
			if( $max_date->GetAsTimestamp() < $value->GetAsTimestamp() ){
				$field->SetError( $this->GetErrorText( 'date:max', array( 'max' => $max_date->format( 'jS F Y' ) ) ) );
				$output = false;
			}
		}

		return $output;
	}
}

?>