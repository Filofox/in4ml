<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

/**
 * Base class for validators
 */
class in4mlValidator
{
	/** Optional CSS class applied to container for any elements using this validator **/
	protected $class = null;

	public $error_message;

	public function ValidateField( in4mlField $field ){
		throw new Exception( 'Method in4mlValidator::ValidateField() must be overridden' );
	}

	public function GetType(){
		return str_replace( 'in4mlValidator', '', get_class( $this ) );
 	}

	/**
	 * Return CSS class
	 *
	 * @param	in4mlField		$field		Use this to set any special behaviour due to field properties
	 */
	public function GetClass( in4mlField $field ){
		return $this->class;
	}

	/**
	 * Called when definition is being parsed -- allows validator to alter field's properties
	 *
	 * @param		in4mlField		$element
	 *
	 * @param		mixed			in4mlElement or array of in4mlElements
	 */
	public function ModifyElement( in4mlField $element ){
		// BY default, do nothing
		return $element;
	}

	/**
	 * Return the text for an error
	 *
	 * @param		string		$error_type		What type of error?
	 * @param		array		$parameters		[Optional] list of key=>value pairs to be interpolated into the error message template
	 *
	 * @return		string
	 */
	protected function GetErrorText( $error_type, $parameters = false ){

		// Is there a custom error message (from definition)?
		if( $this->error_message ){
			$exploded = explode( ':', $error_type );
			$type = array_pop( $exploded );

			// Is there i18n?
			if( is_array( $this->error_message ) ){
				// Get current language setting
				$lang = in4ml::Config( 'lang' );
				if( isset( $this->error_message[ $type ][ $lang ] ) ){
					$text = $this->error_message[ $type ][ $lang ];
				} else {
					throw new Exception( 'Error ' . $type . 'not available for lang: ' . $lang );
				}
			} else {
				// Only one language
				$text = $this->error_message;
			}
			// Interpolate any parameters
			if( is_array( $parameters ) && count( $parameters ) ){
				$text = in4mlText::Interpolate( $parameters, $text );
			}
		} else {
			// Just use default error message
			$text = in4ml::Text( 'Error', $error_type, $parameters );
		}

		return $text;
	}

	/**
	 * Catch invalid property set
	 */
	public function __set( $property, $value ){
		throw new Exception( "Validator property '" . $property . "' not valid for " . get_class( $this ) );
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( "Validator property '" . $property . "' not valid for " . get_class( $this ) );
	}

}

?>