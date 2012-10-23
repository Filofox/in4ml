<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlField.class.php' );

/**
 * Text field
 */
class in4mlFieldDate extends in4mlField{
	public $type = 'Date';
	protected $container_type = 'Container';
	public $format = 'dd/mm/yy'; // Format of javascript date picker
	public $custom_params = array
	(
		'first_day' => 1 // First day of the week 0=Sunday (default = 1 = Monday)
	);

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues( $render_value = false ){
		$values = parent::GetRenderValues();

		// Insert value
		$value = "";
		if( $render_value ){
			// Use submitted value
			$values->SetAttribute( 'value', in4ml::Escape( $this->value ) );
		} else {
			// Use default value?
			if( isset( $this->default ) ){
				$values->SetAttribute( 'value', $this->default );
			}
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

		$element = in4ml::CreateElement( 'Block:Group' );
		$element->AddClass( strtolower( $this->type ) );

		foreach( $this->container_class as $class ){
			$element->AddClass( $class );
		}
		$element->AddClass( 'container' );
		if( $this->notes ){
			$element->notes = $this->notes;
		}

		$element->label = $this->label;

		// Hidden element -- used as a marker by JS, otherwise ignored
		$hidden_element = in4ml::CreateElement( 'Field:Hidden' );
		$hidden_element->name = $this->name;
		$hidden_element->form_id = $this->form_id;

		$element->AddElement( $hidden_element );


		// Wrap in a noscript element
		$noscript_element = in4ml::CreateElement( 'Block:NoScript' );

		// Get date range validator details
		$daterange_validator = false;
		foreach( $this->validators as $validator ){
			if( $validator->GetType() == 'DateRange' ){
				$daterange_validator = $validator;
				break;
			}
		}
		if( !$daterange_validator ){
			throw new Exception( 'Date field must have DateRange validator' );
		}

		// Min and max date
		require_once( in4ml::GetPathLibrary() . 'LibDate.class.php' );
		$min_date = new LibDate();
		$min_date->SetRelativeDateFromString( $daterange_validator->min );
		$max_date = new LibDate();
		$max_date->SetRelativeDateFromString( $daterange_validator->max );

		// Days
		$day_select = in4ml::CreateElement( 'Field:Select' );
		$day_select->name = $this->name . '[day]';
		$day_select->form_id = $this->form_id;
		$day_select->label = 'Day';
		for( $i = 1; $i <= 31; $i++ ){
			$day_select->AddOption( $i, $i );
		}
		$noscript_element->AddElement( $day_select );

		// Months
		$month_select = in4ml::CreateElement( 'Field:Select' );
		$month_select->label = 'Month';
		$month_select->form_id = $this->form_id;
		for( $i = 1; $i <= 12; $i++ ){
			$month_select->AddOption( $i, in4ml::Text( 'Date', 'month_full:' . $i ) );
		}
		$month_select->name = $this->name . '[month]';
		$noscript_element->AddElement( $month_select );

		// Years
		$year_select = in4ml::CreateElement( 'Field:Select' );
		$year_select->name = $this->name . '[year]';
		$year_select->form_id = $this->form_id;
		$year_select->label = 'Year';
		for( $i = $min_date->year; $i <= $max_date->year; $i++ ){
			$year_select->AddOption( $i, $i );
		}
		$noscript_element->AddElement( $year_select );

		$element->AddElement( $noscript_element );

		return $element;
	}

	/**
	 * Set value - converts array to date object
	 */
	public function SetValue( $value ){
		// Dont' set if no value supplied
		if( $value[ 'year' ] ){
			require_once( in4ml::GetPathLibrary() . 'LibDate.class.php' );
			$this->value = new LibDate();
			$this->value->SetFromFormValue( $value );
		}
	}

	/**
	 * Return 'extra' key/value pairs required when exporting field to JSON
	 */
	public function GetPropertiesForJSON(){
		// Get date range validator details
		$daterange_validator = false;
		foreach( $this->validators as $validator ){
			if( $validator->GetType() == 'DateRange' ){
				$daterange_validator = $validator;
				break;
			}
		}

		// Min and max date
		require_once( in4ml::GetPathLibrary() . 'LibDate.class.php' );
		$min_date = new LibDate();
		$min_date->SetRelativeDateFromString( $daterange_validator->min );
		$max_date = new LibDate();
		$max_date->SetRelativeDateFromString( $daterange_validator->max );

		$settings = array
		(
			'format' => $this->format,
			'min_date' => $min_date,
			'max_date' => $max_date
		);

		if( $this->default ){
			if( is_object( $this->default ) ){
				$settings[ 'default' ] = $this->default;
			} else {
				// It's a string
				$default_date = new LibDate();
				$default_date->SetRelativeDateFromString( $this->default );
				$settings[ 'default' ] = $default_date;
			}
		}

		if( $this->value ){
			$settings[ 'value' ] = $this->value;
		}
		if( $this->notes ){
			$settings[ 'notes' ] = $this->notes;
		}

		$settings[ 'custom_params' ] = $this->custom_params;

		return $settings;
	}
}

?>
