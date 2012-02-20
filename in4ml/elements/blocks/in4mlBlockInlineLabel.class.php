<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathBlocks() . 'in4mlBlockContainer.class.php' );

/**
 * Container for an element with a label
 */
class in4mlBlockInlineLabel extends in4mlBlock{
	public $type = 'InlineLabel';

	public $label;
	public $field_name;
	public $prefix;
	public $suffix;
	public $notes;
	public $errors;

	public function __construct(){
		parent::__construct();

		$this->AddClass( 'container' );
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

		// Check for element container classes
		foreach( $this->elements as $element ){
			// Check for validator classes
			if( is_subclass_of( $element, 'in4mlField' ) ){
				foreach( $element->GetValidators() as $validator ){
					if( $validator_class = $validator->GetClass( $element ) ){
						$this->AddClass( strtolower( $validator_class ) );
					}
				}
				if( $element->container_id ){
					$this->id = $element->container_id;
				}
			}

			foreach( $element->GetContainerClasses() as $class ){
				$this->AddClass( $class );
			}

		}
		$values = parent::GetRenderValues();

		$values->prefix = $this->prefix;
		$values->suffix = $this->suffix;
		$values->notes = $this->notes;
		$values->errors = $this->errors;

		return $values;
	}
}

?>
