<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Use a regular expression to check for unwanted characters
 *
 * Returns false (i.e. invalid) if pattern match succeeds
 */
class in4mlValidatorRegex extends in4mlValidator{
	
	public $pattern;
	public $delimiter = '/';
	public $ignore_case = true;
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){
		
		$output = $this->DoRegex( $field );
		if( !$output ){
			$field->SetError( $this->GetErrorText( 'regex' ) );
		}

		return $output;
	}
	/**
	 * Perform regex
	 */
	protected function DoRegex( in4mlField $field ){

		$output = true;
		
		$value = $field->GetValue();

		if( $value !== null && $value !== '' ){
			// Make any instances of the delimiter in the pattern istelf are escaped
			$pattern = str_replace( $this->delimiter, '\\' . $this->delimiter, $this->pattern );
			// Wrap with delimiter
			$pattern = $this->delimiter . $pattern . $this->delimiter;
			
			if( $this->ignore_case ){
				$pattern .= 'i';
			}
			$output = !( preg_match( $pattern, $value ) );
		}		
		
		return $output;
	}

}

?>