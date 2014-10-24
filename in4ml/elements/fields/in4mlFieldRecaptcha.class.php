<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Text field
 */
class in4mlFieldRecaptcha extends in4mlField{
	public $type = 'Recaptcha';
	public $container_type = 'Container';
	
	public $elements = array();

	public function __construct(){

		// Automatically validates options
		$this->AddValidator( in4ml::CreateValidator( 'Recaptcha' ) );
		parent::__construct();
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

		return $values;
	}
}

?>
