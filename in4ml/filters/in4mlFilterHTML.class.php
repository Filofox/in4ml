<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathCore() . 'in4mlFilter.class.php' );

/**
 * Rmove all HTML tags
 */
class in4mlFilterHTML extends in4mlFilter{
	
	public $allowed_tags;
	
	/**
	 * Perform filtering
	 */
	public function FilterField( in4mlField $field ){
		$field->SetValue( strip_tags( $field->GetValue(), $this->allowed_tags ) );
	}
}

?>