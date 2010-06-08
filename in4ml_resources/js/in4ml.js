// Class support
// http://ejohn.org/blog/simple-javascript-inheritance/
// Inspired by base2 and Prototype
(function(){
  var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;
  // The base Class implementation (does nothing)
  this.Class = function(){};
  
  // Create a new Class that inherits from this class
  Class.extend = function(prop) {
    var _super = this.prototype;
    
    // Instantiate a base class (but only create the instance,
    // don't run the init constructor)
    initializing = true;
    var prototype = new this();
    initializing = false;
    
    // Copy the properties over onto the new prototype
    for (var name in prop) {
      // Check if we're overwriting an existing function
      prototype[name] = typeof prop[name] == "function" && 
        typeof _super[name] == "function" && fnTest.test(prop[name]) ?
        (function(name, fn){
          return function() {
            var tmp = this._super;
            
            // Add a new ._super() method that is the same method
            // but on the super-class
            this._super = _super[name];
            
            // The method only need to be bound temporarily, so we
            // remove it when we're done executing
            var ret = fn.apply(this, arguments);        
            this._super = tmp;
            
            return ret;
          };
        })(name, prop[name]) :
        prop[name];
    }
    
    // The dummy class constructor
    function Class() {
      // All construction is actually done in the init method
      if ( !initializing && this.init )
        this.init.apply(this, arguments);
    }
    
    // Populate our constructed prototype object
    Class.prototype = prototype;
    
    // Enforce the constructor to be what we expect
    Class.constructor = Class;

    // And make this class extendable
    Class.extend = arguments.callee;
    
    return Class;
  };
})();

/**
 * Singleton class that handles in4ml functionality
 */
var in4ml = {

	// Lookup for forms (by ID)
	forms:{},
	ready_events:{},

	/**
	 * Initialise in4ml
	 *
	 * @param		JSON		text		List of all dynamic text
	 */
	Init:function( text, resources_path ){
		this.text = new in4mlText( text );
		this.resources_path = resources_path;
	},
	
	/**
	 * Register a form for JavaScript handling
	 *
	 * @param		JSON		form_definition
	 */
	RegisterForm:function( form_definition ){
		this.forms[ form_definition.id ] = new in4mlForm( form_definition, this.ready_events[ form_definition.id ] );
		this.forms[ form_definition.id ].Init();
	},
	/**
	 * Load a form via ajax
	 */
	LoadForm:function( form_name, parameters, callback ){
		var form_parameters = {
			'__form_name': form_name
		}
		
		if( typeof parameters == 'object' ){
			for( var index in parameters ){
				form_parameters[ index ] = parameters[ index ];
			}
		}

		$$.JSONRequest
		(
			'/form/load',
			'POST',
			form_parameters,
			$$.Bind
			(
				this.LoadFormSuccess,
				this,
				callback
			),
			$$.Bind
			(
				this.LoadFormError,
				this,
				callback
			)
		);
	},
	
	LoadFormSuccess:function( status, response, callback ){
		// Create a temporary DIV to attach it to
		var div = $$.Create( 'div', {css:{'display':'none'}} );
		$$.Append( document.body, div );
		$( div ).html( response.form_html );
		
		callback( this.forms[ response.form_id ] );

	},
	LoadFormError:function(){
		console.log( 'error' );
		console.log( arguments );
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
			if( typeof error_messages == 'object' ){
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
			case 'Date':
			{
				var selector = 'input[type=hidden][name=' + field_name + ']';
				break;
			}
			case 'RichText':
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
	},
	GetForm:function( form_id ){
		return this.forms[ form_id ];
	},
	onFormReady:function( form_id, callback ){
		if( typeof this.ready_events[ form_id ] == 'undefined' ){
			this.ready_events[ form_id ] = [];
		}
		this.ready_events[ form_id ].push( callback );
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
in4mlForm = function( form_definition, ready_events ){
	
	this.events = {};

	this.element = $$.Find( 'form#' + form_definition.id ).pop();
	this.error_element = $$.Find( this.element, '> div.error' ).pop();

	this.errors = [];
	this.ajax_submit = form_definition.ajax_submit;

	// Build fields list	
	this.fields = {};
	for( var i = 0; i < form_definition.fields.length; i++ ){
		switch( form_definition.fields[ i ].type ){
			case 'Date':{
				var field = new in4mlFieldDate( this, form_definition.fields[ i ] );
				break;
			}
			case 'RichText':{
				var field = new in4mlFieldRichText( this, form_definition.fields[ i ] );
				break;
			}
			default:{
				var field = new in4mlField( this, form_definition.fields[ i ] );
				break;
			}
		}

		this.fields[ form_definition.fields[ i ].name ] = field;
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

	if( ready_events ){
		for( var i = 0; i < ready_events.length; i++ ){
			this.BindEvent( 'ready', ready_events[ i ] );
		}
	}
}

in4mlForm.prototype.Init=function(){
	this.TriggerEvent( 'ready' );
}
/**
 * Return a field by its name
 *
 * @param		field_name
 *
 * @return 		in4mlField
 */
in4mlForm.prototype.GetField = function( field_name ){
	return this.fields[ field_name ];
}
/**
 * Return all fields
 *
 * @return		object
 */
in4mlForm.prototype.GetFields = function(){
	return this.fields;
}
in4mlForm.prototype.Submit = function(){
	$$.Trigger( this.element, 'submit' );
}
/**
 * Catch form submit event and do validation
 */
in4mlForm.prototype.HandleSubmit = function(){
	// Form will submit itself automatically if validation is successful
	var is_valid = this.Validate();

	if( is_valid && this.ajax_submit ){
		// This prevents the form being submitted accidentally due to javascript error
		setTimeout
		(
			$$.Bind
			(
				this.AjaxSubmit,
				this
			),
			0
		);
		is_valid = false;
	}
	
	return is_valid;
}
/**
 * Get all field values
 *
 * @return		object
 */
in4mlForm.prototype.GetValue = function( field_name ){
	var field = this.GetField( field_name );
	return field.GetValue();
}
/**
 * Get all field values
 *
 * @return		object
 */
in4mlForm.prototype.GetValues = function(){
	var values = {};
	for( var index in this.fields ){
		values[ index ] = this.fields[ index ].GetValue();
	}
	return values;
}
/**
 * Set a field value
 *
 * @param		string		field_name
 * @param		mixed		value
 */
in4mlForm.prototype.SetValue = function( field_name, value ){
	var field = this.GetField( field_name );
	field.SetValue( value );
}
/**
 * Submit the form over Ajax
 */
in4mlForm.prototype.AjaxSubmit = function(){
	var values = this.GetValues();
	
	$$.JSONRequest
	(
		$$.GetAttribute( this.element, 'action' ),
		'POST',
		values,
		$$.Bind
		(
			this.HandleAjaxSubmitSuccess,
			this
		),
		$$.Bind
		(
			this.HandleAjaxSubmitError,
			this
		)
	);
}
/**
 * Catch successful AJAX submit
 *
 * @param		string		status		Indicates success or failure
 * @param		object		response	JSON object with details of server response
 */
in4mlForm.prototype.HandleAjaxSubmitSuccess = function( status, response ){
	if( response.success == true ){
		this.TriggerEvent( 'SubmitSuccess' );
	} else {
		// Form errors
		for( var i = 0; i < response.form_errors.length; i++ ){
			this.SetFormError( response.form_errors[ i ] );
		}
		this.ShowErrors();
		// Field errors
		for( var field_name in response.field_errors ){
			var field = this.GetField( field_name );
			for( var i = 0; i < response.field_errors[ field_name ].length; i++ ){
				field.SetError( response.field_errors[ field_name ][ i ] );
			}
			field.ShowErrors();
		}
		this.TriggerEvent( 'SubmitError' );
	}
}
/**
 * Catch successful AJAX submit
 *
 * @param		object		request_object		XMLHTTP request object
 * @param		string		error_code			Code indicating error
 */
in4mlForm.prototype.HandleAjaxSubmitError = function( request_object, error_code  ){
	console.log( 'error' );
	console.log( arguments );
	this.TriggerEvent( 'SubmitError' );
}
/**
 * Do validation
 */
in4mlForm.prototype.Validate = function(){

	var is_valid = true;
	for( var index in this.fields ){
		if( !this.fields[ index ].Validate() ){
			is_valid = false;
		}
	}

	return is_valid;
}
in4mlForm.prototype.SetFormError = function( error ){
	this.errors.push( error );
}
in4mlForm.prototype.ClearErrors = function(){
	$$.RemoveClass( this.element, 'invalid' );
	if( this.error_element ){
		$$.Empty( this.error_element );
	}
	// Clear field errors
	for( var index in this.fields ){
		this.fields[ index ].ClearErrors();
	}
}
in4mlForm.prototype.ShowErrors = function(){
	
	this.ClearErrors();
	if( this.errors.length ){
		$$.AddClass( this.element, 'invalid' );

	}
	
	if( !this.error_element ){
		this.error_element = $$.Create( 'div', { 'class':[ 'error', 'default' ] } );
		$$.Prepend
		(
			$$.Find( '> fieldset', this.element ).pop(),
			this.error_element
		);
	}
	var html = '<ul>';
	for( var i = 0; i < this.errors.length; i++ ){
		html += '<li>' + this.errors[ i ] + '</li>';
	}
	html += '</ul>';
	
	$$.SetHTML( this.error_element, html );
}
in4mlForm.prototype.BindEvent = function( event, func ){

	$$.AddEvent
	(
		this.element,
		event,
		func
	);
	if( typeof this.events[ event ] == 'undefined' ){
		this.events[ event ] = [];
	}
	this.events[ event ].push( func );
}
in4mlForm.prototype.TriggerEvent = function( event ){
	if( typeof this.events[ event ] != 'undefined' ){
		for( var i = 0; i < this.events[ event ].length; i++ ){
			this.events[ event ][ i ]( this, event );
		}
	}
}
in4mlForm.prototype.BindFieldEvent = function( field_name, event, func ){
	this.fields[ field_name ].BindEvent( event, func );
}
in4mlForm.prototype.AppendTo = function( element ){
	$$.Append( element, this.element );
}

/**
 * Field
 */
var in4mlField = Class.extend({
	init:	function( form, definition ){
		this.type = definition.type;
		this.validators = definition.validators;
		this.name = definition.name;
		this.errors = [];
		this.form = form;
		this.element = $$.Find( in4ml.GetFieldSelector( this.type, this.name ), form.element );
		this.container = $$.FindParent( this.element, '.container' );
	
		if( this.container ){
			var error_element = $$.Find( 'div.error', this.container )
			if( error_element.length ){
				this.error_element = error_element[ 0 ];
			}
		}
	
	},
	GetValue:function(){
		return $$.GetValue( this.element );
	},
	/**
	 * Set the value of the field
	 *
	 * @param		mixed		value
	 */
	SetValue:function( value ){
		return $$.SetValue( this.element, value );
	},
	Validate:function(){
	
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
	
		return is_valid;
	},
	SetError:function( error ){
		this.errors.push( error );
	},
	ClearErrors:function(){
		$$.RemoveClass( this.container, 'invalid' );
		if( this.error_element ){
			$$.Empty( this.error_element );
		}
	},
	ShowErrors: function(){
		this.ClearErrors();
		$$.AddClass( this.container, 'invalid' );
	
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
	},
	BindEvent:function( event, func ){
		$$.AddEvent
		(
			this.element,
			event,
			$$.Bind
			(
				function( func, event ){
					func( this, event );
				},
				this,
				[ func, event ],
				true
			)
		);
	}
});
/**
 * Rich text (WYSIWYG) element
 */
var in4mlFieldRichText = in4mlField.extend({
	init:function( form, definition ){
		this._super( form, definition );
		
		var options =
		{
		};
		
		$$.ConvertToRichText
		(
			this.element,
			options
		);
	}
});
/**
 * Datepicker element
 */
var in4mlFieldDate = in4mlField.extend({
	init:function( form, definition ){
		this._super( form, definition );
		var options =
		{
			'format': definition.format,
			'min_date': new Date( definition.min_date.year, definition.min_date.month - 1, definition.min_date.day ),
			'max_date': new Date( definition.max_date.year, definition.max_date.month - 1, definition.max_date.day ),
			'change': $$.Bind
			(
				this.onUpdate,
				this
			)
		};
		
		// Set default date
		if( definition[ 'default' ] ){
			options.default_date = new Date( definition[ 'default' ].year, definition[ 'default' ].month-1, definition[ 'default' ].day );
		}
	
		this.element = $$.ConvertToDatePicker
		(
			this.element,
			options
		);
		
		// Create hidden fields to store
		this.hidden_element_day = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[day]',
				value: definition[ 'default' ].day
			}
		);
		this.hidden_element_month = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[month]',
				value: definition[ 'default' ].month
			}
		);
		this.hidden_element_year = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[year]',
				value: definition[ 'default' ].year
			}
		);
		$$.Append(
			this.container,[ this.hidden_element_day, this.hidden_element_month, this.hidden_element_year ] );
	},
	/**
	 * Called when field is updated. Updates hidden field values
	 *
	 * @param		Date		date		A JavaScript date object
	 */
	onUpdate:function( date ){
		$$.SetValue( this.hidden_element_day, date.getDate() );
		$$.SetValue( this.hidden_element_month, date.getMonth() + 1 );
		$$.SetValue( this.hidden_element_year, date.getFullYear() );
	},
	GetValue:function(){
		return {
			day:$$.GetValue( this.hidden_element_day ),
			month:$$.GetValue( this.hidden_element_month ),
			year:$$.GetValue( this.hidden_element_year )
		}
	}
});

/**************
 * Validators *
 **************/
/**
 * Check confirm field matches
 */
in4mlValidatorConfirm = function(){
}
in4mlValidatorConfirm.prototype.ValidateField = function( field ){
	var output = true;

	var value = field.GetValue();
	var confirm_field = field.form.GetField( field.name + '_confirm' );

	if( confirm_field && value && value != confirm_field.GetValue() ){
		field.SetError( in4ml.GetErrorText( 'confirm' ), null, this.error_message );
		output = false;
	}
	
	return output;
}
/**
 * Check for valid email address
 */
in4mlValidatorEmail = function(){
}
in4mlValidatorEmail.prototype.ValidateField = function( field ){

	var output = true;

	var value = field.GetValue();

	if( value && !Utilities.CheckRegexp( value, this.pattern, ['i'] ) ){
		field.SetError( in4ml.GetErrorText( 'email' ), null, this.error_message );
		output = false;
	}
	
	return output;
}
/**
 * Check string length
 */
in4mlValidatorLength = function(){
}
in4mlValidatorLength.prototype.ValidateField = function( field ){

	var output = true;

	var value = field.GetValue();

	if( this.min !== null && value !== '' && value.length < this.min ){
		field.SetError( in4ml.GetErrorText( 'length:min', { 'min': this.min }, this.error_message ) );
		output = false;
	}
	if( this.max !== null && value !== '' && value.length > this.max ){
		field.SetError( in4ml.GetErrorText( 'length:max', { 'max': this.max }, this.error_message ) );
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
			field.SetError( in4ml.GetErrorText( "numeric:nan", { 'value' : this.min }, this.error_message ) );
			output = false;
		} else {
			// Minimum?
			if( this.min && value < this.min ){
			// Too big
				field.SetError( in4ml.GetErrorText( "numeric:min", { 'value' : this.min }, this.error_message ) );
				output = false;
			}
			// Maximum?
			if( this.max && value > this.max ){
				// Too small
				field.SetError( in4ml.GetErrorText( "numeric:max", { 'value' : this.max }, this.error_message ) );
				output = false;
			}
		}
	}		
	return output;
}
/**
 * Check against a regular expression
 */
in4mlValidatorRegex = function(){
}
in4mlValidatorRegex.prototype.ValidateField = function( field ){
	var output = true;

	var value = field.GetValue();

	if( value ){
		var modifiers = [];
		if( this.ignore_case ){
			modifiers.push( 'i' );
		}
		
		// Run the regex
		if ( Utilities.CheckRegexp( value, this.pattern, modifiers ) ){
			field.SetError( in4ml.GetErrorText( "regex", null, this.error_message ) );
			output = false;
		}
	}
	
	return output;
}
/**
 * Check against a list of values
 */
in4mlValidatorRejectValues = function(){
}
in4mlValidatorRejectValues.prototype.ValidateField = function( field ){
	var output = true;

	var value = field.GetValue();

	if( value ){
		for( var i = 0; i < this.values.length; i++ ){
			if( this.values[ i ] == value ){
				field.SetError( in4ml.GetErrorText( "reject_value", null, this.error_message ) );
				output = false;
			}
		}
	}
	
	return output;
}
/**
 * Field must have a value
 */
in4mlValidatorRequired = function(){
}
in4mlValidatorRequired.prototype.ValidateField = function( field ){

	var output = true;

	if( field.GetValue() == null || field.GetValue() == '' || typeof field.GetValue() == 'undefined' ){
		field.SetError( in4ml.GetErrorText( 'required', null, this.error_message ) );
		output = false;
	}
	
	return output;
}
/**
 * Check for valid URL
 */
in4mlValidatorURL = function(){
}
in4mlValidatorURL.prototype.ValidateField = function( field ){

	var output = true;

	var value = field.GetValue();

	if( value ){
		// Check protocol
		if( !Utilities.CheckRegexp( value, "^(http|https)://", [ 'i' ]  ) ){
			field.SetError( in4ml.GetErrorText( 'url:protocol', null, this.error_message ) );
			output = false;
		// Check well-formedness
		} else if( !Utilities.CheckRegexp( value, "^(http|https)://([\dA-Z0-9-]+\.)+[a-zA-z0-9]{1,3}", [ 'i' ] ) ) {
			field.SetError( in4ml.GetErrorText( 'url:invalid', null, this.error_message ) );
			output = false;
		}
	}
	
	return output;
}

/***************************************************
 * Standardised interface for dealing DOM elements *
 ***************************************************/
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
 * Append an element inside another element
 *
 * @param		HTMLElement			parent_element			Element to prepend to
 * @param		HTMLElement			child_element			Element to be prepended
 */
JSLibInterface_jQuery.prototype.Prepend = function( parent_element, child_element ){
	jQuery( parent_element ).prepend( child_element );
}
/**
 * Remove an element from the DOM
 *
 * @param		HTMLElement			element			Element to be removed
 */
JSLibInterface_jQuery.prototype.Remove = function( element ){
	jQuery( element ).remove();
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
 * Set an attribute of an element
 *
 * @param		HTMLElement		element
 * @param		string			attribute
 * @param		mixed			value
 *
 * @return		mixed
 */
JSLibInterface_jQuery.prototype.SetAttribute = function( element, attribute, value ){
	return jQuery( element ).attr( attribute, value );
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
		this.Bind(
			function(){
				return callback( event, element );
			
			},
			this,
			[ callback, element, event ],
			true
		)
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

	if( typeof properties == 'object' ){
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
				case 'class':{
					// Convert array to string
					if( typeof value == 'object' ){
						value = value.join( ' ' );
					}
					element.addClass( value );
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
 * Trigger a behaviour/handler of an element
 *
 * @param		HTMLElement		element
 * @param		string			event
 */
JSLibInterface_jQuery.prototype.Trigger = function( element, event ){
	$( element ).trigger( event );
}
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
 * Set the value of a form element
 *
 * @param		HTMLElement		element
 * @param		mixed			value
 *
 * @return		mixed
 */
JSLibInterface_jQuery.prototype.SetValue = function( element, value ){
	$( element ).val( value );

	//switch( $$.GetAttribute( element, 'type' ) ){
	//	case 'checkbox':{
	//		value = $$.GetAttribute( element, 'checked' );
	//		break;
	//	}
	//	case 'radio':{
	//		for( var i = 0; i < element.length; i++ ){
	//			if( jQuery( element[ i ] ).attr( 'checked' ) ){
	//				value = jQuery( element[ i ] ).val();
	//			}
	//		}
	//		break;
	//	}
	//	default:{
	//		value = jQuery( element ).val();
	//	}
	//}
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
 * Convert a textarea element to a rich text field
 *
 * @param		HTMLElement		element
 * @param		JSON			options
 */
JSLibInterface_jQuery.prototype.ConvertToRichText = function( element, options ){

	var defaults = {
		// Location of TinyMCE script
		script_url : in4ml.resources_path + 'js/lib/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,formatselect,|,cut,copy,paste,|,search,replace,|,bullist,numlist,|,undo,redo,|,link,unlink",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
	};

	var settings = $.extend(true, defaults, options)

	$( element ).tinymce( settings );
}
/**
 * Convert a set of date selectors to a date picker
 *
 * @param		HTMLElement		element
 * @param		JSON			options
 */
JSLibInterface_jQuery.prototype.ConvertToDatePicker = function( element, options ){
	element = $( element );
	
	var new_element = $( this.Create
		(
			'input',
			{
				'name':element.attr( 'name' ),
				'id':element.attr( 'id' ),
				'class': 'date text'
			}
		)
	);

	element.replaceWith( new_element );

	var settings =
	{
		'dateFormat': options.format,
		'minDate': options.min_date,
		'maxDate': options.max_date,
		'showOn': 'both',
		'buttonImage': in4ml.resources_path + 'img/calendar_icon.png'
	};
	
	// Interpolate settings
	for( var property in options ){
		var value = options[ property ];
		switch( property ){
			case 'button':{
				settings.showOn = ( value ) ? 'both':'focus';
				break;
			}
			case 'button_image':{
				settings.buttonImage = value;
				break;
			}
			case 'change':{
				settings.onSelect = this.Bind(
					function( date_string, date_picker, callback ){
						callback( date_picker.input.datepicker( 'getDate' ) );
					},
					this,
					value
				);
				break;
			}
		}
	}
	
	new_element.datepicker
	(
		settings
	);
	
	// Only seems to work after instantiation, for some reason
	if( options.default_date ){
		new_element.datepicker( 'setDate', options.default_date );
	}
	
	return new_element;
}
	
/**
 * Send a JSON request
 *
 * @param		string			url
 * @param		string			method					GET or POST
 * @param		JSON			data					Key => value pairs
 * @param		function		success_callback
 * @param		function		error_callback
 */
JSLibInterface_jQuery.prototype.JSONRequest = function( url, method, data, success_callback, error_callback ){
	jQuery.ajax
	(
		{
			url:url,
			type:method,
			dataType:'json',
			data:data,
			success:function( request_object, data ){ success_callback( data, request_object ) },
			error:error_callback
		}
	);
}
// Replaces the native jQuery param() method to support sending nested objects
jQuery.extend
(
	{
		// Serialize an array of form elements or a set of
		// key/values into a query string
		param: function( data, name ) {

			var s = [];

			switch( Utilities.GetType(data) ){
				case 'array':{
					// Serialize the form elements
					for( var key = 0; key < data.length; key++ ){
						if( name ){
							fullkey = name + '[' + key + ']';
						} else {
							fullkey = key;
						}
						if( Utilities.GetType( data[ key ] ) == 'array' || Utilities.GetType( data[ key ] ) == 'object' ){
							s.push( jQuery.param( data[ key ], fullkey ) );
						} else {
							if( data[key] === null ){
								data[key] = '';
							}
							s.push( fullkey + "=" + encodeURIComponent( jQuery.isFunction(data[ key ]) ? data[ key ] : data[ key ] ) );
						}
					}
					break;
				}
				case 'object':{
					// Serialize the form elements
					for( var key in data ){
						if( name ){
							fullkey = name + '[' + key + ']';
						} else {
							fullkey = key;
						}
						if( Utilities.GetType( data[ key ] ) == 'array' || Utilities.GetType( data[ key ] ) == 'object' ){
							s.push( jQuery.param( data[ key ], fullkey ) );
						} else {
							if( data[key] === null ){
								data[key] = '';
							}
							s.push( fullkey + "=" + encodeURIComponent( jQuery.isFunction(data[ key ]) ? data[ key ] : data[ key ] ) );
						}
					}
					break;
				}
				default:{
					if( data === null ){
						data = '';
					}
					s.push( encodeURIComponent(name) + "=" + encodeURIComponent( jQuery.isFunction(data) ? data() : data ) );
					break;
				}
			}

			// Return the resulting serialization
			return s.join("&").replace(/%20/g, "+");
		}
	}
);

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
	},
	GetType:function(obj){
		if (obj == undefined) return false;
		if (obj.constructor == Array) return 'array';
		if (obj.nodeName){
			switch (obj.nodeType){
				case 1: return 'element';
				case 3: return (/\S/).test(obj.nodeValue) ? 'textnode' : 'whitespace';
			}
		} else if (typeof obj.length == 'number'){
			if (obj.callee) return 'arguments';
			else if (obj.item) return 'collection';
		}
		return typeof obj;
	},
	/**
	 * Evaluate a regular expression match
	 *
	 * @param		string		field				The field to check
	 * @param		string		pattern				Regexp pattern
	 * @param		array		modifiers			Any modifiers to use
	 *
	 * @return		boolean							Success -- ie. did it match?
	 */
	CheckRegexp:function( value, pattern, modifiers ){

		var output = true;
		
		var regex = new RegExp( pattern, modifiers.join() );

		return regex.test( value );
	}
}

// This is the wrapper interface for the Javascript library
var $$ = new JSLibInterface_jQuery();

(function(b){var e,d,a=[],c=window;b.fn.tinymce=function(j){var p=this,g,k,h,m,i,l="",n="";if(!p.length){return p}if(!j){return tinyMCE.get(p[0].id)}function o(){var r=[],q=0;if(f){f();f=null}p.each(function(t,u){var s,w=u.id,v=j.oninit;if(!w){u.id=w=tinymce.DOM.uniqueId()}s=new tinymce.Editor(w,j);r.push(s);if(v){s.onInit.add(function(){var x,y=v;if(++q==r.length){if(tinymce.is(y,"string")){x=(y.indexOf(".")===-1)?null:tinymce.resolve(y.replace(/\.\w+$/,""));y=tinymce.resolve(y)}y.apply(x||tinymce,r)}})}});b.each(r,function(t,s){s.render()})}if(!c.tinymce&&!d&&(g=j.script_url)){d=1;h=g.substring(0,g.lastIndexOf("/"));if(/_(src|dev)\.js/g.test(g)){n="_src"}m=g.lastIndexOf("?");if(m!=-1){l=g.substring(m+1)}c.tinyMCEPreInit=c.tinyMCEPreInit||{base:h,suffix:n,query:l};if(g.indexOf("gzip")!=-1){i=j.language||"en";g=g+(/\?/.test(g)?"&":"?")+"js=true&core=true&suffix="+escape(n)+"&themes="+escape(j.theme)+"&plugins="+escape(j.plugins)+"&languages="+i;if(!c.tinyMCE_GZ){tinyMCE_GZ={start:function(){tinymce.suffix=n;function q(r){tinymce.ScriptLoader.markDone(tinyMCE.baseURI.toAbsolute(r))}q("langs/"+i+".js");q("themes/"+j.theme+"/editor_template"+n+".js");q("themes/"+j.theme+"/langs/"+i+".js");b.each(j.plugins.split(","),function(s,r){if(r){q("plugins/"+r+"/editor_plugin"+n+".js");q("plugins/"+r+"/langs/"+i+".js")}})},end:function(){}}}}b.ajax({type:"GET",url:g,dataType:"script",cache:true,success:function(){tinymce.dom.Event.domLoaded=1;d=2;if(j.script_loaded){j.script_loaded()}o();b.each(a,function(q,r){r()})}})}else{if(d===1){a.push(o)}else{o()}}return p};b.extend(b.expr[":"],{tinymce:function(g){return g.id&&!!tinyMCE.get(g.id)}});function f(){function i(l){if(l==="remove"){this.each(function(n,o){var m=h(o);if(m){m.remove()}})}this.find("span.mceEditor,div.mceEditor").each(function(n,o){var m=tinyMCE.get(o.id.replace(/_parent$/,""));if(m){m.remove()}})}function k(n){var m=this,l;if(n!==e){i.call(m);m.each(function(p,q){var o;if(o=tinyMCE.get(q.id)){o.setContent(n)}})}else{if(m.length>0){if(l=tinyMCE.get(m[0].id)){return l.getContent()}}}}function h(m){var l=null;(m)&&(m.id)&&(c.tinymce)&&(l=tinyMCE.get(m.id));return l}function g(l){return !!((l)&&(l.length)&&(c.tinymce)&&(l.is(":tinymce")))}var j={};b.each(["text","html","val"],function(n,l){var o=j[l]=b.fn[l],m=(l==="text");b.fn[l]=function(s){var p=this;if(!g(p)){return o.apply(p,arguments)}if(s!==e){k.call(p.filter(":tinymce"),s);o.apply(p.not(":tinymce"),arguments);return p}else{var r="";var q=arguments;(m?p:p.eq(0)).each(function(u,v){var t=h(v);r+=t?(m?t.getContent().replace(/<(?:"[^"]*"|'[^']*'|[^'">])*>/g,""):t.getContent()):o.apply(b(v),q)});return r}}});b.each(["append","prepend"],function(n,m){var o=j[m]=b.fn[m],l=(m==="prepend");b.fn[m]=function(q){var p=this;if(!g(p)){return o.apply(p,arguments)}if(q!==e){p.filter(":tinymce").each(function(s,t){var r=h(t);r&&r.setContent(l?q+r.getContent():r.getContent()+q)});o.apply(p.not(":tinymce"),arguments);return p}}});b.each(["remove","replaceWith","replaceAll","empty"],function(m,l){var n=j[l]=b.fn[l];b.fn[l]=function(){i.call(this,l);return n.apply(this,arguments)}});j.attr=b.fn.attr;b.fn.attr=function(n,q,o){var m=this;if((!n)||(n!=="value")||(!g(m))){return j.attr.call(m,n,q,o)}if(q!==e){k.call(m.filter(":tinymce"),q);j.attr.call(m.not(":tinymce"),n,q,o);return m}else{var p=m[0],l=h(p);return l?l.getContent():j.attr.call(b(p),n,q,o)}}}})(jQuery);