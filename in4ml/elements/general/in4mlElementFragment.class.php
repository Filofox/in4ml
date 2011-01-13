<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlElement.class.php' );

/**
 * Arbitrary HTML fragment
 */
class in4mlElementFragment extends in4mlElement{
	public $type = 'Fragment';
	public $category = 'General';
	
	public $content = '';
	
	/**
	 * Set the content of the fragment
	 *
	 * @param		string
	 */
	public function SetContent( $content ){
		$this->content = $content;
	}
	
	public function GetRenderValues(){
		$values = parent::GetRenderValues();
		
		$values->content = $this->content;
		
		return $values;
	}
}

?>