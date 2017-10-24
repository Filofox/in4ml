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
	// Set to true to add container to dynamically-added options
	public $container_dynamic = false;

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
		$this->options_element->notes = $this->notes;
		$this->options_element->prefix = $this->prefix;
		$this->options_element->suffix = $this->suffix;

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
				if( !is_subclass_of( $element, 'in4mlField' ) ){
					$element = $element->elements[ 0 ];
				}
				if( in_array( $element->field_value, $value ) ){
					$element->SetDefault( true );
				} else {
					$element->SetDefault( false );
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
		$checkbox->template = $this->template;

		if( is_array( $this->value ) && in_array( $this->value[ $index ] ) ){
			$checkbox->value = 1;
		}
		if( is_array( $this->default ) && in_array( $value, $this->default ) ){
			$checkbox->default = 1;
		}
		$checkbox->field_value = $value;
		$checkbox->form_id = $this->form_id;

		// If a container type is specified
		if( $this->container_dynamic && $container_type = $this->GetContainerType() ){
			$element = $checkbox;
			// Create container element
			$container = in4ml::CreateElement( 'Block:' . $container_type );

			if( in4ml::Config()->default_container_class ){
				$container->AddClass( in4ml::Config()->default_container_class );
			}
			if( !in4ml::Config()->no_container_type_class ){
				$container->AddClass( strtolower( $container_type ) );
			}
			$container->AddClass( strtolower( $element->type ) );

			// Set some values
			$container->label = $element->GetLabel();
			$container->field_name = $element->name;
			$container->prefix = $element->GetPrefix();
			$container->suffix = $element->GetSuffix();
			$container->notes = $element->GetNotes();

			$container->form_id = $this->form_id;
			$container->field_name = $element->name;

			// Wrap the element in the container
			$container->AddElement( $checkbox );
			$this->options_elements[] = $checkbox;
			$checkbox = $container;
		} else {
			$this->options_elements[] = $checkbox;
		}
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
