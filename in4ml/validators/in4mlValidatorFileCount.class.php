<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorFileCount extends in4mlValidator{
	
	public $max;
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		$output = true;
		
		if( $this->max !== null && count( $field->files ) > $this->max ){
			$field->SetError( $this->GetErrorText( "file:count" ), array( 'count' => count( $field->files ), 'max' => $this->max ) );
			$output = false;
		}

		return $output;
	}
	
}

?>
