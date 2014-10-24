<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check that confirm field value matches original field value
 */
class in4mlValidatorRecaptcha extends in4mlValidator{

	public function __construct(){
		if( !( in4ml::Config()->recaptcha_public_key && in4ml::Config()->recaptcha_private_key ) ){
			throw new Exception( 'You must set public and private keys for reCAPTCHA' );
		}
	
//		parent::__construct();
	}

	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		$output = true;
		
		require_once( in4ml::GetPathLibrary() . 'recaptcha-php/recaptchalib.php' );
		$challenge = '';
		$response = '';
		if($field->form->submit_method == in4ml::SUBMIT_METHOD_POST ){
			if(
				isset( $_POST["recaptcha_challenge_field"] )
				&&
				isset( $_POST["recaptcha_response_field"] )
			){
				$challenge = $_POST["recaptcha_challenge_field"];
				$response = $_POST["recaptcha_response_field"];
			}
		} else {
			if(
				isset( $_GET["recaptcha_challenge_field"] )
				&&
				isset( $_GET["recaptcha_response_field"] )
			){
				$challenge = $_GET["recaptcha_challenge_field"];
				$response = $_GET["recaptcha_response_field"];
			}
		}
		
		$resp = recaptcha_check_answer (
			in4ml::Config()->recaptcha_private_key,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);

		if (!$resp->is_valid) {
			$field->SetError( $this->GetErrorText( "recaptcha" ) );
			$output = false;
		}
		
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

		$captcha_element = in4ml::CreateElement( 'General:Fragment' );
		require_once( in4ml::GetPathLibrary() . 'recaptcha-php/recaptchalib.php' );
		$captcha_element->content = recaptcha_get_html( in4ml::Config()->recaptcha_public_key );
		$element->elements[] = $captcha_element;
		return array
		(
			$element
		);
	}
}

?>