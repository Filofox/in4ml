<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlValidator.class.php' );

/**
 * Check for a valid email address
 */
class in4mlValidatorFileType extends in4mlValidator{
	
	public $types = array();
	
	/**
	 * Perform validation
	 * 
	 * @param		in4mlField		$field		The field to be validated
	 *
	 * @return		boolean						False if the field is not valid
	 */
	public function ValidateField( in4mlFieldFile $field ){
		$output = true;
		foreach( $field->files as $file ){
			if( !in_array( $file[ 'mime_type' ], array_keys( $this->types ) ) ){
				$field->SetError( $this->GetErrorText( 'file:type', array( 'filetype' => $file[ 'mime_type' ], 'filename' => $file[ 'name' ], 'validtypes' => implode( $this->types ) ) ) );
				$output = false;
			}
		}

		return $output;
	}
}
// MIME TYPES http://php.net/manual/en/function.mime-content-type.php
/**
I see a lot of comments suggesting doing file extension sniffing (i.e. assuming .jpg files are JPEG images) when proper file-type sniffing functions are unavailable.
I want to point out that there is a much more accurate way.
If neither mime_content_type() nor Fileinfo is available to you and you are running *any* UNIX variant since the 70s, including Mac OS, OS X, Linux, etc. (and most web hosting is), just make a system call to 'file(1)'.
Doing something like this:
<?php
echo system("file -bi '<file path>'");
?>
will output something like "text/html; charset=us-ascii". Some systems won't add the charset bit, but strip it off just in case.
The '-bi' bit is important. However, you can use a command like this:
<?php
echo system("file -b '<file path>'"); // without the 'i' after '-b'
?>
to output a human-readable string, like "HTML document text", which can sometimes be useful.
The only drawback is that your scripts will not work on Windows, but is this such a problem? Just about all web hosts use a UNIX.
It is a far better way than just examining the file extension.
*/

?>
