<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathFieldTypes() . 'in4mlFieldSelect.class.php' );

/**
 * Multi-select field
 */
class in4mlFieldSelectMultiple extends in4mlFieldSelect{

	public $type = 'SelectMultiple';

	protected $container_type = 'Container';
	
	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();
		
		// Converts name to an array so that PHP can handle more than one value
		$values->setAttribute( 'name', $this->name . '[]' );
		
		return $values;
	}

	/**
	 * Take a list of options and render them as HTML
	 */
	protected function BuildOptions( $options ){

		$output = '';
		foreach( $options as $option ){
			
			// Check to see if it's a group
			if( isset( $option[ 'value' ] ) ){
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
						htmlentities( $option[ 'value' ] ),
						$option[ 'text' ],
						( ( is_array( $this->value ) && in_array( $option[ 'value' ], $this->value ) )?' selected="selected"':'' )
					),
					$this->option_template
				);
			} else {
				$output .= '<optgroup label="' . $option[ 'label' ] . '">' . $this->BuildOptions( $option[ 'options' ] ) . '</optgroup>';
			}
		}
		
		return $output;
	}
}

?>