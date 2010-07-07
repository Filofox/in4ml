<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Text field
 */
class in4mlFieldCaptcha extends in4mlField{
	public $type = 'Captcha';
	public $container_type = 'Container';
	public $font_path = null;
	public $font_size = 30;
	public $padding = 10;
	public $background_colour = 'ffffff';
	public $text_colour = '000f00';

	public $code_length = '6';
	// Only use easily-distinguishable characters
	public $characters = "ACDEFGHJKLMNPRTUVWXY34679";
	
	public $uid;
	public $uid_field;
	
	public function __construct(){
		
		// Default font
		$this->font_path = in4ml::GetPathLibrary() . 'fonts/Liberation/LiberationMono-Bold.ttf';

		// Automatically validates options
		$this->AddValidator( in4ml::CreateValidator( 'Required' ) );		
		$this->AddValidator( in4ml::CreateValidator( 'Captcha' ) );		
		$this->uid = sha1( microtime( true ) . rand( 0, 10000 ) );
		
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
		
		$values->image_path = in4ml::Config( 'path_resources' ) . 'php/captcha_image.php';

		$values->name = $this->name;
		$values->uid = $this->uid;
		
		// Insert value
		$value = "";
		if( $render_value ){
			// Use submitted value
			$values->SetAttribute( 'value', htmlentities( $this->value ) );
		} else {
			// Use default value?
			if( isset( $this->default ) ){
				$values->SetAttribute( 'value', htmlentities( $this->default ) );
			}
		}
		
		return $values;
	}
	
	public function CheckUID( $uid, $code ){
		return $this->form->CheckCaptchaUID( $uid, $code );
	}
}

?>