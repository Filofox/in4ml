<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Checkbox field
 */
class in4mlFieldCheckbox extends in4mlField{

	public $type = 'Checkbox';
	
	protected $container_type = 'InlineLabel';
	
	public $text = '';
	public $checked = "";

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
		$values->text = $this->text;
		
		$values->name = $this->name;

		// Set value?		
		if( ( $render_value && $this->value ) || ( !$render_value && isset( $this->default ) && $this->default ) ){
			$values->SetAttribute( 'checked', 'checked' );
		}
		
		return $values;
	}
	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	public function GetValue(){
		return ( $this->value )?true:false;
	}
}

?>