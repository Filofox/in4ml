<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Checkbox field
 */
class in4mlFieldCheckboxMultiple extends in4mlField{

	public $type = 'CheckboxMultiple';
	
	protected $container_type = 'InlineLabel';
	
	public $text = '';
	public $checked = "";
	
	private $options = array();
	private $options_elements = array();


	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();
		
		// Checkbox text
		$values->text = $this->text;
		
		$values->name = $this->name;

		// Set value?		
		if( ( $render_value && $this->value ) || ( !$render_value && isset( $this->default ) && $this->default ) ){
			$values->SetAttribute( 'checked', 'checked' );
		}
		
		return $values;
	}

	/**
	 * Perform any required modifications on the field
	 *
	 * In this case, convert options list to actual radio buttons
	 *
	 * @return		array		An array of in4mlElement objects
	 */
	public function Modify(){

		$element = in4ml::CreateElement( 'Block:' . $this->container_type );
		$element->AddClass( strtolower( $this->type ) );
		
		foreach( $this->container_class as $class ){
			$element->AddClass( $class );
		}
		
		$element->label = $this->label;

		foreach( $this->options as $index => $option ){

			$checkbox = in4ml::CreateElement( 'Field:Checkbox' );

			$checkbox->default = $this->default;
			$checkbox->name = $this->name.'[' . $index . ']';
			$checkbox->text = $option[ 'text' ];
			if( isset( $this->value[ $index ] ) ){
				$checkbox->value = 1;
			}
			$checkbox->form_id = $this->form_id;
			
			$this->options_elements[] = $checkbox;
			
			$element->AddElement( $checkbox );
		}

		return $element;
	}


	/**
	 * Set parsed value for this field
	 */
	public function SetValue( $value ){
		parent::SetValue( $value );
		// Pass value to options in case re-rendering
		foreach( $value as $index => $value ){
			$this->options_elements[ $index ]->value = 1;
		}
	}
	
	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	public function GetValue(){
		
		$values = array();
		
		foreach( $this->options as $index => $option ){
			if( isset( $this->value[ $index ] ) ){
				$values[ $index ] = $this->options[ $index ][ 'value' ];
			}
		}
		
		return $values;
	}
	
	/**
	 * Add an option
	 *
	 * @param		string		$value		The value will be submitted
	 * @param		string		$text		The text that is shown in the list
	 */
	public function AddOption( $value, $text ){
		$this->options[] = array
		(
			'value' => $value,
			'text' => $text
		);
	}
	
	/**
	 * Return list of options
	 *
	 * @return		array
	 */
	public function GetOptions(){
		return $this->options;
	}
}

?>