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
	if( field.uploader != null ){
		var copy = field.old_element_copy.cloneNode(true);
		jQuery( field.uploader_element ).replaceWith( copy );
		field.element = jQuery( copy ).find( 'input[type=file]' );
		field.container = copy;
		field.uploader.destroy();
		field.uploader = null;
	}
}
JSLibInterface_jQuery.prototype._EnableAdvancedFile = function( field ){
	var params = {
		// General settings
		runtimes : 'gears,flash,silverlight,browserplus,html5',
		url : in4ml.resources_path + 'php/upload.php',
		max_file_size : '1Gb',
		unique_names : true,

		// Flash settings
		flash_swf_url : in4ml.resources_path + 'js/lib/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : in4ml.resources_path + 'js/lib/plupload/js/plupload.silverlight.xap',

		preinit:{
			PostInit: function(up) {
				// Hide start button
                $(".plupload_start").hide();
            }
		},
		init:{
			FilesAdded:
				$$.Bind
					(
						function(uploader, files) {
							var max_files = false;
							for( var i = 0; i < this.validators.length; i++ ){
								var validator = this.validators[ i ];
								if( validator.type == 'in4mlValidatorFileCount' ){
									max_files = validator.max;
								}
							}
							if ( max_files !== false && uploader.files.length > max_files ) {
								alert( 'You may not upload more than ' + max_files + ' file' + ((max_files!=1)?'s':'') + ' at a time.' );
								uploader.splice( max_files );
							}
						},
						field
					),
			QueueChanged:
				$$.Bind
				(
					function( uploader ){
						this.SetFilesCount( uploader.files.length );
						return true;
					},
					field
			),
			FileUploaded:
				$$.Bind
				(
					function( uploader, file, response ){
						this.AddUploadedFileCode( response.response );
						this.SetFilesCount( this.GetFilesCount() - 1 );
						return true;
					},
					field
			),
			UploadComplete:$$.Bind(
				function ( uploader ) {
					this.onUploadsComplete( field );
					return true;
				},
				field
			)
		}
	};

	var max_files = false;
	for( var i = 0; i < field.validators.length; i++ ){
		var validator = field.validators[ i ];
		switch( validator.type ){
			case 'in4mlValidatorFileCount':{
				max_files = validator.max;
				break;
			}
			case 'in4mlValidatorFileType':{
				// File groups
				var filters = [];
				if( validator.enable_groups ){
					for( var index in validator.types ){
						var extensions = [];
						var file_type = validator.types[ index ];
						for( var mime_type in file_type ){
							extensions.push( file_type[mime_type] );
						}
						var filter = {
							title:index,
							extensions:extensions.join( ',' )
						};
						filters.push( filter );
					}
					params.filters = filters;
				} else {

				}
				break;
			}
		}
	}

	if( max_files == 1 ){
		params.multi_selection = false;
	}
	params.init.Init =
		$$.Bind
			(
				function( uploader, runtime, max_files ){
					this.uploader = uploader;
					$( 'div.plupload_header_title').text( "Select file" + ((max_files != 1)?'s':'') );
					$( 'div.plupload_header_text').text( "Use the 'Add files' buttons below to choose " + ((max_files != 1)?'files':'a file') + " to upload." );
					return true;
				},
				field,
				[ max_files ]
			);

	field.old_element_copy = jQuery( field.element ).parent()[0].cloneNode(true);
	field.uploader_element = jQuery( field.element ).parent().pluploadQueue( params );
}
/**
 * Upload files
 */
JSLibInterface_jQuery.prototype.FileUpload = function( field ){
	field.uploader.start();
}
/**
 * Clear queue
 */
JSLibInterface_jQuery.prototype.FileClearQueue = function( field ){
	if( field.uploader != null ){
		field.uploader.splice();
		field.uploader.refresh();
	}
}
