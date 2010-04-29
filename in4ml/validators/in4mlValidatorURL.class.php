<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorURL extends in4mlValidator{
	
	public $pattern = '^([\w-]+\.?)*\w+@([\da-zA-z-]+\.)+[a-zA-z]{2,3}$';
	
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
		
		if( $value !== null ){
			// Check protocol
			if( !preg_match( "~^(http|https)://~i", $value ) ){
				$field->SetError( $this->GetErrorText( 'url:protocol' ) );
				$output = false;
			// Check well-formedness
			} elseif( !preg_match( "~^(http|https)://([\dA-Z0-9-]+\.)+[a-zA-z0-9]{1,3}~Ui", $value ) ) {
				$field->SetError( $this->GetErrorText( 'url:invalid' ) );
				$output = false;
			}
		}
		return $output;
	}
}

?>