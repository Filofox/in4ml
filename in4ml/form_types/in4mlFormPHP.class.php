<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlForm.class.php' );

/**
 * Form type using hard-coded PHP array to define a form
 */
class In4mlFormPHP extends In4mlForm{

	// Stores details of fields
	protected $definition = array();

	public function AddFieldToDefinition( $field ){
		if( count( $this->fields ) > 0 ){
			throw new Exception( 'Cannot modify form after definition has been processed.' );
		}
		$this->definition[ 'elements' ][] = $field;
	}


	/**
	 * Convert form definition into element objects
	 */
	protected function ProcessDefinition(){
		// Set type of root form element
		if( !isset( $this->definition[ 'type' ] ) ){
			$this->definition[ 'type' ] = 'Block:Form';
		}
		$this->submit_method = $this->definition[ 'submit_method' ];
		if( isset( $this->definition[ 'target' ] ) ){
			$this->target = $this->definition[ 'target' ];
		}
		if( isset( $this->definition[ 'form_id' ] ) ){
			$this->form_id = $this->definition[ 'form_id' ];
		}
		foreach( $this->buttons as $id => $button ){
			$button[ 'type' ] = 'Button';
			$button[ 'id' ] = $id;
			$this->definition[ 'elements' ][] = $button;
		}

		$this->form_element = $this->LoadElement( $this->definition );

		// Add script element?
		if( $this->use_javascript ){

			$element = in4ml::CreateElement( 'General:ScriptDefinition' );
			$element->form = $this;

			$this->form_element->AddElement( $element );
		}

		// Set automatically when file element is present
		$this->form_element->enctype = $this->enctype;

		// Do any structure modifications (e.g. add containers etc)
		$this->form_element = $this->ModifyStructure( $this->form_element );

	}

	/**
	 * Parse definition and compile the structure of the form
	 *
	 * @param		array		$definition
	 *
	 * @return		in4mlElement
	 */
	protected function LoadElement( $definition ){

		// Get field instance
		// Assume field category if no category specified
		if( strpos( $definition[ 'type' ], ':' ) === false ){
			$definition[ 'type' ] = 'Field:' . $definition[ 'type' ];
		}

		$element = in4ml::CreateElement( $definition[ 'type' ] );

		$element->form = $this;
		$element->form_id = $this->form_id;
		$element->form_type = $this->form_type;

		// Set properties
		foreach( $definition as $key => $value ){
			switch( $key ){
				case 'options':{
					foreach( $value as $option ){
						if( isset( $option[ 'value' ] ) ){
							$element->AddOption( $option[ 'value' ], $option[ 'text' ] );
						} else {
							foreach( $option as $group ){
								$element->AddOptionGroup( $group[ 'label' ], $group[ 'options' ] );
							}
						}
					}
					break;
				}
				case 'validators':{
					foreach( $definition[ 'validators' ] as $validator_type => $parameters ){

						$validator = in4ml::CreateValidator( $validator_type );

						$element->AddValidator( $validator );

						if( is_array( $parameters ) ){
							foreach( $parameters as $name => $value ){
								$validator->$name = $value;
							}
						}
					}
					break;
				}
				case 'filters':{
					foreach( $definition[ 'filters' ] as $filter_type => $parameters ){
						$filter = in4ml::CreateFilter( $filter_type );

						$element->AddFilter( $filter );

						if( is_array( $parameters ) ){
							foreach( $parameters as $name => $value ){
								$filter->$name = $value;
							}
						}
					}
					break;
				}
				case 'elements':{
					foreach( $definition[ 'elements' ] as $child_definition ){
						$child_elements = $this->LoadElement( $child_definition );
						// If element is modified, could return more than one element
						if( is_array( $child_elements ) ){
							foreach( $child_elements as $child_element ){
								$element->AddElement( $child_element );
							}
						} else {
							$element->AddElement( $child_elements );
						}
					}
					break;
				}
				case 'type':{
					// Do nothing
					break;
				}
				default:{
					$element->$key = $value;
					break;
				}
			}
		}

		// In case any validators need to modify the element
		if( $element->category == 'Field' ){
			$element = $element->DoValidatorModifications();
		}

		if( is_array( $element ) ){
			foreach( $element as $child_element ){
				if( $child_element->category == 'Field' ){
					$this->fields[] = $child_element;
					// Set encoding for forms with one or more file elements
					if( $child_element->type == 'File' ){
						$this->enctype = self::ENCTYPE_MULTIPART;
					}
				}
				// Store fragments so that their content can be set later
				if( $child_element->category == 'General' && $child_element->type == 'Fragment' ){
					$this->fragments[] = $child_element;
				}
			}
		} else {
			if( $element->category == 'Field' ){
				$this->fields[] = $element;
				// Set encoding for forms with one or more file elements
				if( $element->type == 'File' ){
					$this->enctype = self::ENCTYPE_MULTIPART;
				}
			}
			// Store fragments so that their content can be set later
			if( $element->category == 'General' && $element->type == 'Fragment' ){
				$this->fragments[] = $element;
			}
		}

		return $element;
	}

	/**
	 * Add container elements
	 *
	 * @param		in4mlElement		$element
	 *
	 * @return		in4mlElement
	 */
	private function ModifyStructure( in4mlElement $element ){
		// The element might be self-modifying (e.g. radio buttons)
		$element = $element->Modify();

		if( isset( $element->elements ) ){

			$elements = array();

			foreach( $element->elements as $child_element ){
				$elements[] = $this->ModifyStructure( $child_element );
			}

			$element->elements = $elements;
		}

		// If a container type is specified
		if( $container_type = $element->GetContainerType() ){
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
			$container->AddElement( $element );
			$element = $container;
		}

		return $element;
	}
}

?>
