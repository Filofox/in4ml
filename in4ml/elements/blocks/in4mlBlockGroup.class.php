<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlBlock.class.php' );

/**
 * A group of elements
 */
class in4mlBlockGroup extends in4mlBlock{
	public $type = 'Group';

	public $notes = false;

	public function __construct(){
		parent::__construct();

		$this->AddClass( 'clearfix' );
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

		if( $this->notes ){
			$values->notes = $this->notes;
		} else {
			$values->notes = '';
		}

		return $values;
	}

}

?>
