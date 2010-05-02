/**
 * Singleton class that handles in4ml functionality
 */
var in4ml = {

	// Lookup for forms (by ID)
	forms:{},

	/**
	 * Initialise in4ml
	 *
	 * @param		JSON		text		List of all dynamic text
	 */
	Init:function( text ){
		this.text = new in4mlText( text );
	},
	
	/**
	 * Register a form for JavaScript handling
	 *
	 * @param		JSON		form_definition
	 */
	RegisterForm:function( form_definition ){
console.log( form_definition );
		this.forms[ form_definition.id ] = new in4mlForm( form_definition );
	},
	
	/**
	 * Generate error text using dynamic text strings
	 *
	 * @param		string		error_type			Identifier for the error
	 * @param		JSON		parameters			[Optional] List of key=>value pairs to be interpolated into the string
	 * @param		mixed		error_messages		Either a string or list of strings of custom error messages
	 */
	GetErrorText:function( error_type, parameters, error_messages ){
		// Is there a custom error message (from definition)?
		if( typeof error_messages != 'undefined' ){
			
			var sub_item = error_type.split( ':' ).pop();
			
			// Text string or list of strings?
			if( is_object( error_messages ) ){
				// Is the required one present?
				if( error_messages[ sub_item ] ){
					text = this.text.Interpolate( parameters, error_messages[ sub_item ] );
				} else {
					// Just use default error message
					text = this.text.Get( 'Error', error_type, parameters );
				}
			} else {
				// It's a string i.e. only one language
				text = this.text.Interpolate( parameters, error_messages );
			}
		} else {
			// Just use default error message
			text = this.text.Get( 'Error', error_type, parameters );
		}
		
		return text;
	},
	GetFieldSelector:function( field_type, field_name ){
		switch( field_type ){
			case 'Select':
			{
				var selector = 'select[name=' + field_name + ']';
				break;
			}
			case 'SelectMultiple':
			{
				var selector = 'select[name=' + field_name + '\\[\\]]';
				break;
			}
			case 'Textarea':
			{
				var selector = 'textarea[name=' + field_name + ']';
				break;
			}
			default:{
				var selector = 'input[name=' + field_name + ']';
				break;
			}
		}
		return selector;
	}
}

/**
 * Handles all dynamic text
 */
in4mlText = function( text ){
	this.text_namespaces = text;
}
/**
 * Find a dynamic string template and return it (interpolating values if necessary)
 *
 * @param		string		namespace		What type of text item (e.g. Error)
 * @param		string		item			Identifier for the item
 * @param		JSON		parameters		[Optional] Key -> value pairs to be interpolated into the string
 */
in4mlText.prototype.Get = function( namespace, item, parameters ){
		
	var parts = item.split( ':' );
	if( parts.length == 1 ){
		parts.push( 'default' );
	}
	item = parts[ 0 ];
	sub_item = parts[ 1 ];

	item_text = this.text_namespaces[ namespace ][ item ][ sub_item ];

	if( parameters && typeof parameters != 'undefined' ){
		item_text = this.Interpolate( parameters, item_text );
	}

	return item_text;
}
/**
 * Find a dynamic string template and return it (interpolating values if necessary)
 *
 * @param		JSON		parameters		Key -> value pairs to be interpolated into the string
 * @param		string		template		The template string
 */
in4mlText.prototype.Interpolate = function( parameters, template ){
	var keys = [];
	var values = [];
	for( var key in parameters ){
		var value = parameters[ key ];
		keys.push( '[[' +  key + ']]' );
		values.push( value );
	}
	return Utilities.Replace
	(
		keys,
		values,
		template
	);
}

/**
 * Form item
 */
in4mlForm = function( form_definition ){

	this.element = $$.Find( 'form#' + form_definition.id ).pop();

	// Build fields list	
	this.fields = [];
	for( var i = 0; i < form_definition.fields.length; i++ ){
		this.fields.push( new in4mlField( this, form_definition.fields[ i ] ) );
	}

	// Bind submit event
	$$.AddEvent
	(
		this.element,
		'submit',
		$$.Bind
		(
			this.HandleSubmit,
			this
		)
	);
}
/**
 * Catch form submit event and do validation
 */
in4mlForm.prototype.HandleSubmit = function(){
	// Form will submit itself automatically if validation is successful
	return this.Validate();
}
/**
 * Do validation
 */
in4mlForm.prototype.Validate = function( form ){

	is_valid = true;
	for( var i = 0; i < this.fields.length; i++ ){
		if( !this.fields[ i ].Validate() ){
			is_valid = false;
		}
	}

	return is_valid;
}


/**
 * Field
 */
in4mlField = function( form, definition ){

	this.type = definition.type;
	this.validators = definition.validators;
	this.name = definition.name;
	this.errors = [];

	this.element = $$.Find( in4ml.GetFieldSelector( this.type, this.name ), form.form_element );
	this.container = $$.FindParent( this.element, '.container' );
	
	this.has_confirm = ( definition.has_confirm ) ? true : false;
}
in4mlField.prototype.GetValue = function(){
	return $$.GetValue( this.element );
}
in4mlField.prototype.Validate = function(){

	// Reset errors
	this.errors = [];

	var value = this.GetValue();
	
	is_valid = true;
	
	for( var i = 0; i < this.validators.length; i++ ){
		
		if( window[this.validators[i].type] ){
			// Instantiate validator object			
			var validator = new window[this.validators[i].type]();
			for( var index in this.validators[i] ){
				validator[ index ] = this.validators[i][ index ];
			}
			if( !validator.ValidateField( this ) ){
				is_valid = false;
			}
		} else {
//			console.log( 'Validator not defined ' + this.validators[i].type );
		}
	}
	
	if( is_valid ){
		$$.RemoveClass( this.container, 'invalid' );
		this.ClearErrors();
	} else {
		$$.AddClass( this.container, 'invalid' );
		this.ShowErrors();
	}
	
return false;
	return is_valid;
}
in4mlField.prototype.SetError = function( error ){
	this.errors.push( error );
	console.log( error );
}
in4mlField.prototype.ClearErrors = function(){
	if( this.error_element ){
		$$.Empty( this.error_element );
	}
}
in4mlField.prototype.ShowErrors = function(){
	this.ClearErrors();
	if( !this.error_element ){
		this.error_element = $$.Create( 'div', { 'class':'error' } );
		$$.Append( this.container, this.error_element );
	}
	var html = '<ul>';
	for( var i = 0; i < this.errors.length; i++ ){
		html += '<li>' + this.errors[ i ] + '</li>';
	}
	html += '</ul>';
	
	$$.SetHTML( this.error_element, html );
}


/**
 * Validators
 */
in4mlValidatorLength = function(){
}
in4mlValidatorLength.prototype.ValidateField = function( field ){

	var output = true;

	var value = field.GetValue();

	if( this.min !== null && value !== '' && value.length < this.min ){
		field.SetError( in4ml.GetErrorText( 'length:min', { 'min': this.min } ) );
		output = false;
	}
	if( this.max !== null && value !== '' && value.length > this.max ){
		field.SetError( in4ml.GetErrorText( 'length:max', { 'max': this.max } ) );
		output = false;
	}
	
	return output;
}
in4mlValidatorNumeric = function(){
}
in4mlValidatorNumeric.prototype.ValidateField = function( field ){
	var output = true;
	
	var value = field.GetValue();

	if( value ){
		if( isNaN( value ) ){
			// Not a number
			field.SetError( in4ml.GetErrorText( "numeric:nan", { 'value' : this.min } ) );
			output = false;
		} else {
			// Minimum?
			if( this.min && value < this.min ){
			// Too big
				field.SetError( in4ml.GetErrorText( "numeric:min", { 'value' : this.min } ) );
				output = false;
			}
			// Maximum?
			if( this.max && value > this.max ){
				// Too small
				field.SetError( in4ml.GetErrorText( "numeric:max", { 'value' : this.max } ) );
				output = false;
			}
		}
	}		
	return output;
}
in4mlValidatorRequired = function(){
}
in4mlValidatorRequired.prototype.ValidateField = function( field ){

	var output = true;

	if( field.GetValue() == null || field.GetValue() == '' || typeof field.GetValue() == 'undefined' ){
		field.SetError( in4ml.GetErrorText( 'required', null, this.error_messages ) );
		output = false;
	}
	
	return output;
}

/**
 * Standardised interface for dealing DOM elements
 */
JSLibInterface_jQuery = function(){
}
/**
 * Find an element using a selector
 *
 * @param		string				selector		The selector string
 * @param		HTMLElement			element			[Optional] Element to find inside
 *
 * @return 		array
 */
JSLibInterface_jQuery.prototype.Find = function( selector, element ){

	// If an element is supplied, find only inside that element
	if( typeof element != 'undefined' ){
		var elements = jQuery( element ).find( selector );
	} else {
		var elements = jQuery( selector );
	}
	
	return jQuery.makeArray( elements );
}
/**
 * Find an element's parent
 *
 * @param		HTMLElement			element			[Optional] Element to find parent
 * @param		string				selector		[Optional] selector string
 *
 * @return 		HTMLElement
 */
JSLibInterface_jQuery.prototype.FindParent = function( element, selector ){

	// If an element is supplied, find only inside that element
	if( typeof selector != 'undefined' ){
		var container = jQuery.makeArray( jQuery( element ).parent( selector ) ).pop();
	} else {
		var container = jQuery.makeArray( jQuery( element ).parent() ).pop();
	}
	
	return container;
}
/**
 * Append an element inside another element
 *
 * @param		HTMLElement			parent_element			Element to append to
 * @param		HTMLElement			child_element			Element to be appended
 */
JSLibInterface_jQuery.prototype.Append = function( parent_element, child_element ){
	jQuery( parent_element ).append( child_element );
}
/**
 * Set the HTML text for an element
 *
 * @param		HTMLElement			element			Element to set HTML for
 * @param		HTMLElement			html			HTML string
 */
JSLibInterface_jQuery.prototype.SetHTML = function( element, html ){
	jQuery( element ).html( html );
}
/**
 * Set the HTML text for an element
 *
 * @param		HTMLElement			element			Element to empty
 */
JSLibInterface_jQuery.prototype.Empty = function( element ){
	jQuery( element ).empty();
}
/**
 * Get an attribute of an element
 *
 * @param		HTMLElement		element
 * @param		string			attribute
 *
 * @return		mixed
 */
JSLibInterface_jQuery.prototype.GetAttribute = function( element, attribute ){
	return jQuery( element ).attr( attribute );
}
/**
 * Bind a callback function to an event
 *
 * @param		HTMLElement		element
 * @param		string			event
 * @param		function		callback
 */
JSLibInterface_jQuery.prototype.AddEvent = function( element, event, callback ){
	return jQuery( element ).bind
	(
		event,
		callback
	);
}
/**
 * Add a CSS class to an element
 *
 * @param		HTMLElement		element
 * @param		string			css_class
 */
JSLibInterface_jQuery.prototype.AddClass = function( element, css_class ){
	jQuery( element ).addClass( css_class );
}
/**
 * Remove a CSS class from an element
 *
 * @param		HTMLElement		element
 * @param		string			css_class
 */
JSLibInterface_jQuery.prototype.RemoveClass = function( element, css_class ){
	jQuery( element ).removeClass( css_class );
}
/**
 * Create an HTML element and populate its properties
 *
 * @param		string		type			What type of element to create
 * @param		object		properties		[Optional] Options to assign to the object (text, html, css, events, id etc.)
 *
 * @return		HTMLElement
 */
JSLibInterface_jQuery.prototype.Create = function( type, properties ){
	var element = jQuery( document.createElement( type ) );

	if(  typeof properties == 'object' ){
		for( var name in properties ){
			var value = properties[ name ];
			switch( name ){
				case 'css':{
					element.css( value );
					break;
				}
				case 'text':{
					element.attr
					(
						( ( jQuery.browser.mozilla ) ? 'textContent' : 'innerText' ),
						value
					);
					break;
				}
				case 'html':{
					element.append( value );
					break;
				}
				case 'events':{
					for( var event in value ){
						element.bind( event, value[ event ] );
					 }
					 break;
				}
				default:{
					element.attr( name, value )
					break;
				}
			}
		}
	}

	return element[0];
},
/**
 * Bind a function to an object scope
 *
 * @param		function		fn				A (usually anonymous) function
 * @param		object			scope			An object to bind the function to
 * @param		array			args			[ Optional ] A list of arguments to pass to the function
 * @param		boolean			override		[ Optional ] If args supplied, should they overwrite the normal arguments or be appended?
 *
 * @return		function
 */
JSLibInterface_jQuery.prototype.Bind = function( fn, scope, args, override ) {
	args = jQuery.makeArray( args );

	return function()
		{
			arguments = jQuery.makeArray( arguments );
			if( args ){
				if( override ){
					arguments = args;
				} else {
					for( var i = 0; i < args.length; i++ ){
						arguments.push( args[ i ] );
					}
				}
			}
			return fn.apply( scope, arguments );
		};
},
/**
 * Get the value of a form element
 *
 * @param		HTMLElement		element
 *
 * @return		mixed
 */
JSLibInterface_jQuery.prototype.GetValue = function( element ){

	var value = null;

	switch( $$.GetAttribute( element, 'type' ) ){
		case 'checkbox':{
			value = $$.GetAttribute( element, 'checked' );
			break;
		}
		case 'radio':{
			for( var i = 0; i < element.length; i++ ){
				if( jQuery( element[ i ] ).attr( 'checked' ) ){
					value = jQuery( element[ i ] ).val();
				}
			}
			break;
		}
		default:{
			value = jQuery( element ).val();
		}
	}

	return value;
}
/**
 * Call a function when document is ready
 *
 * @param		function		callback
 */
JSLibInterface_jQuery.prototype.Ready = function( callback ){
	jQuery( document ).ready
	(
		callback
	);
}

/**
 * Useful utility functions
 */
Utilities = {
	Replace:function(search, replace, subject) {
		// http://kevin.vanzonneveld.net
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +   improved by: Gabriel Paderni
		// +   improved by: Philip Peterson
		// +   improved by: Simon Willison (http://simonwillison.net)
		// +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		// +   bugfixed by: Anton Ongson
		// +      input by: Onno Marsman
		// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +    tweaked by: Onno Marsman
		// *     example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
		// *     returns 1: 'Kevin.van.Zonneveld'
		// *     example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
		// *     returns 2: 'hemmo, mars'

		var f = search, r = replace, s = subject;
		var ra = r instanceof Array, sa = s instanceof Array, f = [].concat(f), r = [].concat(r), i = (s = [].concat(s)).length;

		while (j = 0, i--) {
			if (s[i]) {
				while (s[i] = (s[i]+'').split(f[j]).join(ra ? r[j] || "" : r[0]), ++j in f){};
			}
		};

		return sa ? s : s[0];
	}
}

// This is the wrapper interface for the Javascript library
var $$ = new JSLibInterface_jQuery();

// Initiliase in4ml
$$.Ready(
	function(){
		in4ml.Init();
	}
);
