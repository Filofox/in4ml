<?php

/**
 * Copyright (c) 2010 Pat Fox
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.gnu.org/licenses/gpl.html) licenses
 */

$this->templates = array
(
	'block' => array
	(
		'form'			=> '<form [[attributes]]><fieldset>[[legend]][[error]][[elements]]</fieldset></form>',
		'group'			=> '<div [[attributes]]><label>[[label]]</label>[[elements]]</div>',
		'inlinelabel'	=> '<div [[attributes]]><label>[[label]]</label>[[elements]]<div class="notes">[[notes]]</div>[[error]]</div>',
		// Extra <div> around fieldset to allow consistent styling (see http://www.tyssendesign.com.au/articles/css/legends-of-style/)
		'set'			=> '<div [[attributes]]><fieldset>[[legend]][[elements]]</fieldset></div>',
		'container'		=> '<div [[attributes]]><label for="[[form_id]]_[[field_name]]">[[label]]</label><span class="prefix">[[prefix]]</span>[[elements]]<span class="suffix">[[suffix]]</span><div class="notes">[[notes]]</div>[[error]]</div>',
		'error'			=> '<div [[attributes]]><ul>[[elements]]</ul></div>',
		'noscript'			=> '<noscript [[attributes]]>[[elements]]</noscript>'
	),
	'field' => array
	(
		'hidden'			=> '<input type="hidden" [[attributes]]/>',
		'text'				=> '<input type="text" [[attributes]]/>',
		'password'			=> '<input type="password" [[attributes]]/>',
		// 'rows' and 'cols' attributes are required for valid XHTML -- these can be overridden with CSS
		'textarea'			=> '<textarea [[attributes]] rows="2" cols="20">[[value]]</textarea>',
		'richtext'			=> '<textarea [[attributes]] rows="2" cols="20">[[value]]</textarea>',
		'select'			=> '<select [[attributes]]>[[options]]</select>',
		'selectmultiple'	=> '<select multiple="multiple" [[attributes]]>[[options]]</select>',
		'radiobutton'		=> '<input type="radio" [[attributes]]/><label for="[[element_id]]" class="element">[[label]]</label>',
		'checkbox'			=> '<input type="checkbox" [[attributes]]/><label for="[[form_id]]_[[name]]" class="element">[[text]]</label>',
		'checkboxmultiple'	=> '<input type="checkboxmutliple" [[attributes]]/><label for="[[form_id]]_[[name]]" class="element">[[text]]</label>',
		'file'				=> '<input type="file" [[attributes]]/>',
		'button'			=> '<input [[attributes]]/>',
		'captcha'			=> '<div class="elements"><img src="[[image_path]]?f=[[form_type]]&e=[[name]]&c=[[uid]]" class="captcha"/><input type="text" [[attributes]]/></div>'
	),
	'general' => array
	(
		'error' => '<li>[[value]]</li>',
		'script' => '<script type="text/javascript">[[code]]</script>'
	)
);

?>