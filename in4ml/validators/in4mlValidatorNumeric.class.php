<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Verify that a value is numeric
 *
 * Optionally, check for minimum and/or maximum length
 */
class in4mlValidatorNumeric extends in4mlValidator{
	
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

		if( $value !== null && $value != '' ){
			if( !is_numeric( $value ) ){
				// Not a number
				$field->SetError( "numeric:nan" );
				$output = false;
			} else {

				// Minimum?
				if( $this->min !== null && $value < $this->min ){
					// Too big
					$field->SetError( $this->GetErrorText( "numeric:min", array( 'value' => $this->min ) ) );
					$output = false;
				}
				// Maximum?
				if( $this->max !== null && $value > $this->max ){
					// Too long
					$field->SetError( $this->GetErrorText( "numeric:max", array( 'value' => $this->max ) ) );
					$output = false;
				}
			}
		}		
		return $output;
	}
}

?>