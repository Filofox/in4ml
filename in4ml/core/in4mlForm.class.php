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
	protected $form_type;

	protected $enctype;

	// Flat list of field elements
	protected $fields = array();
	// Flat list of fragment elements
	protected $fragments = array();
	protected $target = false;

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

	// Has been processed
	public $is_processed = false;
	// Either hasn't been validated, or has been validated and failed
	public $is_valid = true;
	// Has been set with submitted values
	public $is_populated = false;

	public $form_id;

	private $cleanup_files = array();

	public $use_javascript = true;
	public $ajax_submit = false;
	public $auto_render = true;

	// Allows for 'automatic' processing of the form
	protected $allow_auto_process = false;

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

		$this->form_type = substr( get_class( $this ), strlen( in4ml::Config( 'form_prefix' ) ) );

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
		$this->form_element->AddClass( strtolower( get_class( $this ) ) );
		if( !$this->is_valid ){
			$this->form_element->AddClass( 'invalid' );
		}
		// Allow 'automatic' form processing?
		if( $this->allow_auto_process ){
			// Add hidden field with form id
			$form_id_field = in4ml::CreateElement( 'Hidden' );
			$form_id_field->name = '__form_id';
			$form_id_field->default = $this->form_id;
			$form_id_field->form_id = $this->form_id;
			$this->form_element->AddElement( $form_id_field );
			$this->fields[] = $form_id_field;
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
			$this->buttons[ $id ] = array
			(
				'button_type' => $button_type
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
	 * Set a button's class
	 *
	 * @param		string		$button_id
	 * @param		string		$class
	 */
	public function SetButtonClass( $button_id, $class ){
		if( isset( $this->buttons[ $button_id ] ) ){
			$this->buttons[ $button_id ][ 'class' ] = $class;
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
			if( $field->type == 'File' ){
				// Advanced file (check that codes field is preent -- if not, it was not submitted via JS)
				if( $field->advanced && isset(  $values[ '_' . $field->name . '_uploadcodes' ] ) ){

					// Rebuild $_FILES array entry

					// Get codes
					$codes = $values[ '_' . $field->name . '_uploadcodes' ];
					if( $codes != '' ){
						$codes = explode( '|', $codes );
					} else {
						$codes = array();
					}

					$_FILES[ $field->name ] = array();
					foreach( $codes as $code ){

						// Find the file
						$temp_dir = sys_get_temp_dir();

						if( substr( $temp_dir, -1 ) != '/' && substr( $temp_dir, -1 ) != "\\" ){
							$temp_dir .= '/';
						}

						$target_dir = $temp_dir . 'in4ml_' . $code . DIRECTORY_SEPARATOR;

						$d = dir( $target_dir );
						while (false !== ($entry = $d->read())) {
							if( strpos( $entry, $code ) !== false ){
								$target_file_name = $entry;
							}
						}
						$file_name = preg_replace( '~\.' . preg_quote( $code ) . '~', '', $target_file_name );
						$this->cleanup_files[] = $target_dir . $target_file_name;
						$d->close();

						$mime_type = in4ml::GetMimeType( $target_dir . $target_file_name, $file_name );

						$_FILES[ $field->name ][] = array
						(
							'name' => $file_name,
							'type' => $mime_type,
							'tmp_name' => $target_dir . $target_file_name,
							'error' => 0,
							'size' => filesize( $target_dir . $target_file_name )
						);
					}
				}
				$field_name = str_replace( '[]', '', $field->name );
				if( isset( $_FILES[ $field_name ] ) && count( $_FILES[ $field_name ] ) > 0 ){
					// Check it's not an array of files
					if( isset( $_FILES[ $field_name  ][ 0 ] ) ){
						// It's an array
						foreach( $_FILES[ $field_name  ] as $file ){
							if( $file[ 'error' ] != 4 ){
								$field->AddFile
								(
									$file[ 'name' ],
									$file[ 'tmp_name' ],
									$file[ 'type' ],
									$file[ 'size' ],
									$file[ 'error' ]
								);
							}
						}
					} else {
						if( $_FILES[ $field_name  ][ 'error' ] != 4 ){
							// Just one file
							$field->AddFile
							(
								$_FILES[ $field_name ][ 'name' ],
								$_FILES[ $field_name ][ 'tmp_name' ],
								$_FILES[ $field_name ][ 'type' ],
								$_FILES[ $field_name ][ 'size' ],
								$_FILES[ $field_name ][ 'error' ]
							);
						}
					}
				}
			} elseif( isset( $values[ $field->name ] ) ){
				$value = $values[ $field->name ];
				// Strip slashes if necessary
				if( get_magic_quotes_gpc() == true ){
					$value = $this->stripslashes( $value );
				}
				$field->SetValue( $value );
			} else {
				$field->SetValue( null );
			}
		}
		$this->is_populated = true;
	}

	/**
	 * Performs stripslahes recursively
	 *
	 * @param		mixed		$value
	 *
	 * @return		mixed
	 */
	private function stripslashes($value)
	{
		return ( is_array($value) )?array_map( array( $this, 'stripslashes' ), $value):stripslashes($value);
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
				return $field->GetValue();
			}
		}
	}

	/**
	 * Get all field values
	 *
	 * @return		object
	 */
	public function GetValues(){
		$values = new stdClass();
		foreach( $this->fields as $field ){
			if( $name = $field->name ){
				if( $name != '__form_id' ){
					$values->$name = $field->GetValue();
				}
			}
		}
		return $values;
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

	public function HasField( $field_name ){
		foreach( $this->fields as $field ){
			if( $field->name == $field_name ){
				return true;
			}
		}
	}

	/**
	 * If the form has been submitted, do validation
	 *
	 * @return		NULL if not submitted, else boolean value for success/fail validation
	 */
	public function Process(){

		// Check that allow_auto_process boolean is true
		if( !$this->allow_auto_process ){
			throw new Exception( 'To use the in4mlForm::Process() method you must first enable the in4mlForm::allow_auto_process property in your form definition' );
		}

		$form_id = false;
		if( $this->submit_method == in4ml::SUBMIT_METHOD_GET ){
			if( isset( $_GET[ '__form_id' ] ) ){
				$form_id = $_GET[ '__form_id' ];
			}
		} else {
			if( isset( $_POST[ '__form_id' ] ) ){
				$form_id = $_POST[ '__form_id' ];
			}
		}

		if( $form_id && $form_id == $this->form_id ){
			$this->is_processed = true;
			return $this->Validate();
		} else {
			return null;
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
			'element_container_class' => in4ml::Config()->default_container_class,
			'fields' => array(),
			'ajax_submit' => $this->ajax_submit,
			'auto_render' => $this->auto_render
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

			foreach( $field->GetPropertiesForJSON() as $key => $value ){
				$field_properties[ $key ] = $value;
			}

			array_push
			(
				$definition[ 'fields' ],
				$field_properties
			);
		}

		return in4ml::json_encode( $definition );
	}

	/**
	 * Set a general 'form' error (as opposed to a specific field error)
	 *
	 * @param		string		$error
	 */
	public function SetFormError( $error ){
		$this->form_element->SetError( $error );
		$this->is_valid = false;
	}

	/**
	 * Get a response object (including all errors etc) that can be encoded (e.g. for AJAX response_
	 *
	 * @return		in4mlFormResponse
	 */
	public function GetResponse(){

		$response = new in4mlFormResponse();

		try{
			$response->success = $this->is_valid;
			// Field errors
			foreach( $this->fields as $field ){
				foreach( $field->GetErrors() as $error ){
					$response->SetError( $field->name, $error );
				}
			}
			// Form errors
			foreach( $this->form_element->GetErrors() as $error ){
				$response->SetFormError( $error );
			}
		} catch( Exception $e ){
			$response->SetFormError( $e->message );
		}

		return $response;
	}

	/**
	 * Render an image for Captcha field
	 */
	public function RenderCaptchaImage( $uid, $field_name ){

		if( $field = $this->GetField( $field_name ) ){

			// Generate text
			$characters = str_split( $field->characters );

			$code = '';
			$max = count( $characters ) - 1;
			while( strlen( $code ) < $field->code_length ){
				$code .= $characters[ rand( 0, $max ) ];
			}

			// Write the code to a PHP file using the uid as a file name
			if( !$captcha_codes_path = in4ml::Config( 'captcha_codes_path' ) ){
				$captcha_codes_path = in4ml::GetPathLocal();
			}
			$files_path = $captcha_codes_path . date( 'YmdH' ) . '/';
			if( !is_dir( $files_path ) ){
				mkdir( $files_path, 0775, true );
			}
			file_put_contents( $files_path . $uid . '.php', "<?php\n" . '$code=\'' . sha1( get_class( $this ) . $code ) . "';\n?>" );

			// Get text size
			$text_dimensions = $this->CalculateTextBox
			(
				$field->font_size,
				0,
				$field->font_path,
				$code
			);

			$width = $text_dimensions[ 'width' ] + $text_dimensions[ 'left' ] + ( $field->padding * 2 );
			$height = $text_dimensions[ 'height' ] + ( $field->padding * 2 );

			// Create the image
			$im = imagecreatetruecolor( $width, $height );

			// Background colour
			$background_colour = $this->hexrgb( $field->background_colour );
			imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocate($im, $background_colour[ 'r' ], $background_colour[ 'g' ], $background_colour[ 'b' ]));

			// Create the text
			$text_colour = $this->hexrgb( $field->text_colour );
			imagettftext(
				$im,
				$field->font_size,
				0,
				$field->padding,
				$field->padding + $text_dimensions[ 'height' ],
				imagecolorallocate( $im, $text_colour[ 'r' ], $text_colour[ 'g' ], $text_colour[ 'b' ] ),
				$field->font_path,
				$code
			);

			if( 0 && $error = error_get_last() ){
				throw new Exception( $error[ 'message' ] );
			} else {
				// Set the content-type
				header('Content-type: image/png');
				// Using imagepng() results in clearer text compared with imagejpeg()
				imagepng($im);
				imagedestroy($im);
			}
			exit;
		} else {
			throw new Exception( 'Invalid field name' );
		}
	}

	public function CheckCaptchaUID( $uid, $user_input ){

		// Do cleanup (maybe)
		if( !rand(0,10) ){
			$this->ClearUpCaptchaCodes();
		}

		$output = false;

		// Try to load file
		if( !$captcha_codes_path = in4ml::Config( 'captcha_codes_path' ) ){
			$captcha_codes_path = in4ml::GetPathLocal() . 'captcha_codes/';
		}
		$file_path = $captcha_codes_path . date( 'YmdH' ) . '/' . $uid . '.php';
		$file_path_old = $captcha_codes_path . date( 'YmdH', strtotime( '-1 hour' ) ) . '/' . $uid . '.php';
		if( file_exists( $file_path_old ) ){
			$file_path = $file_path_old;
		}
		if( file_exists( $file_path ) ) {
			include( $file_path );
			if( $code == sha1( get_class( $this ) . $user_input )  ){
				unlink( $file_path );
				$output = true;
			}
		}

		return $output;
	}

	public function ClearUpCaptchaCodes(){
		if( !$captcha_codes_path = in4ml::Config( 'captcha_codes_path' ) ){
			$captcha_codes_path = in4ml::GetPathLocal() . 'captcha_codes/';
		}
		$file_path = $captcha_codes_path;

		$dirs = array();
		$current = date( 'YmdH' );
		$last = date( 'YmdH', strtotime( '-1 hour' ) );
		$dirs_to_delete = array();
		$d = dir($file_path);
		while (false !== ($entry = $d->read())) {
			if( $entry[ 0 ] != '.' ){
				if( $entry != $current && $entry != $last ){
					array_push( $dirs_to_delete, $entry );
				}
			}
		}
		$d->close();

		foreach( $dirs_to_delete as $dir ){
			$path = $file_path . $dir . '/';
			$d = dir( $path );
			while (false !== ($entry = $d->read())) {
				if( $entry[ 0 ] != '.' ){
					unlink( $path . $entry );
				}
			}
			$d->close();
			rmdir( $path );
		}
	}

	/**
	 * Set the content of a fragment
	 *
	 * @param		string		$name			The name of the fragment
	 * @param		string		$content		The content to be written into the fragment
	 */
	public function SetFragment( $name, $content ){
		/**
		 * Might have more than one fragment with the same name
		 */
		foreach( $this->fragments as $fragment ){
			if( $fragment->name == $name ){
				$fragment->content = $content;
			}
		}
	}

	/**
	 * Taken from http://php.net/manual/en/function.hexdec.php
	 */
	private function hexrgb($hexstr, $rgb = false) {
		$int = hexdec($hexstr);
		switch($rgb) {
			case "r":
			return 0xFF & $int >> 0x10;
				break;
			case "g":
			return 0xFF & ($int >> 0x8);
				break;
			case "b":
			return 0xFF & $int;
				break;
			default:
			return array(
				"r" => 0xFF & $int >> 0x10,
				"g" => 0xFF & ($int >> 0x8),
				"b" => 0xFF & $int
				);
				break;
		}
	}// END GET Cor Hex => RGB

	/**
	 * Taken from http://www.php.net/manual/en/function.imagettfbbox.php
	 */
	private function CalculateTextBox($font_size, $font_angle, $font_file, $text) {
		$box = imagettfbbox($font_size, $font_angle, $font_file, $text);

		$min_x = min(array($box[0], $box[2], $box[4], $box[6]));
		$max_x = max(array($box[0], $box[2], $box[4], $box[6]));
		$min_y = min(array($box[1], $box[3], $box[5], $box[7]));
		$max_y = max(array($box[1], $box[3], $box[5], $box[7]));

		return array(
			'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
			'top' => abs($min_y),
			'width' => $max_x - $min_x,
			'height' => $max_y - $min_y,
			'box' => $box
		);
	}

	/**
	 * Do cleanup
	 */
	public function __destruct(){
		if( count( $this->cleanup_files ) > 0 ){
			foreach( $this->cleanup_files as $file ){
				$pathinfo = pathinfo( $file );
				// Delete file
				if( file_exists( $file ) ){
					//unlink( $file );
				}
				// Delete directory
				if( is_dir( $pathinfo[ 'dirname' ] ) ){
					rmdir( $pathinfo[ 'dirname' ] );
				}
			}
		}
	}
}

/**
 * Class for containing data about form response (e.g. for use with Ajax form submit)
 */
class in4mlFormResponse{
	public $success = false;
	public $form_errors = array();
	public $field_errors = array();

	/**
	 * Add a generic 'form error'
	 *
	 * @param		string		$error
	 */
	public function SetFormError( $error ){
		$this->form_errors[] = $error;
		$this->success = false;
	}

	/**
	 * Add a field error
	 *
	 * @param		string		$field
	 * @param		string		$error
	 */
	public function SetError( $field, $error ){
		$this->field_errors[ $field ][] = $error;
		$this->success = false;
	}
}
?>
