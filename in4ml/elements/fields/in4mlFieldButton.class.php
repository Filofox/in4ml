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
	public $button_type;
	
	public function Render(){
		$output = parent::Render();
		
		$classes = array
		(
			'button',
			$this->button_type,
			$this->name
		);
		
		$output = str_replace
		(
			array
			(
				'[[button_type]]',
				'[[class]]',
				'[[label]]'
			),
			array
			(
				$this->button_type,
				implode( ' ', array_unique( $classes ) ),
				$this->label
			),
			$output
		);
		
		return $output;
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
		$values->setAttribute( 'value', $this->label );
		$values->setAttribute( 'type', $this->button_type );

		return $values;
	}
}

?>