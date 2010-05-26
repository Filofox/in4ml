<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathFieldTypes() . 'in4mlFieldTextarea.class.php' );

/**
 * Textarea
 */
class in4mlFieldRichText extends in4mlFieldTextarea{
	public $type = 'RichText';
}

?>