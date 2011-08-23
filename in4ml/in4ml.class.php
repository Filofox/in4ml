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

		$config_object->path_local = $config_path;
		// Read config file into config object
		$config = array();

		// Path to in4ml itself
		$path_info = pathinfo( __FILE__ );
		$config[ 'path_base' ] = $path_info[ 'dirname' ] . '/';

		require_once( $config_path . 'im4ml.config.php' );
		foreach( $config as $key => $value ){
			$config_object->$key = $value;
		}

		if( !$config_object->default_renderer_template ){
			$config_object->default_renderer_template = self::GetPathRenderers() . 'in4mlRendererPHP_templates/list.template.php';
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

	public static function Text( $namespace, $item, $parameters = null ){
		$text = self::GetText();

		return $text->Get( $namespace, $item, $parameters );
	}

	/**
	 * Return server operating system
	 */
	public static function GetServerOS(){
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
	public static function GetPathBase(){
		return self::Config( 'path_base' );
	}
	/**
	 * Convenience function for retrieving core class path
	 *
	 * @return		string
	 */
	public static function GetPathCore(){
		return self::GetPathBase() . 'core/';
	}
	/**
	 * Convenience function for retrieving elements class path
	 *
	 * @return		string
	 */
	public static function GetPathElements(){
		return self::GetPathBase() . 'elements/';
	}
	/**
	 * Convenience function for retrieving blocks class path
	 *
	 * @return		string
	 */
	public static function GetPathBlocks(){
		return self::GetPathElements() . 'blocks/';
	}
	/**
	 * Convenience function for retrieving field types class path
	 *
	 * @return		string
	 */
	public static function GetPathFieldTypes(){
		return self::GetPathElements() . 'fields/';
	}
	/**
	 * Convenience function for retrieving general elements class path
	 *
	 * @return		string
	 */
	public static function GetPathGeneral(){
		return self::GetPathElements() . 'general/';
	}
	/**
	 * Convenience function for retrieving filters class path
	 *
	 * @return		string
	 */
	public static function GetPathFilters(){
		return self::GetPathBase() . 'filters/';
	}
	/**
	 * Convenience function for retrieving validators class path
	 *
	 * @return		string
	 */
	public static function GetPathRenderers(){
		return self::GetPathBase() . 'renderers/';
	}
	/**
	 * Convenience function for retrieving validators class path
	 *
	 * @return		string
	 */
	public static function GetPathValidatorTypes(){
		return self::GetPathBase() . 'validators/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 *
	 * @return		string
	 */
	public static function GetPathFormTypes(){
		return self::GetPathBase() . 'form_types/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 *
	 * @return		string
	 */
	public static function GetPathi18n(){
		return self::GetPathBase() . 'i18n/';
	}
	/**
	 * Convenience function for retrieving form types class path
	 *
	 * @return		string
	 */
	public static function GetPathLibrary(){
		return self::GetPathBase() . 'lib/';
	}
	/**
	 * Convenience function for retrieving local class path
	 *
	 * @return		string
	 */
	public static function GetPathLocal(){
		return self::Config( 'path_local' );
	}
	/**
	 * Convenience function for retrieving resources (CSS, JavaScript) path
	 *
	 * @return		string
	 */
	public static function GetPathResources(){
		return self::Config( 'path_resources' );
	}
	/**
	 * Convenience function for retrieving forms
	 *
	 * @return		string
	 */
	public static function GetPathForms(){
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
				throw new Exception( 'Form ' . $form_name . ' not found [' . $file_path . ']' );
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
	 * Load a form definition directly from a path rather than from default location set in the config
	 *
	 * @param		string		$form_type
	 * @param		string		$path
	 * @param		string		$prefix			[optional] A prefix to use (defaul;t = false, i.e. use default prefix)
	 *
	 * @return		in4mlForm					A form instance
	 */
	public static function GetFormFromPath( $form_type, $path, $prefix = false ){
		if( $prefix === false ){
			$prefix = self::Config( 'form_prefix' );
		}
		if( is_array( $form_type ) ){
			$form_name = $form_type[ 0 ];
			$file_name = $form_type[ 1 ];
		} else {
			$file_name = $form_name;
		}
		$form_name = $prefix . $form_name;

		// Only include it once
		if( !class_exists( $form_name ) ){
			$file_path = $path .  $file_name . '.class.php';
			if( file_exists( $file_path ) ){
				require_once( $file_path );
			} else {
				throw new Exception( 'Form ' . $form_name . ' not found [' . $file_path . ']' );
			}
		}

		// Check for optional arguments
		$arguments = func_get_args();
		if( count( $arguments ) > 3 ){
			// Drop form name
			$arguments = array_slice( $arguments, 3 );
		} else {
			$arguments = array();
		}

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
	 * Write required setup into page header
	 */
	public static function GetInitCode(){

		$output = '<script type="text/javascript" src="' . self::GetPathResources() . 'js/in4ml.js"></script>' . "\n";

		// Make this smarter
		$output .= '<script type="text/javascript" src="' . self::GetPathResources() . 'js/lib/tiny_mce/tiny_mce.js"></script>' . "\n";
		$output .= '<script type="text/javascript" src="' . self::GetPathResources() . 'js/lib/uploadify/jquery.uploadify.min.js"></script>' . "\n";
		$output .= '<script type="text/javascript" src="' . self::GetPathResources() . 'js/lib/uploadify/swfobject.js"></script>' . "\n";
		$output .= '<link rel="stylesheet" type="text/css" href="' . in4ml::GetPathResources() . 'js/lib/uploadify/uploadify.css"/>' . "\n";


		//foreach( in4ml::GetRequiredJavaScript() as $path ){
		//	$output .= ('<script type="text/javascript" src="' . $path . '"></script>');
		//}

		$output .= '<script type="text/javascript" >
			$$.Ready
			(
				function(){
					in4ml.Init({"Error":' . json_encode( self::GetTextNamespace( 'Error' ) ) . '},"' .  self::GetPathResources() . '" );
				}
			)
		</script>
		<link rel="stylesheet" type="text/css" href="' . in4ml::GetPathResources() . 'css/in4ml.default.css"/>
		';

		return $output;

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

	public static function GetTextNamespace( $namespace = false ){
		$text = self::GetText();
		return $text->GetNamespace( $namespace );
	}

	public static function ShowCaptchaImage(){
		if( isset( $_GET[ 'f' ] ) && isset( $_GET[ 'c' ] ) && isset( $_GET[ 'e' ] ) ){

			$pattern = '~[^a-z0-9]*~Ui';

			$form_name = preg_replace( $pattern, '', $_GET[ 'f' ] );
			$code = preg_replace( $pattern, '', $_GET[ 'c' ] );
			$element = preg_replace( $pattern, '', $_GET[ 'e' ] );

			$form = self::GetForm( $form_name );

			$form->RenderCaptchaImage( $code, $element );

		} else {
			throw new Exception( 'Invalid captcha image request' );
		}

	}

	// converts a UTF8-string into HTML entities
	//  - $utf8:        the UTF8-string to convert
	//  - $encodeTags:  booloean. TRUE will convert "<" to "&lt;"
	//  - return:       returns the converted HTML-string
	// taken from http://php.net/manual/en/function.htmlentities.php;
	public static function Escape( $utf8, $encodeTags = true ) {
		$result = '';
		if(is_numeric( $utf8 )){
			return $utf8;
		}
		for ($i = 0; $i < strlen($utf8); $i++) {
			$char = $utf8[$i];
			$ascii = ord($char);
			if ($ascii < 128) {
				// one-byte character
				$result .= ($encodeTags) ? htmlentities($char) : $char;
			} else if ($ascii < 192) {
				// non-utf8 character or not a start byte
			} else if ($ascii < 224) {
				// two-byte character
				$result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
				$i++;
			} else if ($ascii < 240) {
				// three-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$unicode = (15 & $ascii) * 4096 +
						   (63 & $ascii1) * 64 +
						   (63 & $ascii2);
				$result .= "&#$unicode;";
				$i += 2;
			} else if ($ascii < 248) {
				// four-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$ascii3 = ord($utf8[$i+3]);
				$unicode = (15 & $ascii) * 262144 +
						   (63 & $ascii1) * 4096 +
						   (63 & $ascii2) * 64 +
						   (63 & $ascii3);
				$result .= "&#$unicode;";
				$i += 3;
			}
		}
		return $result;
	}
}

/**
 * Class for storing configuration settings
 */
class in4mlConfig{
	public $path_base;
	public $path_local;
	public $path_resources = 'in4ml_resources/';
	public $form_prefix;

	// Override these settings in config file if necessary
	public $default_renderer = 'PHP';
	public $default_renderer_template = false;
	public $override_renderer_template = false;

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

	/**
	 * Get a text snippet
	 */
	public function Get( $namespace, $item, $parameters = array() ){

		$this->LoadNamespace( $namespace );

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

	private function LoadNamespace( $namespace ){
		if( !isset( $this->text_namespaces[ $namespace ] ) ){
			// Attempt to load text
			$file_path = in4ml::GetPathi18n() . strtolower( $namespace ) . '/' . in4ml::Config( 'lang' ) . '.inc.php';

			include( $file_path );

			$this->text_namespaces[ $namespace ] = $text;
		}
	}

	public function GetNamespace( $namespace = false ){
		if( $namespace ){
			$this->LoadNameSpace( $namespace );
			return $this->text_namespaces[ $namespace ];
		} else {
			return $this->text_namespaces;
		}
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

// Setup replacement functions for the deprecated mime_content_type()
// Based on http://www.lowter.com/blogs/2008/4/7/php-determining-mime-type
if (!function_exists('mime_content_type'))
{
    // If Fileinfo extension is installed
    if (function_exists('finfo_file'))
    {
        /**
         * Determine a file's MIME type
         *
         * @param string $file File path
         * @return string
         */
        function mime_content_type($file)
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $file);
            finfo_close($finfo);

            return $mimetype;
        }
    }
    // Otherwise use this method
    else
    {
        /**
         * Determine a file's MIME type
         *
         * @param string $file File path
         * @return string
         */
        function mime_content_type($filename)
        {
			// Windows
			if( strpos( PHP_OS, 'WIN' ) === 0 ){
				$mime_types = array(
					'txt' => 'text/plain',
					'htm' => 'text/html',
					'html' => 'text/html',
					'php' => 'text/html',
					'css' => 'text/css',
					'js' => 'application/javascript',
					'json' => 'application/json',
					'xml' => 'application/xml',
					'swf' => 'application/x-shockwave-flash',
					'flv' => 'video/x-flv',

					// images
					'png' => 'image/png',
					'jpe' => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'jpg' => 'image/jpeg',
					'gif' => 'image/gif',
					'bmp' => 'image/bmp',
					'ico' => 'image/vnd.microsoft.icon',
					'tiff' => 'image/tiff',
					'tif' => 'image/tiff',
					'svg' => 'image/svg+xml',
					'svgz' => 'image/svg+xml',

					// archives
					'zip' => 'application/zip',
					'rar' => 'application/x-rar-compressed',
					'exe' => 'application/x-msdownload',
					'msi' => 'application/x-msdownload',
					'cab' => 'application/vnd.ms-cab-compressed',

					// audio/video
					'mp3' => 'audio/mpeg',
					'qt' => 'video/quicktime',
					'mov' => 'video/quicktime',

					// adobe
					'pdf' => 'application/pdf',
					'psd' => 'image/vnd.adobe.photoshop',
					'ai' => 'application/postscript',
					'eps' => 'application/postscript',
					'ps' => 'application/postscript',

					// ms office
					'doc' => 'application/msword',
					'rtf' => 'application/rtf',
					'xls' => 'application/vnd.ms-excel',
					'ppt' => 'application/vnd.ms-powerpoint',

					// open office
					'odt' => 'application/vnd.oasis.opendocument.text',
					'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				);

				$parts = explode('.',$filename);
				$ext = array_pop($parts);
				$ext = strtolower( $ext );
				if (array_key_exists($ext, $mime_types)) {
					return $mime_types[$ext];
				}
				elseif (function_exists('finfo_open')) {
					$finfo = finfo_open(FILEINFO_MIME);
					$mimetype = finfo_file($finfo, $filename);
					finfo_close($finfo);
					return $mimetype;
				}
				else {
					return 'application/octet-stream';
				}
			} else {
				$parts = explode(';',exec('file -bi '.escapeshellarg($filename)));
				$mime = array_shift( $parts );
	            return trim($mime);
			}
        }
    }
}

?>
