<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Text field
 */
class in4mlFieldButton extends in4mlField{
	public $type = 'Button';
	public $button_type = 'button';
	
	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$this->AddClass( strtolower( $this->button_type ) );

		$values = parent::GetRenderValues();
		
		// Checkbox text
		$values->setAttribute( 'value', htmlentities( $this->label ) );
		$values->setAttribute( 'type', $this->button_type );

		return $values;
	}
}

?>