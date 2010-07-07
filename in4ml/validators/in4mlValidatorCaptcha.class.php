<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check that confirm field value matches original field value
 */
class in4mlValidatorCaptcha extends in4mlValidator{

	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		$output = true;
		
		$uid = $field->uid_field->GetValue();
		$value = $field->GetValue();

		if( !$field->CheckUID( $uid, $value ) ){
			$field->SetError( $this->GetErrorText( "captcha" ) );
			$output = false;
		}
		// Update this in case form is re-rendered
		$field->uid = $uid;
		
		return $output;
	}
	
	/**
	 * Modify field -- adds a hidden field for the UID
	 * 
	 * @param		in4mlField		$field
	 *
	 * @return		array
	 */
	public function ModifyElement( in4mlField $element ){

		$hidden_element = in4ml::CreateElement( 'Field:Hidden' );

		$hidden_element->name = $element->name . '_uid';
		$hidden_element->form_id = $element->form_id;
		$hidden_element->default = $element->uid;
		
		$element->uid_field = $hidden_element;
		
		return array
		(
			$element,
			$hidden_element
		);
	}
}

?>