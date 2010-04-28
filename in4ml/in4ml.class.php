<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

/**
 * in4ml core class.
 *
 * Handles config and basic functions. Acts as a singleton, all methods are static
 */
class in4ml{
	
	const SUBMIT_METHOD_GET = 'get';
	const SUBMIT_METHOD_POST = 'post';
	
	// Standard regular expressions (usually for use with ValidatorRegex)
	const REGEX_ALPHA = '[^a-z]';
	const REGEX_ALPHANUMERIC = '[^a-z0-9]';
	const REGEX_ALPHANUMERIC_SPACE = '[^a-z0-9\s]';
	
	const SERVER_OS_WINDOWS = 'WIN';

	/**
	 * Initialise in4ml -- must be called for in4ml to function
	 *
	 * @param		string		$config_path		Path to config file
	 */
	public static function Init( $config_path ){
		$config_object = self::GetConfig();
		// Read config file into config object
		$config = array();

		// Path to in4ml itself
		$path_info = pathinfo( __FILE__ );
		$config[ 'path_base' ] = $path_info[ 'dirname' ] . '/';

		require_once( $config_path . 'im4ml.config.php' );
		foreach( $config as $key => $value ){
			$config_object->$key = $value;
		}
	}
	
	/**
	 * Get a setting from config, or the entire config object
	 *
	 * @param		string		$config_item		[Optional] Which config item to retrieve
	 */
	public static function Config( $config_item = false ){
		$config = self::GetConfig();
		
		if( $config_item ){
			if( isset( $config->$config_item ) ){
				$output = $config->$config_item;
			} else {
				throw new Exception( 'Config item ' . $config_item . 'not set' );
			}
		} else {
			$output = $config;
		}

		return $output;
	}
	
	public function Text( $namespace, $item, $parameters = null ){
		$text = self::GetText();
		
		return $text->Get( $namespace, $item, $parameters );
	}
	
	/**
	 * Return server operating system
	 */
	public function GetServerOS(){
		if( stristr( PHP_OS, self::SERVER_OS_WINDOWS ) ){
			return self::SERVER_OS_WINDOWS;
		} else {
			return PHP_OS;
		}
	}
	
	/**
	 * Convenience function for retrieving base path for in4ml
	 * 
	 * @return		string
	 */
	public function GetPathBase(){
		return self::Config( 'path_base' );
	}
	/**
	 * Convenience function for retrieving core class path
	 * 
	 * @return		string
	 */
	public function GetPathCore(){
		return self::GetPathBase() . 'core/';
	}
	/**
	 * Convenience function for retrieving elements class path
	 * 
	 * @return		string
	 */
	public function GetPathElements(){
		return self::GetPathBase() . 'elements/';
	}
	/**
	 * Convenience function for retrieving blocks class path
	 * 
	 * @return		string
	 */
	public function GetPathBlocks(){
		return self::GetPathElements() . 'blocks/';
	}
	/**
	 * Convenience function for retrieving field types class path
	 * 
	 * @return		string
	 */
	public function GetPathFieldTypes(){
		return self::GetPathElements() . 'fields/';
	}
	/**
	 * Convenience function for retrieving general elements class path
	 * 
	 * @return		string
	 */
	public function GetPathGeneral(){
		return self::GetPathElements() . 'general/';
	}
	/**
	 * Convenience function for retrieving filters class path
	 * 
	 * @return		string
	 */
	public function GetPathFilters(){
		return self::GetPathBase() . 'filters/';
	}
	/**
	 * Convenience function for retrieving validators class path
	 * 
	 * @return		string
	 */
	public function GetPathRenderers(){
		return self::GetPathBase() . 'renderers/';
	}
	/**
	 * Convenience function for retrieving validators class path
	 * 
	 * @return		string
	 */
	public function GetPathValidatorTypes(){
		return self::GetPathBase() . 'validators/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 * 
	 * @return		string
	 */
	public function GetPathFormTypes(){
		return self::GetPathBase() . 'form_types/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 * 
	 * @return		string
	 */
	public function GetPathi18n(){
		return self::GetPathBase() . 'i18n/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 * 
	 * @return		string
	 */
	public function GetPathLibrary(){
		return self::GetPathBase() . 'lib/';
	}
	/**
	 * Convenience function for retrieving local class path
	 * 
	 * @return		string
	 */
	public function GetPathLocal(){
		return self::Config( 'path_local' );
	}
	/**
	 * Convenience function for retrieving forms
	 * 
	 * @return		string
	 */
	public function GetPathForms(){
		return self::GetPathLocal() . 'forms/';
	}

	/**
	 * Create a new element instance
	 *
	 * @param		string		$element_type
	 *
	 * @return		in4mlField
	 */
	public static function CreateElement( $element_type ){
		$type = self::LoadElement( $element_type );
		return new $type();
	}
	/**
	 * Loads a field type class (if not already loaded)
	 *
	 * @param		string		$field_type
	 *
	 * @return		string		Full class name of element
	 */
	public static function LoadElement( $element_type ){
		
		// What type of element is it?
		if( strpos( $element_type, ':' ) !== false ){
			list( $type, $class ) = explode( ':', $element_type );
		} else {
			// Default is a field
			$type = 'Field';
			$class = $element_type;
		}

		switch( $type ){
			case 'Block':{
				$prefix = 'in4mlBlock';
				$path = self::GetPathBlocks();
				break;
			}
			case 'Field':{
				$prefix = 'in4mlField';
				$path = self::GetPathFieldTypes();
				break;
			}
			case 'General':{
				$prefix = 'in4mlElement';
				$path = self::GetPathGeneral();
				break;
			}
			default:{
				throw new Exception( "Element type $type not valid" );
			}
				
		}
		
		$file_path = $path . $prefix . $class . '.class.php';

		if( !class_exists( $prefix . $element_type ) ) {
			if( file_exists( $file_path ) ){
				require_once( $file_path );
			} else {
				throw new Exception( 'Element type ' . $element_type . ' not found' );
			}
		}
		
		return $prefix . $class;
	}

	/**
	 * Create a new validator instance
	 *
	 * @param		string		$validator_type
	 *
	 * @return		in4mlValidator
	 */
	public static function CreateValidator( $validator_type ){
		self::LoadValidatorType( $validator_type );
		$validator_type = 'in4mlValidator' . $validator_type;
		
		return new $validator_type();
	}
	/**
	 * Loads a validator type class (if not already loaded)
	 *
	 * @param		string		$validator_type
	 */
	public static function LoadValidatorType( $validator_type ){
		$file_path = self::GetPathValidatorTypes() . 'in4mlValidator' . $validator_type . '.class.php';
		
		if( !class_exists( 'in4mlValidator' . $validator_type ) ) {
			if( file_exists( $file_path ) ){
				require_once( $file_path );
			} else {
				throw new Exception( 'Validator type ' . $validator_type . ' not found' );
			}
		}
	}
	/**
	 * Create a new filter instance
	 *
	 * @param		string		$filter_type
	 *
	 * @return		in4mlFilter
	 */
	public static function CreateFilter( $filter_type ){
		self::LoadFilter( $filter_type );
		$filter_type = 'in4mlFilter' . $filter_type;
		
		return new $filter_type();
	}
	/**
	 * Loads a filter type class (if not already loaded)
	 *
	 * @param		string		$filter_type
	 */
	public static function LoadFilter( $filter_type ){
		$file_path = self::GetPathFilters() . 'in4mlFilter' . $filter_type . '.class.php';
		
		if( !class_exists( 'in4mlFilter' . $filter_type ) ) {
			if( file_exists( $file_path ) ){
				require_once( $file_path );
			} else {
				throw new Exception( 'Filter type ' . $filter_type . ' not found' );
			}
		}
	}
	
	
	/**
	 * Retrieve a form instance by name
	 *
	 * @param		string		$form_name		Name of the form (without prefix)
	 * @param		mixed						...[optional] any arguments to pass to the constructor
	 *
	 * @return		in4mlForm					A form instance
	 */
	public static function GetForm( $form_type ){
	
		$form_name = self::Config( 'form_prefix' ) . $form_type;

		// Only include it once
		if( !class_exists( $form_name ) ){
			$file_path = self::GetPathForms() .  $form_name . '.class.php';
			if( file_exists( $file_path ) ){
				require_once( $file_path );
			} else {
				throw new Exception( 'Form ' . $form_name . ' not found' );
			}
		}

		// Check for optional arguments
		$arguments = func_get_args();
		// Drop form name
		array_shift( $arguments );
		
		// Are there any arguments?
		if( count( $arguments ) > 0 ){
			// Yes
			$ref = new ReflectionClass( $form_name );
			$instance = $ref->newInstanceArgs( $arguments );
		} else {
			// No
			$instance = new $form_name();
		}
		
		return $instance;
	}

	/**
	 * Get config settings object
	 *
	 * @return		stdClass
	 */
	private static function GetConfig(){
		// Cheeky way to store values in a singelton
		static $config;
		
		// This will only be called once
		if( empty( $config ) ){
			$config = new in4mlConfig();
		}
		
		return $config;
	}
	
	/**
	 * Get a text fragment, using i18n if necessary
	 */
	private static function GetText(){
		// Cheeky way to store values in a singelton
		static $text;
		
		// This will only be called once
		if( empty( $text ) ){
			$text = new in4mlText();
		}
		
		return $text;
	}
	
}

/**
 * Class for storing configuration settings
 */
class in4mlConfig{
	public $path_base;
	public $path_local;
	public $form_prefix;

	// Override these settings in config file if necessary 
	public $default_renderer = 'PHP';
	public $default_renderer_template = 'list';
	
	public $lang = 'en';

	/**
	 * Catch invalid property set
	 */
	public function __set( $property, $value ){
		throw new Exception( 'Configuration property ' . $property . ' not valid'  );
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( 'Configuration property ' . $property . ' not valid'  );
	}
}

/**
 * Class for storing configuration settings
 */
class in4mlText{
	private $text_namespaces;

	public function Get( $namespace, $item, $parameters = array() ){
		if( !isset( $this->text_namespaces[ $namespace ] ) ){
			// Attempt to load text
			$file_path = in4ml::GetPathi18n() . strtolower( $namespace ) . '/' . in4ml::Config( 'lang' ) . '.inc.php';

			include( $file_path );
			
			$this->text_namespaces[ $namespace ] = $text;
		}
		
		if( strpos( $item, ':' ) !== false ){
			list( $item, $sub_item ) = explode( ':', $item );
		} else {
			$sub_item = 'default';
		}

		$item_text = $this->text_namespaces[ $namespace ][ $item ][ $sub_item ];

		if( is_array( $parameters ) && count( $parameters ) ){
			$item_text = self::Interpolate( $parameters, $item_text );
		}

		
		return $item_text;
	}
	
	/**
	 * Interpolate key/value pairs into a template string
	 *
	 * Markers must be formatted as [[marker_name]]
	 *
	 * @param		array		$parameters		List of key/value pairs
	 * @param		string		$template
	 *
	 * @return		string
	 */
	public static function Interpolate( $parameters, $template ){
		$keys = array();
		$values = array();
		foreach( $parameters as $key => $value ){
			$keys[] = "[[$key]]";
			$values[] = $value;
		}
		return str_replace
		(
			$keys,
			$values,
			$template
		);
	}

	/**
	 * Catch invalid property set
	 */
	public function __set( $property, $value ){
		throw new Exception( 'Configuration property ' . $property . ' not valid'  );
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( 'Configuration property ' . $property . ' not valid'  );
	}
}

?>