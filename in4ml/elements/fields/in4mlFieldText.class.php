<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Text field
 */
class in4mlFieldText extends in4mlField{
	public $type = 'Text';
	protected $container_type = 'Container';

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();
		
		// Insert value
		$value = "";
		if( $render_value ){
			// Use submitted value
			$values->SetAttribute( 'value', in4ml::Escape( $this->value, true ) );
		} else {
			// Use default value?
			if( isset( $this->default ) ){
				$values->SetAttribute( 'value', in4ml::Escape( $this->default, true ) );
			}
		}
		
		return $values;
	}
}

?>