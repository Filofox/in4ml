<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

require_once( in4ml::GetPathElements() . '/general/in4mlElementScript.class.php' );

/**
 * Hidden field
 */
class in4mlElementScriptDefinition extends in4mlElementScript{
	public $type = 'Script';
	public $category = 'General';
	public $form;

	public $code;

	/**
	 * Return a list of key/value pairs to be interpolated into template
	 *
	 * @param		boolean		$render_value		Include submitted value when rendering
	 *
	 * @return		in4mlElementRenderValues object
	 */
	public function GetRenderValues(){
		$values = parent::GetRenderValues();

		$values->code = 'var in4ml_forms = ((typeof in4ml_forms == "undefined")?[]:in4ml_forms);in4ml_forms.push( ' . $this->form->GetDefinitionAsJSON() . ' );if(typeof in4ml !== "undefined"){in4ml.RegisterForm( in4ml_forms[ 0 ] );in4ml_forms=[]}';

		return $values;
	}
}

?>
