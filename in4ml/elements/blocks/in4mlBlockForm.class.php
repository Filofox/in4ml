<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlBlock.class.php' );

/**
 * Form element
 */
class in4mlBlockForm extends in4mlBlock{
	public $type = 'Form';
	
	public $action;
	public $submit_method;
	public $enctype;
	public $form_id;
	
	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues(){
		$values = parent::GetRenderValues();
		
		$values->AddClass( 'in4ml' );
		
		$values->SetAttribute( 'action', $this->action );
		$values->SetAttribute( 'method', $this->submit_method );
		$values->SetAttribute( 'id', $this->form_id );
		// Encoding type is set automatically if file element is present
		if( $this->enctype ){
			$values->SetAttribute( 'enctype', $this->enctype );
		}
		
		return $values;
	}
}

?>