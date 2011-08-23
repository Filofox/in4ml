<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Checks that a value has been supplied
 */
class in4mlValidatorRequired extends in4mlValidator{

	protected $class = 'required';

	/**
	 * Perform validation
	 *
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlField $field ){

		$output = true;

		switch( $field->type ){
			case'File':{
				$valid = true;
				foreach( $field->files as $file ){
					if( $file[ 'error_number' ] == 4 ){
						$field->SetError( $this->GetErrorText( 'required' ) );
						$output = false;
					}
				}
				break;
			}
			default:{
				$value = $field->GetValue();

				if( $value === null || $value === '' || $value === false ){
					$field->SetError( $this->GetErrorText( 'required' ) );
					$output = false;
				}
				break;
			}
		}

		return $output;
	}
}

?>
