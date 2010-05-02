<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlElement.class.php' );

/**
 * Error block
 */
class in4mlElementError extends in4mlElement{
	public $type = 'Error';
	public $category = 'General';
	
	private $error_type;
	
	/**
	 * Set the error
	 *
	 * @param		string
	 */
	public function SetErrorType( $error_type ){
		$this->error_type = $error_type;
	}
	
	public function GetRenderValues(){
		$values = parent::GetRenderValues();
		
		$values->value = $this->error_type;
		
		return $values;
	}
}

?>