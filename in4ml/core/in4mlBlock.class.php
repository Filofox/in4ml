<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlElement.class.php' );

/**
 * Base class for block elements -- i.e. elements that have child elements
 */
class In4mlBlock extends in4mlElement{

	public $type;

	public $category = 'Block';

	public $field_name;
	public $form_id;
	public $label;
	
	public $elements = array();

	/**
	 * Add a new child element
	 *
	 * @param		in4mlElement		$element
	 */
	public function AddElement( in4mlElement $element ){
		$this->elements[] = $element;
	}

	public function GetRenderValues(){
		
		$values = parent::GetRenderValues();
		
		$values->name = $this->name;
		$values->field_name = $this->field_name;
		$values->form_id = $this->form_id;
		$values->label = $this->label;
		
		return $values;
	}
}

?>