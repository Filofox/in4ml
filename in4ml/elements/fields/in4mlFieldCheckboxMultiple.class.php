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
	private $options_element = null;

	public function __construct(){
		$element = in4ml::CreateElement( 'Block:' . $this->container_type );
		$element->AddClass( strtolower( $this->type ) );
		$this->options_element = $element;
	}

	/**
	 * Perform any required modifications on the field
	 *
	 * In this case, convert options list to actual radio buttons
	 *
	 * @return		array		An array of in4mlElement objects
	 */
	public function Modify(){
		$this->options_element->label = $this->label;

		foreach( $this->container_class as $class ){
			$this->options_element->AddClass( $class );
		}

		return $this->options_element;
	}


	/**
	 * Set parsed value for this field
	 */
	public function SetValue( $value ){
		parent::SetValue( $value );
		if( is_array( $value ) ){
			// Pass value to options in case re-rendering
			foreach( $value as $index => $value ){
				$this->options_elements[ $index ]->value = 1;
			}
		}
	}
	/**
	 * Set default values for checkboxes
	 */
	public function SetDefault( $value ){
		parent::SetDefault($value);
		if( is_array( $value ) ){
			foreach( $this->options_element->elements as $element ){
				if( in_array( $element->field_value, $value ) ){
					$element->SetDefault( true );
				}
			}
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
			if( is_array( $this->value ) && in_array( $option[ 'value' ], $this->value ) ){
				$values[] = $this->options[ $index ][ 'value' ];
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

		$index = count( $this->options ) - 1;
		$checkbox = in4ml::CreateElement( 'Field:Checkbox' );

		$checkbox->name = $this->name.'[' . $index . ']';
		$checkbox->text = $text;

		if( is_array( $this->value ) && in_array( $this->value[ $index ] ) ){
			$checkbox->value = 1;
		}
		if( is_array( $this->default ) && in_array( $value, $this->default ) ){
			$checkbox->default = 1;
		}
		$checkbox->field_value = $value;
		$checkbox->form_id = $this->form_id;

		$this->options_elements[] = $checkbox;
		$this->options_element->AddElement( $checkbox );
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