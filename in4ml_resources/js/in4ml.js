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
	ready_events_global:[],
	abort_submit: false,


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
	  if( typeof( this.forms[ form_definition.id ] ) != 'undefined' ){
		this.ready_events[ form_definition.id ] = [];
	  }
	  var events = this.ready_events[ form_definition.id ];
	  if( !events ){
		var events = [];
	  }
	  for( var i = 0; i < this.ready_events_global.length; i++ ){
		events.push( this.ready_events_global[i] );
	  }
	  var form = new in4mlForm( form_definition, events );
	  this.forms[ form_definition.id ] = form;
	  // Delay execution due to IE8 DOM problems (can't modify DOM before page load complete)
	  //setTimeout($$.Bind(form.Init,form),0);
	  form.Init();
	},
	/**
	 * Load a form via ajax
	 */
	LoadForm:function( form_path, form_name, parameters, callback ){
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
			form_path,
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

		var container_id = 'in4ml_container_' + response.form_id;
		if( $( 'div#' + container_id ).length > 0 ){
		  $( 'div#' + container_id ).remove();
		}

		// Create a temporary DIV to attach it to
		var div = $$.Create( 'div', {css:{'display':'none'}, id:container_id} );
		$$.Append( document.body, div );
		$( div ).html( response.form_html );

		this.onFormReady( response.form_id, callback );

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

		// Avoid XSS injection
		if( typeof parameters !== 'undefined' ){
		  for( var index in parameters ){
			parameters[ index ] = $$.Escape( parameters[ index ] );
		  }
		}

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
				var selector = 'select[name="' + field_name + '"]';
				break;
			}
			case 'Date':
			{
				var selector = 'input[type=hidden][name="' + field_name + '"]';
				break;
			}
			case 'CheckboxMultiple':{
				var selector = "input.checkbox[name^='" + field_name + "[']";
				break;
			}
			case 'SelectMultiple':{
				var selector = "select.selectmultiple[name='" + field_name + "[]']";
				break;
			}
			case 'RichText':
			case 'Textarea':
			{
				var selector = 'textarea[name="' + field_name + '"]';
				break;
			}
			default:{
				var selector = 'input[name="' + field_name + '"]';
				break;
			}
		}
		return selector;
	},
	GetForm:function( form_id ){
		return this.forms[ form_id ];
	},
	onFormReady:function( form_id, callback ){
		if( typeof this.forms[ form_id ] == 'undefined' ){
			if( typeof this.ready_events[ form_id ] == 'undefined' ){
				this.ready_events[ form_id ] = [];
			}
			this.ready_events[ form_id ].push( callback );
		} else {
			this.forms[ form_id ].BindEvent(
			  'Ready',
			  callback
			);
		}
	},
	/**
	 * Add a callback for every form that's loaded
	 */
	onFormsReady:function( callback ){
		this.ready_events_global.push( callback );
		// Add to existing forms
		for( var id in this.forms ){
			this.forms[ id ].BindEvent(
			  'Ready',
			  callback
			)
		}
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
	return in4mlUtilities.Replace
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

	this.errors_container = '<ul>[[elements]]</ul>';
	this.error_container = '<li>[[value]]</li>';

	this.id = form_definition.id;

	this.ready = false;
	this.fields_initialised = false;
	this.ready_fired = false;

	this.events = {};
	this.validator_functions = [];

	this.element = $$.Find( 'form#' + form_definition.id ).pop();
	this.error_element = $$.Find( this.element, '> div.error' ).pop();

	this.errors = [];
	this.ajax_submit = form_definition.ajax_submit;

	this.file_fields = [];

	// Submit method is called twice when advanced file elements are present
	// This prevents validation + uploads from being performed twice
	this.force_submit = false;

	// Determines whether certain form elements (e.g. RichText) will be drawn automatically when the form is first rendered,
	// or only when the onShow() method is called
	this.auto_render = (typeof form_definition.auto_render != 'undefined') ? form_definition.auto_render : true;

	// Build fields list
	this.fields = {};
	this.form_definition = form_definition;

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
			this.BindEvent( 'Ready', ready_events[ i ] );
		}
	}
	// Remove the init script to prevent it being run again if form is moved
	$$.Remove( $$.Find( 'script', this.element ) );
}

in4mlForm.prototype.Init=function(){

	for( var i = 0; i < this.form_definition.fields.length; i++ ){
		if( this.form_definition.fields[ i ].name ){
			switch( this.form_definition.fields[ i ].type ){
				case 'Captcha':{
					var field = new in4mlFieldCaptcha( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'Checkbox':{
					var field = new in4mlFieldCheckbox( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'CheckboxMultiple':{
					var field = new in4mlFieldCheckboxMultiple( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'Radio':{
					var field = new in4mlFieldRadio( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'Date':{
					var field = new in4mlFieldDate( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'RichText':{
					var field = new in4mlFieldRichText( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'Select':{
					var field = new in4mlFieldSelect( this, this.form_definition.fields[ i ] );
					break;
				}
				case 'File':{
					var field = new in4mlFieldFile( this, this.form_definition.fields[ i ] );
					break;
				}
				default:{
					var field = new in4mlField( this, this.form_definition.fields[ i ] );
					break;
				}
			}

			this.fields[ this.form_definition.fields[ i ].name ] = field;
		}
	}

    this.fields_initialised = true;
	this.FieldReady();
}
in4mlForm.prototype.FieldReady=function(){
  if( this.fields_initialised == true ){
	var fields_count = 0;
	var fields_ready = 0;
	for( var index in this.fields ){
	  fields_count++;
	  if( this.fields[ index ].ready ){
		fields_ready++;
	  }
	}
	if( fields_ready >= fields_count ){
	  this.ready = true;
	  // Allows user to specify different action when submitting over Ajax
	  if( typeof this.ajax_submit == 'string' ){
		this.element.action = this.ajax_submit;
	  }
	  if( !this.ready_fired ){
		this.ready_fired = true;
		this.TriggerEvent( 'Ready' );
	  }
	}
  }
}
in4mlForm.prototype.HasField = function( field_name ){
	return (typeof( this.fields[ field_name ] ) != 'undefined' );
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
in4mlForm.prototype.Submit = function( force_submit ){
	if( typeof force_submit != 'undefined' ){
		this.force_submit = force_submit;
	}
	$$.Trigger( this.element, 'submit' );
}
in4mlForm.prototype.Reset = function(){
	this.ClearErrors();
	$$.Trigger( this.element, 'reset' );
	for( var index in this.fields ){
		var field = this.fields[ index ];
		if( typeof field.Reset == 'function' ){
			field.Reset();
		} else {
		  $( field.element ).val( '' );
		}
	}
}
/**
 * Catch form submit event and do validation
 */
in4mlForm.prototype.HandleSubmit = function(){
	if( this.force_submit != true ){
		this.ClearErrors();

		this.abort_submit = false;

		this.TriggerEvent( 'BeforeValidate' );

		// Form will submit itself automatically if validation is successful
		var is_valid = this.Validate();

		if( !is_valid ){
			this.ShowErrors();
			this.TriggerEvent( 'AfterValidateFail' );
		} else {
			this.TriggerEvent( 'AfterValidateSuccess' );
		}

		// Upload any files
		if( is_valid == true ){
			this.fields_to_upload = 0;
			for( var i = 0; i < this.file_fields.length; i++ ){
				if( this.file_fields[ i ].GetFilesCount() > 0 ){
					this.fields_to_upload++;
					this.file_fields[ i ].Upload();
					// Don't submit immediately
					is_valid = false;
				}
			}
		}
	} else {
		var is_valid = true;
		this.force_submit = false;
	}
	this.TriggerEvent( 'AfterValidate' );

	// Check to see if any BeforeSubmit handlers have requested submit abort
	if( this.abort_submit ){
		is_valid = false;
	} else {
		if( is_valid == true && this.ajax_submit ){
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
	}

	return is_valid;
}
in4mlForm.prototype.AbortSubmit = function(){
	this.abort_submit = true;
}
in4mlForm.prototype.onUploadsComplete = function(){
	this.fields_to_upload--;
	if( this.fields_to_upload == 0 ){
		this.Submit( true );
	}
}
/**
 * Get all field values
 *
 * @return		object
 */
in4mlForm.prototype.GetValue = function( field_name ){
	var field = this.GetField( field_name );
	if( arguments.length > 1 ){
		var args = [];
		for( var i =  1; i < arguments.length; i++ ){
			args.push( arguments[ i ] );
		}
		return field.GetValue.apply( field, args );
	} else {
		return field.GetValue();
	}
}
/**
 * Get all field values
 *
 * @return		object
 */
in4mlForm.prototype.GetValues = function(){
	var values = {};
	for( var index in this.fields ){
		var field = this.fields[ index ];
		values[ index ] = field.GetValue();
		if( field.type == 'File' ){
			var uploadcodes = field.GetUploadCodes();
			if( uploadcodes !== null ){
				values[ '_' + index + '_uploadcodes' ] = uploadcodes;
			}
		}
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

	// Pass values array for pre-submission alterations
	this.TriggerEvent( 'BeforeAjaxSubmit', values );

	$$.JSONRequest
	(
		$$.GetAttribute( this.element, 'action' ),
		$$.GetAttribute( this.element, 'method' ),
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
	this.TriggerEvent( 'AfterSubmit' );
	if( response.success == true ){
		this.TriggerEvent( 'SubmitSuccess', response );
		// Clear file upload queues
		for( var i = 0; i < this.file_fields.length; i++ ){
			this.file_fields[ i ].ClearQueue();
		}
	} else {
	  this.DisplayErrors(response);
	}
}
/**
 * Catch successful AJAX submit
 *
 * @param		object		request_object		XMLHTTP request object
 * @param		string		error_code			Code indicating error
 */
in4mlForm.prototype.HandleAjaxSubmitError = function( request_object, error_code  ){
	if( typeof request_object.responseJSON == 'undefined' ){
	  request_object.responseJSON = jQuery.parseJSON(request_object.responseText);
	}
	this.TriggerEvent( 'AfterSubmit' );
	this.TriggerEvent( 'SubmitError', request_object.responseJSON );
	this.DisplayErrors(request_object.responseJSON);
}
in4mlForm.prototype.DisplayErrors = function( response ){
//console.log(response);
  // Form errors
  for( var i = 0; i < response.form_errors.length; i++ ){
	  this.SetFormError( response.form_errors[ i ] );
  }
  // Field errors
  for( var field_name in response.field_errors ){
	  var field = this.GetField( field_name );
	  for( var i = 0; i < response.field_errors[ field_name ].length; i++ ){
		  field.SetError( response.field_errors[ field_name ][ i ] );
	  }
  }
  this.RenderErrors();
}
in4mlForm.prototype.RenderErrors = function(){
  this.ShowErrors();
  for( var field_name in this.fields ){
	  this.GetField( field_name ).ShowErrors();
  }
}

/**
 * Do validation
 */
in4mlForm.prototype.Validate = function(){

	var is_valid = true;
	this.field_errors = {};
	for( var index in this.fields ){
		if( !this.fields[ index ].Validate() ){
			this.field_errors[ index ] = this.fields[ index ].errors;
			is_valid = false;
		}
	}

	// Custom validator functions
	for( var i = 0; i < this.validator_functions.length; i++ ){
		if( !this.validator_functions[ i ]( this ) ){
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

	this.errors = [];

	if( typeof this.error_element != 'undefined' ){
		$$.Empty( this.error_element );
	}
	// Clear field errors
	for( var index in this.fields ){
		this.fields[ index ].ClearErrors();
	}
}
in4mlForm.prototype.ShowErrors = function(){

	if( this.errors.length ){
		$$.AddClass( this.element, 'invalid' );

		var elements = '';
		for( var i = 0; i < this.errors.length; i++ ){
			elements += in4mlUtilities.Replace( '[[value]]', this.errors[ i ], this.error_container );
		}

		if( typeof this.error_element != 'undefined' ){
			$$.Remove( this.error_element );
		}
		this.error_element = $$.Create( 'div', { 'class':'error default' } );

		$$.SetHTML( this.error_element, in4mlUtilities.Replace( '[[elements]]', elements, this.errors_container ) );

			$$.Prepend
			(
				$$.Find( '> fieldset', this.element ).pop(),
				this.error_element
			);
	}

	//if( this.errors.length ){
	//	$$.AddClass( this.element, 'invalid' );
	//
	//	var elements = '';
	//	for( var i = 0; i < this.errors.length; i++ ){
	//		elements += in4mlUtilities.Replace( '[[value]]', this.errors[ i ], this.error_container );
	//	}
	//
	//	if( typeof this.error_element == 'undefined' ){
	//		this.error_element = $$.Create( in4mlUtilities.Replace( '[[elements]]', elements, this.errors_container ) );
	//		$$.Prepend
	//		(
	//			$$.Find( '> fieldset', this.element ).pop(),
	//			this.error_element
	//		);
	//	}
	//
	//}
}
in4mlForm.prototype.BindEvent = function( event, func ){
  if( typeof this.events[ event ] == 'undefined' ){
	  this.events[ event ] = [];
  }
  this.events[ event ].push( func );
  if( event == "Ready" && this.ready == true ){
	// Has ready event already been fired?
	if( !this.ready_fired ){
	  // No, fire all events
	  this.ready_fired = true;
	  this.TriggerEvent( "Ready" );
	} else {
	  // Yes, just fire this event
	  func( this, "Ready" );
	}
  } else {
	$$.AddEvent
	(
		this.element,
		event,
		func
	);
  }
}
in4mlForm.prototype.TriggerEvent = function( event ){
	if( typeof this.events[ event ] != 'undefined' ){
		var args = [ this, event ];
		if( arguments.length > 1 ){
			for( var i = 1; i < arguments.length; i++ ){
				args.push( arguments[ i ] );
			}
		}
		for( var i = 0; i < this.events[ event ].length; i++ ){
			this.events[ event ][ i ].apply( null, args );
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
 * Add a custom validation function
 *
 * @param		function		The function -- will recieve this (form) as its only parameter. Should return true (is valid) or false (is not valid)
 */
in4mlForm.prototype.AddValidator = function( validator_func ){
	this.validator_functions.push( validator_func );
}
/**
 * Call this when form is hidden
 */
in4mlForm.prototype.onHide = function(){
	for( var index in this.fields ){
		this.fields[ index ].onHide();
	}
}
/**
 * Call this when form is shown after being hidden (e.g. display:none)
 */
in4mlForm.prototype.onShow = function(){
	for( var index in this.fields ){
		this.fields[ index ].onShow();
	}
}
/**
 * Add an advanced file field to the list
 */
in4mlForm.prototype.AddFileField = function( field ){
	this.file_fields.push( field );
}
/**
 * Get form action
 */
in4mlForm.prototype.GetAction = function(){
	return $( this.element ).attr( 'action' );
}
/**
 * Set form action
 */
in4mlForm.prototype.SetAction = function( action ){
	if( typeof this.original_action == "undefined" ){
		this.original_action = this.GetAction();
	}
	$( this.element ).attr( 'action', action );
}
/**
 * Reset form action
 */
in4mlForm.prototype.ResetAction = function(){
	$( this.element ).attr( 'action', this.original_action );
}


/**
 * Field
 */
var in4mlField = Class.extend({
	errors_container: '<ul>[[elements]]</ul>',
	error_container:'<li>[[value]]</li>',
	hidden:false,

	ready: true,
	is_valid: true,
	init:function( form, definition ){
		if( this.onBeforeInit ){
			this.onBeforeInit( form, definition );
		}
		this.type = definition.type;
		this.validators = definition.validators;
		this.name = definition.name;
		this.errors = [];
		this.form = form;
		this.element = this.FindElement();

		this.container = null;

		var no_container = false;
		var element = this.element;
		while( this.container == null && no_container == false ){
			element = $$.FindParent( element );
			var type = $$.GetAttribute( element, 'tagName' );
			if( $$.HasClass( element, 'container' ) ){
				this.container = element;
			}else if( type  == 'FORM' || type == 'BODY' || typeof element == 'undefined' ){
				no_container = true;
			}
		}

		if( this.container ){
			var error_element = $$.Find( 'div.error', this.container )
			if( error_element.length ){
				this.error_element = error_element[ 0 ];
			}
		}
		if( this.onAfterInit ){
			this.onAfterInit( form, definition );
		}
		if( this.ready ){
		  this.form.FieldReady( this );
		}
	},
	FindElement:function(){
		return $$.Find( in4ml.GetFieldSelector( this.type, this.name ), this.form.element );
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

		this.is_valid = true;

		for( var i = 0; i < this.validators.length; i++ ){

			if( window[this.validators[i].type] ){
				// Instantiate validator object
				var validator = new window[this.validators[i].type]();
				for( var index in this.validators[i] ){
					validator[ index ] = this.validators[i][ index ];
				}
				if( !validator.ValidateField( this ) ){
					this.is_valid = false;
				}
			} else {
	//			console.log( 'Validator not defined ' + this.validators[i].type );
			}
		}

		$$.Trigger( this.element, 'Validate', this );

		if( this.is_valid ){
			this.ClearErrors();
		} else {
			this.ShowErrors();
		}

		return this.is_valid;
	},
	SetError:function( error ){
		this.errors.push( error );
	},
	ClearErrors:function(){
		this.errors = [];
		$$.RemoveClass( this.container, 'invalid' );
		if( this.error_element ){
			$$.Empty( this.error_element );
		}
	},
	ShowErrors: function(){

		//if( !this.error_element ){
		//	this.error_element = $$.Create( 'div', { 'class':'error' } );
		//	$$.Append( this.container, this.error_element );
		//} else {
		//	$$.Empty( this.error_element );
		//}
		//var html = '<ul>';
		//for( var i = 0; i < this.errors.length; i++ ){
		//	html += '<li>' + this.errors[ i ] + '</li>';
		//}
		//html += '</ul>';
		//
		//$$.SetHTML( this.error_element, html );
		if( this.errors.length ){
			$$.AddClass( this.container, 'invalid' );
			$$.AddClass( this.element, 'invalid' );

			var elements = '';
			for( var i = 0; i < this.errors.length; i++ ){
				elements += in4mlUtilities.Replace( '[[value]]', this.errors[ i ], this.error_container );
			}

			if( typeof this.error_element != 'undefined' ){
				$$.Remove( this.error_element );
			}
			this.error_element = $$.Create( 'div', { 'class':'error' } );

			$$.SetHTML( this.error_element, in4mlUtilities.Replace( '[[elements]]', elements, this.errors_container ) );

			$$.Append
			(
				this.container,
				this.error_element
			);
		}

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
	},
	onHide:function(){},
	onShow:function(){},
	Disable:function(){
	  $$.SetAttribute( this.element, 'disabled', true);
	},
	Enable:function(){
	  $$.SetAttribute( this.element, 'disabled', false);
	}
});
/**
 * Captcha field
 */
var in4mlFieldCaptcha = in4mlField.extend({
	// Reset -- i.e. load new Captcha image
	Reset:function( value ){
		var img = $$.Find( 'img.captcha', this.container );
		var src = $$.GetAttribute( img, 'src' );
		var code = src.match( /(?:&|\?)c=([a-z0-9]*)/i )[ 1 ];

		var new_code = '';
		var characters = 'abcdefghijklmnopqrstuvwxyz1234567890';
		for( var i=0; i < 41; i++ ){
			new_code += characters.charAt( Math.random() * characters.length );
		}
		var src = src.replace( code, new_code );
		this.form.GetField( this.name + '_uid' ).SetValue( new_code );
		$$.SetAttribute( img, 'src', src );
	}
});
/**
 * Single checkbox
 */
var in4mlFieldCheckbox = in4mlField.extend({
	// Set value
	SetValue:function( value ){
		$$.SetAttribute( this.element, 'checked', value );
	},
	// Set value
	GetValue:function( value ){
		return $$.GetAttribute( this.element, 'checked' );
	},
	Reset:function(){
	  $$.SetAttribute( this.element, 'checked', $$.GetAttribute( this.element, 'defaultChecked' ) );
	}
});
/**
 * Multiple checkboxes
 */
var in4mlFieldCheckboxMultiple = in4mlField.extend({
	// Get value
	GetValue:function(){
		var values = [];
		for( var i = 0; i < this.element.length; i++ ){
			if( $$.GetValue( this.element[ i ] ) == true ){
				values.push( $$.GetAttribute( this.element[ i ], 'value' ) );
			}
		}

		return values;
	},
	SetValue:function( values ){
		var by_index = {};
		for( var i= 0; i < values.length; i++ ){
		  by_index[ values[ i ] ] = true;
		}
		for( var i = 0; i < this.element.length; i++ ){
			if( typeof by_index[ $$.GetAttribute( this.element[ i ], 'value' ) ] != 'undefined' ){
    	  		$$.SetAttribute( this.element[ i ], 'checked', true );
			} else {
		  		$$.SetAttribute( this.element[ i ], 'checked', false );
			}
		}
	},
	Reset:function(){
		for( var i = 0; i < this.element.length; i++ ){
	  		$$.SetAttribute( this.element[ i ], 'checked', false );
		}
	}
});
/**
 * File field
 */
var in4mlFieldFile = in4mlField.extend({
	upload_codes_element:null,
	files_count: 0,
	advanced: false,
	/**
	 * Do this after initialisation
	 */
	onAfterInit:function( form, definition ){
		if( definition.advanced == true ){
			this.advanced = true;
			var options =
			{
			};
			$$.ConvertToAdvancedFile( this, options );
			form.AddFileField( this );
		}
	},
	/**
	 * Initiate file upload
	 */
	Upload:function(){
		$$.FileUpload( this );
	},
	/**
	 * Add element where uploaded file codes are to be stored
	 */
	SetUploadCodeElement:function( element ){
		this.upload_codes_element = element;
	},
	/**
	 * Add code for uploaded file to list of file codes
	 */
	AddUploadedFileCode:function( code ){
		var codes = $$.GetValue( this.upload_codes_element );
		if( codes != '' ){
			codes = codes.split( '|' );
		} else {
			codes = [];
		}
		var exists = false;
		for( var i = 0; i < codes.length; i++ ){
			if( codes[ i ] == code ){
				exists = true;
			}
		}
		if( exists == false ){
			codes.push( code );
		}

		$$.SetValue( this.upload_codes_element, codes.join( '|' ) );
	},
	GetUploadCodes:function(){
		if( this.upload_codes_element !== null ){
			return $$.GetValue( this.upload_codes_element );
		} else {
			return null;
		}
	},
	AddFile:function(){
		this.files_count++;
	},
	RemoveFile:function(){
		this.files_count--;
	},
	GetFilesCount:function(){
		return this.files_count;
	},
	SetFilesCount:function( value ){
		this.files_count = value;
	},
	ClearQueue:function(){
		$$.SetValue( this.upload_codes_element, '' );
		$$.FileClearQueue( this );
	},
	onUploadsComplete:function(){
		if(this.files_count == 0){
			this.form.onUploadsComplete( this );
		} else {
			alert( 'There was an error uploading your file(s). Please try again.' );
		}
	},
	onHide:function(){
		if( this.advanced ){
			$$.AdvancedFileOnHide( this );
		}
		this.hidden = true;
	},
	onShow:function(){
		if( this.advanced ){
			$$.AdvancedFileOnShow( this );
		}
		this.hidden = false;
	},
	Reset:function(){
		if( this.advanced ){
			$$.AdvancedFileReset( this );
		}
	}
});
/**
 * Radio button(s)
 */
var in4mlFieldRadio = in4mlField.extend({
  	init:function( form, definition ){
	  this._super( form, definition );
	  this.default_value = $$.GetValue( $$.Find( 'input.radiobutton:checked', this.container ) );
	},
	//GetValue:function(){
	//	// Check selected
	//	var checked = $$.Find( 'input.radiobutton:checked', this.container );
	//	if( checked.length > 0 ){
	//	}
	//	return null;
	//},
	// Set value (i.e. set which one is checked)
	SetValue:function( value ){
		// Uncheck all
		$( this.container ).find( 'input.radiobutton' ).attr( 'checked', false );
		// Check selected
		$( $( this.container ).find( 'input.radiobutton[value=' + value + ']' ) ).attr( 'checked', true );
	},
	Reset:function(){
	  // Do nothing
	  this.SetValue( this.default_value );
	}
});
/**
 * Rich text (WYSIWYG) element
 */
var in4mlFieldRichText = in4mlField.extend({
	ready: false,
	hidden: false,
	init:function( form, definition ){
		this._super( form, definition );

		var options = {};

		if( typeof definition.custom_params != 'undefined' ){
			for( var index in definition.custom_params ){
				options[ index ] = definition.custom_params[ index ];
			}
		}

		// Render automatically?
		$$.ConvertToRichText
		(
			this,
			options
		);
	},
	onHide:function(){
		$$.RichTextOnHide( this );
		this.hidden = true;
	},
	onShow:function(){
		$$.RichTextOnShow( this );
		this.hidden = false;
	}
});
/**
 * Select element
 */
var in4mlFieldSelect = in4mlField.extend({
	AddOption:function( value, text ){
console.log(1);
	  var option = $$.Create( 'option', { value: value, text:text } );
console.log(2);
	  $$.Append( this.element, option );
	},
	SetOptions:function( options ){
	  this.ClearOptions();
	  for( var i = 0; i < options.length; i++ ){
		this.AddOption( options[ i ].value, options[ i ].text );
	  }
	},
	ClearOptions:function(){
	  $$.Empty( this.element );
	}
});
/**
 * Datepicker element
 */
var in4mlFieldDate = in4mlField.extend({
	init:function( form, definition ){
		this._super( form, definition );
		$( $$.Bind( this.AfterInit, this, [form,definition], true ) );
	},
	AfterInit:function( form,definition ){
		var options =
		{
			'format': definition.format,

			'min_date': new Date( definition.min_date.year, definition.min_date.month - 1, definition.min_date.day ),
			'max_date': new Date( definition.max_date.year, definition.max_date.month - 1, definition.max_date.day ),
			'year_select': (definition.custom_params.year_select|false),
			'month_select': (definition.custom_params.month_select|false),
			'change': $$.Bind
			(
				this.onUpdate,
				this
			),
			'create': $$.Bind
			(
				this.onUpdate,
				this
			),
			'close': $$.Bind
			(
				this.onUpdate,
				this
			),
			first_day: definition.custom_params.first_day
		};

		// Set default date
		if( definition[ 'default' ] ){
			options.default_date = new Date( definition[ 'default' ].year, definition[ 'default' ].month-1, definition[ 'default' ].day );
		}
		if( definition.value ){
			options.value = new Date( definition.value.year, definition.value.month-1, definition.value.day );
		}
		for( var index in definition.custom_params ){
			options[ index ] = definition.custom_params[ index ];
		}

		// Create hidden fields to store
		this.hidden_element_day = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[day]'
			}
		);
		this.hidden_element_month = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[month]'
			}
		);
		this.hidden_element_year = $$.Create
		(
			'input',
			{
				'type':'hidden',
				'name': this.name + '[year]'
			}
		);
		$$.Append( this.container,[ this.hidden_element_day, this.hidden_element_month, this.hidden_element_year ] );

		this.element = $$.ConvertToDatePicker
		(
			this.element,
			options
		);
	},
	/**
	 * Called when field is updated. Updates hidden field values
	 *
	 * @param		Date		date		A JavaScript date object
	 */
	onUpdate:function( date ){
		if( date ){
			$$.SetValue( this.hidden_element_day, date.getDate() );
			$$.SetValue( this.hidden_element_month, date.getMonth() + 1 );
			$$.SetValue( this.hidden_element_year, date.getFullYear() );
		} else {
			$$.SetValue( this.hidden_element_day, 0 );
			$$.SetValue( this.hidden_element_month, 0 );
			$$.SetValue( this.hidden_element_year, 0 );
		}
		$$.Trigger( this.element, '_change' );
	},
	GetValue:function( get_as_object ){
		if( get_as_object ){
			if( parseInt( $$.GetValue( this.hidden_element_year ) ) == 0 ){
				output = null;
			} else {
				output = new Date( parseInt( $$.GetValue( this.hidden_element_year ) ), parseInt( $$.GetValue( this.hidden_element_month ) ) - 1, parseInt( $$.GetValue( this.hidden_element_day ) ) );
			}
			} else {
			var output = {
				day:$$.GetValue( this.hidden_element_day ),
				month:$$.GetValue( this.hidden_element_month ),
				year:$$.GetValue( this.hidden_element_year )
			};
		}
		return output;
	},
	SetValue:function( value ){
	  if( $.isPlainObject(value) ){
		var date = new Date();
		date.setTime( 0 );
		date.setFullYear(parseInt( value.year, 10 ));
		date.setMonth(parseInt( value.month, 10 )-1);
		date.setDate(parseInt( value.day, 10 ));
		value = date;
	  }
	  $$.SetDatePickerValue( this.element, value );
	  this.onUpdate( value );
	},
	BindEvent:function( event, func ){
	  // If we rely on the built-in 'change' event, it fires before the value is updated
	  if( event == 'change' ){
		$$.AddEvent
		(
			this.element,
			'_change',
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
	  } else {
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

	if( value ) {
	  // Multiple addresses?
	  if( this.allow_multiple && value.indexOf( ';' ) !== -1 ){
		var emails = value.split( ';' );
		for( var i = 0; i < emails.length; i++ ){
		  var email = $.trim( emails[ i ] );
		  if( this.allow_name ){
			var matches = email.match( /^(.*)<\s*(.+)\s*>\s*$/ );
			if( matches !== null ){
			  var email = $.trim( matches[2] );
			}
		  }
		  if( email.length > 0 ){
			if( !in4mlUtilities.CheckRegexp( email, this.pattern, ['i'] ) ){
			  field.SetError( in4ml.GetErrorText( 'email', {email:email} ), this.error_message );
			  output = false;
			}
		  }
		}
	  } else {
		  if( this.allow_name ){
			var matches = value.match( /^(.*)<\s*(.+)\s*>\s*$/ );
			if( matches !== null ){
			  var value = $.trim( matches[2] );
			}
		  }
		if( !in4mlUtilities.CheckRegexp( value, this.pattern, ['i'] ) ){
		  field.SetError( in4ml.GetErrorText( 'email', {email:value} ), this.error_message );
		  output = false;
		}
	  }
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
		var result = in4mlUtilities.CheckRegexp( value, this.pattern, modifiers );
		if ( this.match == result ){
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
	if(
	  field.GetValue() == null
	  ||
	  field.GetValue() == ''
	  ||
	  typeof field.GetValue() == 'undefined'
	  ||
	  ( field.type == 'Date' && field.GetValue().year == 0 )
	  ||
	  ( field.type == 'CheckboxMultiple' && field.GetValue().length == 0 )
	)
	{
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
		if( !in4mlUtilities.CheckRegexp( value, "^(http|https)://", [ 'i' ]  ) ){
			field.SetError( in4ml.GetErrorText( 'url:protocol', null, this.error_message ) );
			output = false;
		// Check well-formedness
		} else if( !in4mlUtilities.CheckRegexp( value, "^(http|https)://([\dA-Z0-9-]+\.)+[a-zA-z0-9]{1,3}", [ 'i' ] ) ) {
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
  if (typeof jQuery.prop == 'undefined' ) {
	 return jQuery( element ).attr( attribute );
  }
  switch (attribute) {
	case 'checked':
	case 'selected':
	case 'disabled':
	{
	  return jQuery( element ).prop( attribute );
	}
	default:{
	  return jQuery( element ).attr( attribute );
	}
  }
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
 * Check if an element has a class
 *
 * @param		HTMLElement		element
 * @param		string			css_class
 */
JSLibInterface_jQuery.prototype.HasClass = function( element, css_class ){
	return jQuery( element ).hasClass( css_class );
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
					element.text
					(
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

	if( arguments.length > 2 ){
		$( element ).trigger( event, $( arguments ).slice( 2 ) );
	} else {
		$( element ).trigger( event );

	}

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
			value = ( typeof $$.GetAttribute( element, 'checked' ) != 'undefined' &&  $$.GetAttribute( element, 'checked' ) != false );
			break;
		}
		case 'radio':{
			for( var i = 0; i < element.length; i++ ){
				if( jQuery( element[ i ] ).is(':checked') ){
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
	switch( $( element )[0].nodeName.toLowerCase() ){
		case 'select':{
		  $( element ).val( value ).trigger( 'change' );
		}
		default:{
		  $( element ).val( value );
		  break;
		}
	}
}
/**
 * Simple JS alternative to htmlentities()
 *
 * @param		string		str
 *
 * @return		string
 */
JSLibInterface_jQuery.prototype.Escape = function( str ){
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
/**
 * Call a function when document is ready
 *
 * @param		function		callback
 */
JSLibInterface_jQuery.prototype.Ready = function( callback ){
  // If document is ready to go, run it
  if( document.readyState != 'loading'){
	callback();
  } else{
	jQuery( document ).bind
	(
	  'ready',
	  function( event ){
		jQuery(document).unbind( 'ready', event );
		callback();
	  }
	);
  }

}
/**
 * Convert a textarea element to a rich text field
 *
 * @param		HTMLElement		element
 * @param		JSON			options
 */
JSLibInterface_jQuery.prototype.ConvertToRichText = function( field, options ){
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
		theme_advanced_resizing : true,
		relative_urls: false,
		setup : $$.Bind
		(
			function(ed)
			{
			  ed.onInit.add(
				$$.Bind
				(
				  function(ed, cm)
				  {
					if( !field.ready ){
					  field.ready = true;
					  field.form.FieldReady();
					}
				  },
				  this
				)
			  )
			},
			this
		)
	};

	field.settings = $.extend(true, defaults, options)

	if( field.form.auto_render == true ){
		this._EnableRichText( field );
	} else {
		field.ready = true;
		field.form.FieldReady();
	}
}
/**
 * TinyMCE breaks if it's inside an element that's hidden so call this before hiding
 */
JSLibInterface_jQuery.prototype.RichTextOnHide = function( field ){
	if( typeof tinyMCE != 'undefined'){
	  tinyMCE.triggerSave();
	  var id = $( field.element ).get(0).id;
	  tinyMCE.execCommand("mceRemoveControl", false, id );
	}
}
/**
 * Restores a tinyMCE field that's been hidden
 */
JSLibInterface_jQuery.prototype.RichTextOnShow = function( field ){
  if( $( field.element ).tinymce != 'undefined'){
	this._EnableRichText( field );
  }
}
/**
 * Initiates a rich text editor instance. Should not be called directly.
 */
JSLibInterface_jQuery.prototype._EnableRichText = function( field ){
 $( field.element ).tinymce( field.settings );
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
				'type':'text',
				'id':element.attr( 'id' ),
				'class': 'date text'
			}
		)
	);

	element.replaceWith( new_element );

	var settings =
	{
		'dateFormat': options.format,
		'changeYear': options.year_select,
		'changeMonth': options.month_select,
		'yearRange': options.min_date.getFullYear() + ':' + options.max_date.getFullYear(),
		'minDate': options.min_date,
		'maxDate': options.max_date,
		'firstDay': options.first_day,
		'showOn': 'both',
		'buttonImage': ((options.icon_image)?options.icon_image:in4ml.resources_path + 'img/calendar_icon.png')
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
			case 'close':{
			  settings.onClose = this.Bind(
				function( date_string, date_picker, callback ){
				  if( date_string == '' ){
    				  callback();
				  }
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
	if( options.value ){
		new_element.datepicker( 'setDate', options.value );
	}else if( options.default_date ){
		new_element.datepicker( 'setDate', options.default_date );
	}

	$( new_element ).blur(
	  $$.Bind(
		function(event){
		  var value = $$.GetValue( event.target ).trim().match( /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/ );
		  if( value != null ){
			// Check date
			var date = new Date();
			date.setDate( parseInt( value[ 1 ], 10 ) );
			date.setMonth( ( parseInt( value[ 2 ], 10 ) - 1 ).toString() );
			date.setFullYear( parseInt( value[ 3 ], 10 ) );

			if ( isNaN( date.getTime() ) ) {
			  // Invalid date
			} else {
			  $$.SetValue( $$.Find( 'input[type=hidden][name="' + $$.GetAttribute( element, 'name' ) + '[day]"]' ),  parseInt( value[ 1 ], 10 ) );
			  $$.SetValue( $$.Find( 'input[type=hidden][name="' + $$.GetAttribute( element, 'name' ) + '[month]"]' ),  parseInt( value[ 2 ], 10 ) );
			  $$.SetValue( $$.Find( 'input[type=hidden][name="' + $$.GetAttribute( element, 'name' ) + '[year]"]' ),  parseInt( value[ 3 ], 10 ) );
			}
		  }
		},
		element
	  )
	);
	if( options.create ){
		options.create( new_element.datepicker( 'getDate' ) );
	}

	return new_element;
}
JSLibInterface_jQuery.prototype.SetDatePickerValue = function( element, value ){
	$( element ).datepicker( 'setDate', value );
}
/**
 * Convert a textarea element to a rich text field
 *
 * @param		HTMLElement		element
 * @param		JSON			options
 */
JSLibInterface_jQuery.prototype.ConvertToAdvancedFile = function( field, options ){
	var upload_code_element = $$.Create
		(
			'input',
			{
				'type': 'hidden',
				'name': '_' + field.name + '_uploadcodes'
			}
		);
	field.SetUploadCodeElement( upload_code_element );
	// Add hidden field to store uplod IDs
	$( field.element ).after(
		upload_code_element
	);
	this.options = options;

	if( field.form.auto_render == true ){
		this._EnableAdvancedFile( field );
	}

}
/**
 * Restores an advanced file element that's been hidden
 */
JSLibInterface_jQuery.prototype.AdvancedFileOnShow = function( field ){
	this._EnableAdvancedFile( field );
}
/**
 * Replaces an advanced file element with a 'clean' one that hasn't been uploadified
 */
JSLibInterface_jQuery.prototype.AdvancedFileOnHide = function( field ){
	this.FileClearQueue( field );
	var new_element = field.element[0].cloneNode(true);
	var old_element = field.element;
	field.element = $( new_element );
	jQuery( old_element ).replaceWith( new_element );
	jQuery( new_element ).parent().find( 'object' ).remove();
}
JSLibInterface_jQuery.prototype._EnableAdvancedFile = function( field ){
	 jQuery(field.element).uploadify({
		'uploader'  : in4ml.resources_path + 'js/lib/uploadify/uploadify.swf',
		'script'    : in4ml.resources_path + 'php/upload.php',
		'cancelImg' : in4ml.resources_path + 'js/lib/uploadify/cancel.png',
		'folder'    : in4ml.resources_path + 'js/lib/uploads',
		'auto'      : false,
		'fileDataName' : 'file',
		'removeCompleted' : false,
		'onComplete': $$.Bind(
			function( event, code, file_object, response, data ){
				this.RemoveFile();
				this.AddUploadedFileCode( response );
				$( this.element ).uploadifyCancel( code );
				return true;
			},
			field
		),
		'onError': $$.Bind(
			function( event, code, file_object, response, data ){
				//this.RemoveFile();
				//this.AddUploadedFileCode( response );
				//$( this.element ).uploadifyCancel( code );
				return true;
			},
			field
		),
		'onCancel': $$.Bind(
			function (event, code, file_object, response) {
				this.SetFilesCount( response.fileCount );
				return true;
			},
			field
		),
		'onClearQueue': $$.Bind(
			function ( event, file_object ) {
				this.SetFilesCount( file_object.fileCount );
				return true;
			},
			field
		)
		,
		'onSelectOnce': $$.Bind(
			function ( event, code ) {
				this.SetFilesCount( code.fileCount );
				return true;
			},
			field
		),
		'onAllComplete': $$.Bind(
			function () {
				this.onUploadsComplete( field );
				return true;
			},
			field
		),
		'multi': false
	  });
}
/**
 * Upload files
 */
JSLibInterface_jQuery.prototype.FileUpload = function( field ){
	// Check if any files to upload
	if( field.GetFilesCount() > 0 ){
		$( field.element ).uploadifyUpload();
	} else {
		field.onUploadsComplete( field );
	}
}
/**
 * Upload files
 */
JSLibInterface_jQuery.prototype.FileClearQueue = function( field ){
	if( field.files_count > 0 ){
		$( field.element ).uploadifyClearQueue();
	}
}
/**
 * Restores an advanced file element that's been hidden
 */
JSLibInterface_jQuery.prototype.AdvancedFileReset = function( field ){
	$( field.upload_code_element ).val( '' );
	$$.FileClearQueue( field );
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

			switch( in4mlUtilities.GetType(data) ){
				case 'array':{
					// Serialize the form elements
					for( var key = 0; key < data.length; key++ ){
						if( typeof name !== 'undefined' ){
							var fullkey = name + '[' + key + ']';
						} else {
							var fullkey = key;
						}
						if( in4mlUtilities.GetType( data[ key ] ) == 'array' || in4mlUtilities.GetType( data[ key ] ) == 'object' ){
							s.push( jQuery.param( data[ key ], fullkey ) );
						} else {
							if( data[key] === null ){
								data[key] = '';
							}
							if( data[key] === true ){
								data[key] = 1;
							}
							if( data[key] === false ){
								data[key] = 0;
							}
							s.push( fullkey + "=" + encodeURIComponent( jQuery.isFunction(data[ key ]) ? data[ key ] : data[ key ] ) );
						}
					}
					break;
				}
				case 'object':{
					// Serialize the form elements
					for( var key in data ){
						if( typeof name !== 'undefined' ){
							fullkey = name + '[' + key + ']';
						} else {
							fullkey = key;
						}
						if( in4mlUtilities.GetType( data[ key ] ) == 'array' || in4mlUtilities.GetType( data[ key ] ) == 'object' ){
							s.push( jQuery.param( data[ key ], fullkey ) );
						} else {
							if( data[key] === null ){
								data[key] = '';
							}
							if( data[key] === true ){
								data[key] = 1;
							}
							if( data[key] === false ){
								data[key] = 0;
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
					if( data === true ){
						data = 1;
					}
					if( data === false ){
						data = 0;
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
in4mlUtilities = {
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

(function(c){var b,e,a=[],d=window;c.fn.tinymce=function(j){var p=this,g,k,h,m,i,l="",n="";if(!p.length){return p}if(!j){return tinyMCE.get(p[0].id)}p.css("visibility","hidden");function o(){var r=[],q=0;if(f){f();f=null}p.each(function(t,u){var s,w=u.id,v=j.oninit;if(!w){u.id=w=tinymce.DOM.uniqueId()}s=new tinymce.Editor(w,j);r.push(s);s.onInit.add(function(){var x,y=v;p.css("visibility","");if(v){if(++q==r.length){if(tinymce.is(y,"string")){x=(y.indexOf(".")===-1)?null:tinymce.resolve(y.replace(/\.\w+$/,""));y=tinymce.resolve(y)}y.apply(x||tinymce,r)}}})});c.each(r,function(t,s){s.render()})}if(!d.tinymce&&!e&&(g=j.script_url)){e=1;h=g.substring(0,g.lastIndexOf("/"));if(/_(src|dev)\.js/g.test(g)){n="_src"}m=g.lastIndexOf("?");if(m!=-1){l=g.substring(m+1)}d.tinyMCEPreInit=d.tinyMCEPreInit||{base:h,suffix:n,query:l};if(g.indexOf("gzip")!=-1){i=j.language||"en";g=g+(/\?/.test(g)?"&":"?")+"js=true&core=true&suffix="+escape(n)+"&themes="+escape(j.theme)+"&plugins="+escape(j.plugins)+"&languages="+i;if(!d.tinyMCE_GZ){tinyMCE_GZ={start:function(){tinymce.suffix=n;function q(r){tinymce.ScriptLoader.markDone(tinyMCE.baseURI.toAbsolute(r))}q("langs/"+i+".js");q("themes/"+j.theme+"/editor_template"+n+".js");q("themes/"+j.theme+"/langs/"+i+".js");c.each(j.plugins.split(","),function(s,r){if(r){q("plugins/"+r+"/editor_plugin"+n+".js");q("plugins/"+r+"/langs/"+i+".js")}})},end:function(){}}}}c.ajax({type:"GET",url:g,dataType:"script",cache:true,success:function(){tinymce.dom.Event.domLoaded=1;e=2;if(j.script_loaded){j.script_loaded()}o();c.each(a,function(q,r){r()})}})}else{if(e===1){a.push(o)}else{o()}}return p};c.extend(c.expr[":"],{tinymce:function(g){return !!(g.id&&"tinyMCE" in window&&tinyMCE.get(g.id))}});function f(){function i(l){if(l==="remove"){this.each(function(n,o){var m=h(o);if(m){m.remove()}})}this.find("span.mceEditor,div.mceEditor").each(function(n,o){var m=tinyMCE.get(o.id.replace(/_parent$/,""));if(m){m.remove()}})}function k(n){var m=this,l;if(n!==b){i.call(m);m.each(function(p,q){var o;if(o=tinyMCE.get(q.id)){o.setContent(n)}})}else{if(m.length>0){if(l=tinyMCE.get(m[0].id)){return l.getContent()}}}}function h(m){var l=null;(m)&&(m.id)&&(d.tinymce)&&(l=tinyMCE.get(m.id));return l}function g(l){return !!((l)&&(l.length)&&(d.tinymce)&&(l.is(":tinymce")))}var j={};c.each(["text","html","val"],function(n,l){var o=j[l]=c.fn[l],m=(l==="text");c.fn[l]=function(s){var p=this;if(!g(p)){return o.apply(p,arguments)}if(s!==b){k.call(p.filter(":tinymce"),s);o.apply(p.not(":tinymce"),arguments);return p}else{var r="";var q=arguments;(m?p:p.eq(0)).each(function(u,v){var t=h(v);r+=t?(m?t.getContent().replace(/<(?:"[^"]*"|'[^']*'|[^'">])*>/g,""):t.getContent({save:true})):o.apply(c(v),q)});return r}}});c.each(["append","prepend"],function(n,m){var o=j[m]=c.fn[m],l=(m==="prepend");c.fn[m]=function(q){var p=this;if(!g(p)){return o.apply(p,arguments)}if(q!==b){p.filter(":tinymce").each(function(s,t){var r=h(t);r&&r.setContent(l?q+r.getContent():r.getContent()+q)});o.apply(p.not(":tinymce"),arguments);return p}}});c.each(["remove","replaceWith","replaceAll","empty"],function(m,l){var n=j[l]=c.fn[l];c.fn[l]=function(){i.call(this,l);return n.apply(this,arguments)}});j.attr=c.fn.attr;c.fn.attr=function(o,q){var m=this,n=arguments;if((!o)||(o!=="value")||(!g(m))){if(q!==b){return j.attr.apply(m,n)}else{return j.attr.apply(m,n)}}if(q!==b){k.call(m.filter(":tinymce"),q);j.attr.apply(m.not(":tinymce"),n);return m}else{var p=m[0],l=h(p);return l?l.getContent({save:true}):j.attr.apply(c(p),n)}}}})(jQuery);
