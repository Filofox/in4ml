<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * File upload field
 */
class in4mlFieldFile extends in4mlField{
	public $type = 'File';

	protected $container_type = 'Container';

	public $files = array();

	public $multiple = false;

	public $advanced = false;

	/**
	 * Add a file
	 */
	public function AddFile( $name, $temp_name, $mime_type, $file_size, $error_number ){
		$this->files[] = array
		(
			'name' => $name,
			'temp_name' => $temp_name,
			'mime_type' => $mime_type,
			'file_size' => $file_size,
			'error_number' => $error_number
		);
	}

	public function GetFiles(){
		return $this->files;
	}

	public function GetPropertiesForJSON(){
		return array( 'advanced' => $this->advanced, 'multiple' => $this->multiple );
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

		// Converts name to an array so that PHP can handle more than one value
		if( $this->multiple ){
			$values->setAttribute( 'multiple', 'multiple' );
		}

		return $values;
	}
}
?>
