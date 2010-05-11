<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

/**
 * Base class for form types
 *
 * Different form types use different methods to store and retrieve their field definitions
 *
 * Do not extend from this directly to define a form -- extend a form type class instead
 */
class In4mlForm{
	
	// Root element of form
	public $form_element;
	
	protected $enctype;
	
	// Flat list of field elements
	protected $fields = array();
	
	// Whether or not to use internationalisation
	public $enable_i18n = false;
	// What language to render labels/errors/notes/prefixes/suffixes in
	public $lang;
	
	// What type of renderer to use
	protected $renderer_type;
	// What type of template to use
	protected $renderer_template;
	
	// Use GET (URL parameters) or POST submit [default = GET]]
	public $submit_method;

	// Either hasn't been validated, or has been validated and failed
	public $is_valid = true;
	// Has been set with submitted values
	public $is_populated = false;
	
	public $form_id;
	
	public $use_javascript = true;
	
	const BUTTON_TYPE_SUBMIT = 'submit';
	const BUTTON_TYPE_RESET = 'reset';
	const BUTTON_TYPE_BUTTON = 'button';
	
	const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
	const ENCTYPE_MULTIPART = 'multipart/form-data';


	public $buttons = array
	(
		'submit' => array(
			'button_type' => self::BUTTON_TYPE_SUBMIT,
			'label' => 'Submit'
		)
	);
	
	public function __construct(){
		
		// If not overridden in form type defintion
		if( !$this->renderer_type ){
			// What type of renderer to use -- can change during rendering
			$this->renderer_type = in4ml::Config( 'default_renderer' );
		}
		// If not overridden in form type defintion
		if( !$this->renderer_template ){
			// What type of template to use -- can change this during rendering
			$this->renderer_template = in4ml::Config( 'default_renderer_template' );
		}

		if( !$this->form_id ){
			$this->form_id = strtolower( get_class( $this ) );
		}

		// Load and compile list of fields
		$this->ProcessDefinition();
		// Add classes to form
		$this->form_element->AddClass( 'in4ml' );
		$this->form_element->AddClass( get_class( $this ) );
		if( !$this->is_valid ){
			$this->form_element->AddClass( 'invalid' );
		}
	}
	
	/**
	 * Form type subclasses must override this function
	 */
	protected function ProcessDefinition(){
		throw new Exception( 'Method ProcessDefinition not implented for class ' . get_class( $this ) );
	}
	
	/**
	 * Add a button to end of the form
	 */
	public function AddButton( $button_type, $id )
	{
		// Check it's a valid type
		if( $button_type == self::BUTTON_TYPE_SUBMIT || $button_type == self::BUTTON_TYPE_RESET || $button_type == self::BUTTON_TYPE_BUTTON ){
			$this->buttons[ $name ] = array
			(
				'type' => $button_type
			);
		} else {
			throw new Exception( "Button type '$button_type' not valid" );
		}
	}
	
	/**
	 * Set a button's text
	 *
	 * @param		string		$button_id
	 * @param		string		$label
	 */
	public function SetButtonLabel( $button_id, $label ){
		if( isset( $this->buttons[ $button_id ] ) ){
			$this->buttons[ $button_id ][ 'label' ] = $label;
		}
	}
	
	/**
	 * Return HTML representation of the form
	 *
	 * @return		string
	 */
	public function Render( $renderer_type = false, $renderer_template = false ){
	
		// Override renderer type?
		if( $renderer_type ){
			$renderer_type = 'in4mlRenderer' . $renderer_type;
		} else {
			$renderer_type = 'in4mlRenderer' . $this->renderer_type;
		}

		// Override template?
		if( !$renderer_template ){
			$renderer_template = $this->renderer_template;
		}
	
		$renderer_path = in4ml::GetPathRenderers() . $renderer_type . '.class.php';
		if( file_exists( $renderer_path ) ){
			require_once( $renderer_path );
			$renderer = new $renderer_type();
		} else {
			throw new Exception( "Renderer type '$renderer_type' not found ($renderer_path)" );
		}
		
		return $renderer->RenderForm( $this, $renderer_template );
	}

	/**
	 * Read submitted values and pass them to the form
	 */
	public function ParseValues(){
		switch( $this->submit_method ){
			case in4ml::SUBMIT_METHOD_GET:{
				$values = $_GET;
				break;
			}
			case in4ml::SUBMIT_METHOD_POST:{
				$values = $_POST;
				break;
			}
		}
		
		foreach( $this->fields as $field ){
			if( isset( $values[ $field->name ] ) ){
				$field->SetValue( $values[ $field->name ] );
			} elseif( $field->type == 'File' && isset( $_FILES[ $field->name ] ) ){
				// Check it's not an array of files
				if( isset( $_FILES[ $field->name ][ 0 ] ) ){
					// It's an array
					foreach( $_FILES[ $field->name ] as $file ){
						$field->AddFile
						(
							$file[ 'name' ],
							$file[ 'tmp_name' ],
							$file[ 'type' ],
							$file[ 'size' ],
							$file[ 'error' ]
						);
					}
				} else {
					// Just one file
					$field->AddFile
					(
						$_FILES[ $field->name ][ 'name' ],
						$_FILES[ $field->name ][ 'tmp_name' ],
						$_FILES[ $field->name ][ 'type' ],
						$_FILES[ $field->name ][ 'size' ],
						$_FILES[ $field->name ][ 'error' ]
					);
				}
			} else {
				$field->SetValue( null );
			}
		}
		$this->is_populated = true;
	}
	
	/**
	 * Get a field's value
	 *
	 * @param		string		$field_name
	 * 
	 * @return		mixed
	 */
	public function GetValue( $field_name ){
		foreach( $this->fields as $field ){
			if( $field->name == $field_name ){
				return $field->value;
			}
		}
	}
	/**
	 * Set a field's default value
	 *
	 * @param		string		$field_name
	 * @param		mixed		$value
	 */
	public function SetFieldDefault( $field_name, $value ){
		if( $field = $this->GetField( $field_name ) ){
			$field->SetDefault( $value );
		}
	}
	/**
	 * Get a field
	 *
	 * @param		string		$field_name
	 * 
	 * @return		in4mlField
	 */
	public function GetField( $field_name ){
		foreach( $this->fields as $field ){
			if( $field->name == $field_name ){
				return  $field;
			}
		}
	}
	
	/**
	 * Validate all fields
	 *
	 * @return		boolean
	 */
	public function Validate(){
		
		// Load submitted values into fields
		$this->ParseValues();

		// Run filters
		$this->Filter();

		// Do validation
		foreach( $this->fields as $field ){
			if( !$field->Validate() ){
				$this->is_valid = false;
			}
		}

		return $this->is_valid;
	}

	/**
	 * Filter all fields
	 */
	public function Filter(){
		
		foreach( $this->fields as $field ){
			$field->Filter();
		}
	}
	
	/**
	 * Return a JSON-endoded summary of this form
	 *
	 * @return		string
	 */
	public function GetDefinitionAsJson(){

		$definition = array
		(
			'id' => $this->form_id,
			'fields' => array()
		);
		
		foreach( $this->fields as $field ){
			$validators = array();
			foreach( $field->GetValidators() as $validator ){

				$validator_properties = array
				(
					'type' => get_class( $validator )
				);

				foreach( $validator as $property => $value ){
					switch( $property ){
						case 'error_message':{
							if( $value ){
								if( is_array( $value ) ){
									$error_messages = array();
									foreach( $value as $error_type => $error_value ){
										// If this is an array, it must have i18n values
										if( is_array( $error_value ) ){
											// Only encode language-specific values
											$validator_properties[ $property ][ $error_type ] = $error_value[ in4ml::Config( 'lang' ) ];
										} else {
											$validator_properties[ $property ][ $error_type ] = $error_value;
										}
									}
								} else {
									$validator_properties[ $property ] = $value;
								}
							}
							break;
						}
						default:{
							$validator_properties[ $property ] = $value;
							break;
						}
					}
				}
				array_push
				(
					$validators,
					$validator_properties
				);
			}

			// Field
			$field_properties = array
			(
				'type' => $field->type,
				'name' => $field->name,
				'validators' => $validators
			);
			if( $field->confirm_field ){
				$field_properties[ 'has_confirm' ] = true;
			}

			array_push
			(
				$definition[ 'fields' ],
				$field_properties
			);
		}
		
		return json_encode( $definition );
	}

}
?>