<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorFileMaxSize extends in4mlValidator{
	
	public $size;
	public $units;
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlFieldFile $field ){
		$output = true;
		
		if( $this->size !== null && $this->units !== '' ){		
			switch( $this->units ){
				case 'KB':{
					$max_size_kilobytes = $this->size * 1024;
					break;
				}
				case 'MB':{
					$max_size_kilobytes = $this->size * pow( 1024, 2 );
					break;
				}
				case 'GB':{
					$max_size_kilobytes = $this->size * pow( 1024, 3 );
					break;
				}
				default:{
					throw new Exception( 'Unknown file size unit ' . $this->units );
				}
			}

			foreach( $field->files as $file ){
				if( $file[ 'file_size' ] > $max_size_kilobytes ){
					// Convert file size (bytes) to match file size limit units
					switch( $this->units ){
						case 'KB':{
							$file_size = $file[ 'file_size' ] / 1024;
							break;
						}
						case 'MB':{
							$file_size = number_format( $file[ 'file_size' ] / pow( 1024, 2 ), 2 );
							break;
						}
						case 'GB':{
							$file_size = number_format( $file[ 'file_size' ] / pow( 1024, 3 ), 2 );
							break;
						}
						default:{
							throw new Exception( 'Unknown file size unit ' . $this->units );
						}
					}
					$field->SetError( $this->GetErrorText( 'file:max_size', array( 'filename' => $file[ 'name' ], 'filesize' => $file_size . $this->units, 'max' => $this->size . $this->units ) ) );
					$output = false;
				}
			}
		}

		return $output;
	}	
}

?>
