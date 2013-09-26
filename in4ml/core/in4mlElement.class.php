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

	public $form;
	public $form_id;
	public $form_type;

	public $template;

	protected $class = array();
	protected $container_class = array();

	protected $container_type = null;

	function __construct(){
		$this->AddClass( strtolower( $this->type ) );
	}

	public function SetContainerType( $container_type ){
		$this->container_type = $container_type;
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
			if( $class ){
				$values->AddClass( $class );
			}
		}
		$values->form_type = $this->form_type;

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

		switch( $property ){
			case 'class':{
				if( is_array( $value ) ){
					foreach( $value as $class ){
						$this->AddClass( $class );
					}
				} else {
					$this->AddClass( $value );
				}
				break;
			}
			case 'container_class':{
				if( is_array( $value ) ){
					foreach( $value as $class ){
						$this->AddContainerClass( $class );
					}
				} else {
					$this->AddContainerClass( $value );
				}
				break;
			}
			default:{
				throw new Exception( "Field property '" . $property . "' not valid for " . get_class( $this ) );
			}
		}
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( "Field property '" . $property . "' not valid for " . get_class( $this ) );
	}

	/**
	 * Add a class to the element
	 *
	 * @param		string		$class
	 */
	public function AddClass( $class ){
		$this->class[] = $class;
	}
	/**
	 * Add a class to the element container
	 *
	 * @param		string		$class
	 */
	public function AddContainerClass( $class ){
		$this->container_class[] = $class;
	}
	/**
	 * Get a list of container classes
	 *
	 * @return		array
	 */
	public function GetContainerClasses(){
		return $this->container_class;
	}

	/**
	 * Override this to perform direct modification on the element before rendering
	 */
	public function Modify(){
		return $this;
	}
}

?>
