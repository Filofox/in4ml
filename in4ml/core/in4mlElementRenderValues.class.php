<?php

/**
 * Container object for element properties
 */
class in4mlElementRenderValues{
	
	protected $classes = array();
	protected $attributes = array();
	
	protected $element_category;
	protected $element_type;
	
	public function __construct( $category, $type ){
		
		$this->element_category = $category;
		$this->element_type = $type;
		
		$this->AddClass( strtolower( $this->element_type ) );
	}
	
	/**
	 * Add a class
	 *
	 * @param		string		$class
	 */
	public function AddClass( $class ){
		if( !in_array( $class, $this->classes ) ){
			array_push( $this->classes, $class );
		}
	}
	
	/**
	 * Return a list of classes
	 *
	 * @return		array
	 */
	public function GetClasses(){
		return $this->classes;
	}
	
	/**
	 * Set an element attribute
	 *
	 * @param		string		$name		Attribute name
	 * @param		mixed		$value
	 */
	public function SetAttribute( $name, $value ){
		$this->attributes[ $name ] = $value;
	}
	
	/**
	 * Return a list of attributes
	 *
	 * @return		array
	 */
	public function GetAttributes(){
		
		$attributes = array();
		foreach( $this->attributes as $key => $value ){
			switch( $key ){
				case 'name':
				{
					if( $value ){
						$attributes[ $key ] = $value;
					}
					break;
				}
				default:{
					$attributes[ $key ] = $value;
				}
			}
		}
		$attributes[ 'class' ] = implode( ' ', $this->GetClasses() );
		
		return $attributes;
	}
	
	/**
	 * Get the category of the element
	 *
	 * @return		string
	 */
	public function GetElementCategory(){
		return $this->element_category;
	}
	/**
	 * Get the type of the element
	 *
	 * @return		string
	 */
	public function GetElementType(){
		return $this->element_type;
	}
}

?>