<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlElement.class.php' );

/**
 * Base class for fields
 */
class In4mlField extends in4mlElement{

	public $prefix;
	public $suffix;
	public $notes;

	public $category = 'Field';

	public $value;
	public $default;

	protected $validators = array();
	protected $filters = array();

	public $container_id;

	protected $errors = array();

	public $confirm_field;

	public $disabled = false;

	// Does this field require JavaScript?
	public $require_javascript = false;

	public function __construct(){
		parent::__construct();

		$this->AddClass( $this->name );
	}

	/**
	 * Add a validator to this field
	 */
	public function AddValidator( in4mlValidator $validator ){
		$this->validators[] = $validator;
	}

	public function GetValidators(){
		return $this->validators;
	}

	/**
	 * Add a filter to this field
	 */
	public function AddFilter( in4mlFilter $filter ){
		$this->filters[] = $filter;
	}

	/**
	 * Get a list of all filters applied to this field
	 *
	 * @return		array
	 */
	public function GetFilters(){
		return $this->filters;
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

		$values->SetAttribute( 'name', $this->name );
		if( $this->name ){
			$values->SetAttribute( 'id', $this->form_id . '_' . $this->name );
		}
		if( $this->disabled ){
			$values->SetAttribute( 'disabled', 'disabled' );
		}

		return $values;
	}

	public function GetPrefix(){
		return $this->prefix;
	}
	public function GetSuffix(){
		return $this->suffix;
	}
	public function GetNotes(){
		return $this->notes;
	}

	/**
	 * Run all validators
	 *
	 * @return		boolean		True if all validators pass
	 */
	public function Validate(){
		foreach( $this->validators as $validator ){
			$validator->ValidateField( $this );
		}

		return $this->IsValid();
	}
	/**
	 * Run all filters
	 */
	public function Filter(){
		foreach( $this->filters as $filter ){
			$filter->FilterField( $this );
		}
	}

	/**
	 * Set field value
	 */
	public function SetValue( $value ){
		$this->value = $value;
	}

	/**
	 * Set field default value
	 */
	public function SetDefault( $value ){
		$this->default = $value;
	}

	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	public function GetValue(){
		return $this->value;
	}

	/**
	 * Add an error for this field
	 *
	 * @param		string		$error_message
	 */
	public function SetError( $error_message ){
		$this->errors[] = $error_message;
		$this->form->is_valid = false;
	}

	/**
	 * Return a list of errors for this field
	 *
	 * @return		array
	 */
	public function GetErrors(){
		return $this->errors;
	}

	/**
	 * Check whether this field passed all validators
	 */
	public function IsValid(){
		return ( count( $this->errors ) == 0 )?true:false;
	}

	/**
	 * Retrieve default value for this field
	 *
	 * @return		mixed
	 */
	public function GetDefault(){
		return $this->default;
	}

	/**
	 * Override this to return any 'extra' key/value pairs required when exporting form to JSON
	 *
	 * @return		array
	 */
	public function GetPropertiesForJSON(){
		return array();
	}

	/**
	 * Create a deep clone of this field
	 *
	 * Usage: $new_field = clone $field;
	 */
	function __clone() {
		foreach($this as $key => $val) {
			if(is_object($val)||(is_array($val))){
				$this->{$key} = unserialize(serialize($val));
			}
		}
	}

	public function DoValidatorModifications(){
		$element = $this;
		foreach( $this->validators as $validator ){
			$element = $validator->ModifyElement( $element );
		}
		return $element;
	}

}

?>
