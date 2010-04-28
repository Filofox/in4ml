<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Radio button
 *
 * Not usually used directly -- these are created automatically when using the 'radio' field type
 */
class in4mlFieldRadioButton extends in4mlField{

	public $type = 'RadioButton';
	public $field_value;

	private $options = array();
	
	public $index;
	
	public function __construct(){		
		// Automatically validates options
		$this->AddValidator( in4ml::CreateValidator( 'Options' ) );		
	}

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();
		
		// Checkbox text
		$values->label = $this->label;
		$values->index = $this->index;
		$values->setAttribute( 'id', $this->form_id . '_' . $this->name . '_' . $this->index );
		$values->element_id = $this->form_id . '_' . $this->name . '_' . $this->index;
		
		$values->SetAttribute( 'value', $this->field_value );

		$values->AddClass( $this->name );
		// To allow striping
		$values->AddClass( ( $this->index % 2 )?'odd':'even' );
		
		// Set value?
		if( ( $render_value && $this->field_value == $this->value ) || ( !$render_value && isset( $this->default ) && $this->default == $this->field_value ) ){
			$values->SetAttribute( 'checked', 'checked' );
		}
		
		return $values;
	}
}

?>