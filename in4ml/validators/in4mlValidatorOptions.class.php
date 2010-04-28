<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check that submitted value is a valid option in a select or radio field
 */
class in4mlValidatorOptions extends in4mlValidator{
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		$value = $field->GetValue();
		
		$is_valid = false;
		
		if( $value === null ){
			// Radio buttons and multi-selects can have no value (i.e. none selected)
			if( $field->type == 'Radio' || $field->type == 'SelectMultiple' ){
				$is_valid = true;
			}
		} else {

			// Get a list of all possible values
			$values = array();
			foreach( $field->GetOptions() as $option ){
				if( isset( $option[ 'value' ] ) ){
					// Option
					$values[] = $option[ 'value' ];
				} else {
					// Option group
					foreach( $option[ 'options' ] as $group_option ){
						$values[] = $group_option[ 'value' ];
					}
				}
			}

			// Select Multiple
			if( $field->type == 'SelectMultiple' ){
				if( is_array( $value ) ){
					if( count( array_diff( $value, $values ) ) == 0 ){
						$is_valid = true;
					}
				}
			} else {
				// Select and Radio
				if( in_array( $value, $values ) ){
					$is_valid = true;
				}
			}
		}
		
		if( !$is_valid ){
			$field->SetError( $this->GetErrorText( 'option' ) );
		}
		
		return $is_valid;
	}
}

?>