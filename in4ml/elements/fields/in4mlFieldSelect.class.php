<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Select field
 */
class in4mlFieldSelect extends in4mlField{

	public $type = 'Select';

	protected $container_type = 'Container';

	protected $option_template = '<option value="[[value]]"[[selected]]>[[text]]</option>';

	private $options = array();

	public function __construct(){
		// Automatically validates options
		$this->AddValidator( in4ml::CreateValidator( 'Options' ) );
	}

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();

		// Use default value?
		if( !$render_value && isset( $this->default ) ){
			$this->value = $this->default;
		}

		// Build options HTML
		$values->options = $this->BuildOptions( $this->options );

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
	 * Set the options list in one go
	 *
	 * @param		array		$options		Array of arrays (array('value'=>1, 'text'=>'foo'))
	 */
	public function SetOptions( $options ){
		$this->options = $options;
	}

	/**
	 * Add an option group (<optgroup>)
	 *
	 * @param		string		$label		The label for the group
	 * @param		array		$options	A list of options as array( value => '', text => '' )
	 */
	public function AddOptionGroup( $label, $options, $id = null ){
		$option = array
		(
			'label' => $label,
			'options' => $options
		);
		if( $id !== null ){
			$option[ 'id' ] = $id;
		}

		$this->options[] = $option;
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
	 * Take a list of options and render them as HTML
	 *
	 * @param		array		Array of arrays -- each item could be an option, or an option group
	 *
	 * @return		string
	 */
	protected function BuildOptions( $options ){
		$output = '';
		foreach( $options as $option ){

			// Check to see if it's a group
			if( isset( $option[ 'label' ] ) ){
				$output .= '<optgroup' . ((isset( $option[ 'id' ] ))?' id="' . $option[ 'id' ] . '"':'') . ' label="' . $option[ 'label' ] . '">' . $this->BuildOptions( $option[ 'options' ] ) . '</optgroup>';
			} else {
				if( is_array( $this->value ) ){
					if( count( $this->value ) ){
						$value = (string)$this->value[0];
					} else {
						$value = '';
					}
				} else {
					$value = (string)$this->value;
				}
				$output .= str_replace
				(
					array
					(
						'[[value]]',
						'[[text]]',
						'[[selected]]'
					),
					array
					(
						in4ml::Escape( $option[ 'value' ] ),
						$option[ 'text' ],
						( ( (string)$option[ 'value' ] == $value )?' selected="selected"':'' )
					),
					$this->option_template
				);
			}
		}

		return $output;
	}

}

?>
