<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathValidatorTypes() . 'in4mlValidatorRegex.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorEmail extends in4mlValidatorRegex{

	public $pattern = '^([\w-]+\.?)*\w+@([\da-zA-z-]+\.)+[a-zA-z]{2,3}$';
	public $match = true;

	public $allow_multiple = false;
	public $allow_name = false;


	/**
	 * Perform validation
	 *
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){

		$value = $field->GetValue();

		$output = true ;
		// Check for non-match
		if( $this->allow_multiple && strpos( $value, ';' ) !== false ){
			foreach( explode( ';', $value ) as $email ){
				$email = trim( $email );
				if( $this->allow_name ){
					if( preg_match( '~^(.*)<\s*(.+)\s*>\s*$~U', $email, $matches ) ){
						$email = trim($matches[ 2 ]);
					}
				}
				if( $email && $this->Execute( $email ) ){
					$field->SetError( $this->GetErrorText( "email", array( 'email' => $email ) ) );
					$output = false;
				}
			}
		} else {
			$email = $value;
			if( $this->allow_name ){
				if( preg_match( '~^(.*)<\s*(.+)\s*>\s*$~U', $email, $matches ) ){
					$email = trim($matches[ 2 ]);
				}
			}
			if( $email && $this->Execute( $email ) ){
				$field->SetError( $this->GetErrorText( "email", array( 'email' => $email ) ) );
				$output = false;
			}
		}

		return $output;
	}
}

?>
