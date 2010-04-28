<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Radio field(s)
 */
class in4mlFieldRadio extends in4mlField{

	public $type = 'Radio';
	protected $container_type = 'InlineLabel';

	private $options = array();
	private $options_elements = array();
	
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

	/**
	 * Perform any required modifications on the field
	 *
	 * In this case, convert options list to actual radio buttons
	 *
	 * @return		array		An array of in4mlElement objects
	 */
	public function Modify(){

		$element = in4ml::CreateElement( 'Block:' . $this->container_type );
		$element->AddClass( $this->type );
		$element->label = $this->label;

		foreach( $this->options as $index => $option ){

			$radio_button = in4ml::CreateElement( 'Field:RadioButton' );

			$radio_button->default = $this->default;
			$radio_button->name = $this->name;
			$radio_button->label = $option[ 'text' ];
			$radio_button->field_value = $option[ 'value' ];
			$radio_button->form_id = $this->form_id;
			$radio_button->index = $index;
			
			$this->options_elements[] = $radio_button;
			
			$element->AddElement( $radio_button );
		}

		return $element;
	}
	public function SetValue( $value ){
		foreach( $this->options_elements as $element ){
			$element->SetValue( $value );
		}
	}
}

?>