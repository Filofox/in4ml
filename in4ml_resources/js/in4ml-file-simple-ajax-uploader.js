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
	var upload_button = $$.Create
		(
			'div',
			{
				'text': 'Select file',
				'class': '',
				
			}
		);
	var container = $( '<div class="file-upload-advanced"><a class="file-select-button" id="in4ml_' + field.name + '_button">Select file</a><div class="items_container"></div><div class="progress-outer"></div></div>' );
	field.SetUploadCodeElement( upload_code_element );
	// Add hidden field to store uplod IDs
	$( field.element ).after(
		upload_code_element
	);
	$( field.element ).after(
		container
	);
	this.options = options;
	field.advanced_container = $( container );

	field.allowed_extensions = [];
	field.maxsize = false;
	field.maxfiles = false;

	if( field.form.auto_render == true ){
		this._EnableAdvancedFile( field );
	}

	$( field.element ).hide();
}
JSLibInterface_jQuery.prototype._UpdateAdvancedFilesList = function( field ){
	var items = field.uploader.GetQueue();
	var items_html = '';
	if (items.length == 0) {
		var items_html = '<p>No file(s) selected</p>';
	} else {
		for ( var i = 0; i < items.length; i++ ) {
			var item = items[ i ];
			items_html += '<li><span class="filename">' + item.name + '</span><a class="button remove">Remove</a></li>';
		}
		items_html = '<ul class="items">' + items_html + '</ul>';
	}
	field.advanced_container.find( '.items_container' ).html( items_html );
	field.advanced_container.find( '.items_container ul.items a.button.remove' ).click(
		$.proxy(
			function ( event ) {
				this.uploader.RemoveByIndex( $( event.target ).closest( 'li' ).index() );
				$$._UpdateAdvancedFilesList( this );
			},
			field
		)
	);
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
	if ( field.uploader ) {
		field.uploader.destroy();
	}
}
JSLibInterface_jQuery.prototype._EnableAdvancedFile = function( field ){
	var params = {
		  button: $(field.form.element).find( '#in4ml_' + field.name + '_button' ), // HTML element used as upload button
		  url: in4ml.resources_path + 'php/upload.php?uploader=simple-ajax-uploader', // URL of server-side upload handler
		  name: 'uploadfile', // Parameter name of the uploaded file
		  progressUrl: in4ml.resources_path + 'js/lib/simple-ajax-uploader/extras/uploadProgress.php',
		  autoSubmit:false,
		  onChange:
			$.proxy(
				function ( filename, extension, button ) {
					setTimeout(
						$.proxy(
							function () {
								this.form.ClearErrors();
								var queue = this.uploader.GetQueue();

								// Check max files number
								var valid = true;
								if ( this.maxfiles !== false && queue.length > this.maxfiles ) {
									this.uploader.removeCurrent();
									this.SetError( in4ml.GetErrorText( 'file:count', {max:this.maxfiles,count:queue.length} ) );
									valid = false;
								} else {
									if ( this.maxsize ) {
										for( var i = 0; i < queue.length; i++ ){
											if( queue[ i ].size > this.maxsize ){
												this.SetError( in4ml.GetErrorText( 'file:max_size', {filename:filename,filesize:(queue[i].size/1024).toFixed(1) + 'MB',max:this.maxsize_text} ) );
												this.uploader.removeCurrent();
												valid = false;
											}
										}
									}
	
									if ( this.allowed_extensions.length > 0) {
										// Check allowed extensions
										if ( $.inArray( extension, this.allowed_extensions ) === -1 ) {
											this.uploader.removeCurrent();
											this.SetError( in4ml.GetErrorText( 'file:type', {filename:filename,filetype:'.' + extension,validtypes:this.allowed_extensions.join( ', ' )} ) );
											valid = false;
										}
									}
								}
								if (!valid) {
									this.ShowErrors();
								}

								this.SetFilesCount( this.uploader.getQueueSize() );
								$$._UpdateAdvancedFilesList( this );
								return true;
							},
							this
						),
						0
					)
					return true;
				},
				field
			),
			onComplete: $.proxy(
				function ( filename, response, button ) {
					this.AddUploadedFileCode( response );
					this.SetFilesCount( this.uploader.getQueueSize() );
					if (this.uploader.getQueueSize() == 0) {
						this.advanced_container.removeClass( 'in-progress' );
						this.onUploadsComplete( this );
					} else {
						this.uploader.submit();
					}
					$$._UpdateAdvancedFilesList( this );
					return true;
				},
				field
			),
			onError:function(){
			},
			onSizeError:function( filename, extension ){
			},
			onSubmit: $.proxy(
				function( filename, extension, uploadBtn ){
					this.advanced_container.find( '.progress-outer' ).append( '<div class="progress"><div class="bar"></div></div>' );
					this.uploader.setProgressContainer( $( this.advanced_container.find( '.progress' ) ) );
					this.uploader.setProgressBar( $( this.advanced_container.find( '.progress .bar' ) ) );
					return true;
				},
				field
			)
			
	
	};

	field.max_files = false;
	this.allowed_extensions = [];
	for( var i = 0; i < field.validators.length; i++ ){
		var validator = field.validators[ i ];
		switch( validator.type ){
			case 'in4mlValidatorFileCount':{
				field.maxfiles = validator.max;
				break;
			}
			case 'in4mlValidatorFileType':{

				// File groups
				if( validator.enable_groups ){
					var extensions = [];
					for( var index in validator.types ){
						var file_type = validator.types[ index ];
						for( var mime_type in file_type ){
							extensions.push( file_type[mime_type] );
						}
					}
					field.allowed_extensions = params.allowedExtensions = extensions;
				} else {

				}
				break;
			}
			case 'in4mlValidatorFileMaxSize': {

				switch( validator.units ){
					case 'KB':{
						var max_size_kilobytes = validator.size;
						break;
					}
					case 'MB':{
						var max_size_kilobytes = validator.size * 1024;
						break;
					}
					case 'GB':{
						var max_size_kilobytes = validator.size * Math.pow( 1024, 2 );
						break;
					}
					default:{
						throw 'Unknown file size unit ' . validator.units;
					}
				}

				field.maxsize = params.maxSize = max_size_kilobytes;
				field.maxsize_text = validator.size + validator.units;
				break;
			}
		}
	}

	if( field.max_files === 1 ){
		params.multiple = false;
	} else {
		params.multiple = true;
	}

	field.uploader = new ss.SimpleUpload( params );
	field.uploader.GetQueue = function(){
		return this._queue;
	}
	field.uploader.RemoveByIndex = function( index ){
		var queue = this.GetQueue();
		var items = [];
		for( var i = 0; i < queue.length; i++ ){
			if ( i != index ) {
				items.push( queue[ i ] );
			}
		}
		this._queue = items;
	}

	$$._UpdateAdvancedFilesList( field );
	return;
}
/**
 * Upload files
 */
JSLibInterface_jQuery.prototype.FileUpload = function( field ){
	field.advanced_container.addClass( 'in-progress' );
	field.uploader.submit();
}
/**
 * Clear queue
 */
JSLibInterface_jQuery.prototype.FileClearQueue = function( field ){
	if( field.uploader != null ){
		field.uploader.clearQueue();
	}
}
