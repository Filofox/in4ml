<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check value's minimum and/or maximum length
 */
class in4mlValidatorRejectValues extends in4mlValidator{
	
	public $values = array();
	
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
		if( $value !== null && in_array( $value, $this->values ) ){
			$field->SetError( $this->GetErrorText( 'reject_value' ) );
			$output = false;
		}
		
		return $output;
	}
}

?>