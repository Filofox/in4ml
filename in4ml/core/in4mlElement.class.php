<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

/**
 * Base class for elements
 */
class In4mlElement{

	public $type;
	public $category;
	public $name;
	public $label;

	public $form_id;

	protected $template;
	
	protected $class = array();
	
	protected $container_type = null;

	function __construct(){
		$this->AddClass( strtolower( $this->type ) );
	}
	
	public function GetContainerType(){
		return $this->container_type;
	}

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues(){
		
		require_once( in4ml::GetPathCore() . 'in4mlElementRenderValues.class.php' );
		
		$values = new in4mlElementRenderValues( $this->category, $this->type );
		
		foreach( $this->class as $class ){
			$values->AddClass( $class );
		}
		
		return $values;
	}
	
	/**
	 * Retrieve the label for this element
	 *
	 * @return		string
	 */
	public function GetLabel(){
		return $this->label;
	}

	/**
	 * Catch invalid property set
	 */
	public function __set( $property, $value ){
		throw new Exception( "Field property '" . $property . "' not valid for " . get_class( $this ) );
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( "Field property '" . $property . "' not valid for " . get_class( $this ) );
	}
	
	public function AddClass( $class ){
		$this->class[] = $class;
	}
	
	public function Modify(){
		return $this;
	}
}

?>