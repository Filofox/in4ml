<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check that confirm field value matches original field value
 */
class in4mlValidatorConfirm extends in4mlValidator{

	protected $class = 'is_confirm';

	/**
	 * Perform validation
	 *
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){

		$output = true;

		if( $field->confirm_field ){
			if( $field->GetValue() != $field->confirm_field->GetValue() ){
				$field->SetError( $this->GetErrorText( "confirm" ) );
				$output = false;
			}
		}

		return $output;
	}

	/**
	 * Return appropriate class according to whether this is the confirm field or the original field
	 *
	 * @param		in4mlField		$field
	 *
	 * @return		string
	 */
	public function GetClass( in4mlField $field ){
		if( $field->confirm_field ){
			$this->class = 'has_confirm';
		}
		return $this->class;
	}

	/**
	 * Modify field -- adds a clone 'confirm' field
	 *
	 * @param		in4mlField		$field
	 *
	 * @return		array
	 */
	public function ModifyElement( in4mlField $element ){
		$confirm_field = clone $element;
		$confirm_field->name .= '_confirm';
		if( $element->confirm_label ){
			$confirm_field->label = $confirm_field->confirm_label;
		} else {
			$confirm_field->label = 'Confirm ' . $confirm_field->label;
		}
		if( isset( $confirm_field->placeholder ) && $confirm_field->placeholder != '' ){
			if( $element->confirm_placeholder ){
				$confirm_field->confirm_placeholder = $confirm_field->confirm_placeholder;
			} else {
				$confirm_field->placeholder = 'Confirm ' . $confirm_field->placeholder;
			}
		}
		if( isset( $confirm_field->notes ) ){
			$confirm_field->notes = null;
		}

		$element->confirm_field = $confirm_field;

		return array
		(
			$element,
			$confirm_field
		);
	}
}

?>
