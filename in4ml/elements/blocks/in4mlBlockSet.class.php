<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlBlock.class.php' );

/**
 * Form element
 */
class in4mlBlockSet extends in4mlBlock{
	public $type = 'Set';
	public $legend = '';

	function __construct(){
		parent::__construct();
		
		$this->AddClass( 'fieldset' );
	}

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues(){
		$values = parent::GetRenderValues();
		
		// Extra <span> in fieldset to allow consistent styling (see http://www.tyssendesign.com.au/articles/css/legends-of-style/)
		$values->legend = ( $this->label )?'<legend><span>' . $this->label . '</span></legend>':'';
		
		return $values;
	}
	
}

?>