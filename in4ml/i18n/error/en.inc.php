<?php

$text = array
(
	'confirm' => array
	(
		'default' => 'Confirm field value does not match.'
	),
	'date' => array
	(
		'min' => 'Date must be no earlier than [[min]]',
		'max' => 'Date must be no later than [[max]]'
	),
	'email' => array
	(
		'default' => 'Not a valid email address.'
	),
	'file' => array
	(
		'count' => 'Maximum number of files [[max]], [[count]] uploaded.',
		'max_size' => 'File [[filename]] (size: [[filesize]]) exceeds maximum file size [[max]].',
		'type' => 'File [[filename]] has invalid file type ([[filetype]]). Valid types are: [[validtypes]]'
	),
	'length' => array
	(
		'min' => 'Must be at least [[min]] characters.',
		'max' => 'Must not be longer than [[max]] characters.'
	),
	'numeric' => array
	(
		'default' => 'Not a number.',
		'min' => 'Must not be less than [[value]].',
		'max' => 'Must not be greater than [[value]].',
		'nan' => 'Must be a numeric value.',
	),
	'option' => array
	(
		'default' => 'Invalid value.'
	),
	'regex' => array
	(
		'default' => 'Contains invalid characters.'
	),
	'reject_value' => array
	(
		'default' => 'Invalid value.'
	),
	'required' => array
	(
		'default' => 'This field is required.'
	),
	'url' => array
	(
		'invalid' => 'Not a valid URL.',
		'protocol' => 'Not a valid URL protocol (must be http or https)'
	)
);

?>