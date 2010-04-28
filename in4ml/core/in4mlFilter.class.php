<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

/**
 * Base class for filters
 */
class in4mlFilter
{
	public function FilterField( in4mlField $field ){
		throw new Exception( 'Method in4mlFilter::FilterField() must be overridden' );
	}
	
	/**
	 * Catch invalid property set
	 */
	public function __set( $property, $value ){
		throw new Exception( "Validator property '" . $property . "' not valid for " . get_class( $this ) );
	}

	/**
	 * Catch invalid property get
	 */
	public function __get( $property ){
		throw new Exception( "Validator property '" . $property . "' not valid for " . get_class( $this ) );
	}

}

?>