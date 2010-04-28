<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check value's minimum and/or maximum length
 */
class in4mlValidatorLength extends in4mlValidator{
	
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
		if( $this->min !== null && strlen( $value ) < $this->min ){
			$field->SetError( $this->GetErrorText( 'length:min', array( 'min' => $this->min ) ) );
			$output = false;
		}
		if( $this->max !== null && strlen( $value ) > $this->max ){
			$field->SetError( $this->GetErrorText( 'length:max', array( 'max' => $this->max ) ) );
			$output = false;
		}
		
		return $output;
	}
}

?>