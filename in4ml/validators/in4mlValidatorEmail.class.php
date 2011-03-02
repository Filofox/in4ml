<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathValidatorTypes() . 'in4mlValidatorRegex.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorEmail extends in4mlValidatorRegex{
	
	public $pattern = '^([\w-]+\.?)*\w+@([\da-zA-z-]+\.)+[a-zA-z]{2,3}$';
	public $match = true;

	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		// Check for non-match
		$output = $this->DoRegex( $field );

		$value = $field->GetValue();

		if( $value !== null && $value !== '' && $output ){
			$field->SetError( $this->GetErrorText( "email" ) );
			$output = false;
		}
		
		return $output;
	}
}

?>