var ImageUploader =
{
	'uploaders':{},
	'imageExtensions':null, // set serverside
	'thumbnailWidth':null, // set serverside
	'thumbnailHeight':null, // set serverside
	'emptyImageURL':null, // set serverside
		
	Add:function(baseID)
	{
		application.log('Image uploader ['+baseID+']', 'Initializing.', 'event');
		
		// create the upload handler
		var uploader = new plupload.Uploader({
			'browse_button':baseID+'_browse',
			'url':application.url+'/storage/upload.php',
			'multi_selection':false,
			'filters':{
				'mime_types':[{'title':'Image files', 'extensions':this.imageExtensions.join(',')}]
			}
		});

		// initialize the handler, this needs to be done before the event handlers
		uploader.init();
		
		// create the object that will handle all upload events
		// for this uploader instance, as well as all UI actions.
		var handler = 
		{
			'uploader':uploader,
			'id':baseID,
			'file':null,
			'uploadResult':null,
			
			Init:function()
			{
				var handlerObj = this;
				uploader.bind('FilesAdded', function(up, files) {
					handlerObj.Handle_FilesAdded(files);
				});
				
				uploader.bind('UploadProgress', function(up, file) {
					handlerObj.Handle_UploadProgress(file);
				});
				
				uploader.bind('UploadComplete', function() {
					handlerObj.Handle_UploadComplete();
				});
				
				uploader.bind('FileUploaded', function(up, file, response) {
					handlerObj.Handle_FileUploaded(response.response);
				});
				
				uploader.bind('Error', function(up, error) {
					handlerObj.Handle_Error(error);
				});

				this.Element('delete').on('click', function() {
					handlerObj.Handle_Delete();
				});
				
				this.Element('browse2').on('click', function() {
					handlerObj.Element('browse').click();
				});
				
				UI.MakeTooltip(this.Element('delete'));
				UI.MakeTooltip(this.Element('browse'));
				
				this.Element('thumbnail').on('click', function() {
					handlerObj.Handle_ClickThumbnail();
				});
				
				this.log('Uploader initialized.', 'event');
			},
			
			/**
			 * Gets a DOM element for this instance.
			 * @param elName
			 * @returns DOMNode
			 */
			Element:function(elName)
			{
				return $('#'+this.id+'_'+elName);
			},
			
			/**
			 * Handles files being added.
			 * @param files
			 */
			Handle_FilesAdded:function(files)
			{
				this.Element('message').html('');
				this.Element('browse').html(UI.Icon().Spinner()+'');
				
				var obj = this;
				plupload.each(files, function(file) {
					display = t('File: %1$s, %2$s', file.name, plupload.formatSize(file.size));
					obj.Element('file').html(display);
					obj.file = file;
				});
				
				this.uploader.start();
			},
			
			Handle_FileUploaded:function(response)
			{
				// parsing as json, since plupload does not interpret json.
				this.uploadResult = JSON.parse(response);
			},
			
		    /**
		     * Called when the user clicks the delete button to clear the image.
		     */
			Handle_Delete:function()
			{
				this.ResetStatusbar();
				
				// in case the field had an error, we need to reset that 
				// if the user clears the image.
				var control = this.Element('name').closest('.control-group');
				control.removeClass('error');
				control.find('.help-inline').hide();
				
				this.Element('state').val('empty');
				this.Element('filetype').hide();
				this.Element('name').val('').change();
				this.Element('id').val('');
				this.Element('thumbnail').attr('src', ImageUploader.emptyImageURL);
				this.Element('thumbnail').removeClass('clickable');
				this.Element('progress').width('0%');
				
				var obj = this;
				this.Element('browse2').on('click', function() {
					obj.Element('browse').click();
				});
			},
			
			ResetStatusbar:function()
			{
				this.DisplayNeutralMessage(
					t('%1$sSelect an image%2$s to upload.','<a href="javascript:void(0);" id="'+this.id+'_browse2">', '</a>'), 
					false
				);
			},
			
			/**
			 * Called while the file is being uploaded.
			 * @param file
			 */
			Handle_UploadProgress:function(file)
			{
				application.preventFormSubmission(t('Image upload in progress.'));
				this.DisplaySuccessMessage(t('Uploading file...')+' '+file.percent+'%');
				this.Element('progress').width(file.percent+'%');
			},
			
			/**
			 * Called when the file has been uploaded successfully.
			 */
			Handle_UploadComplete:function()
			{
				this.log('Upload complete.', 'event');
				
				if(this.uploadResult.status == 'error') 
				{
					this.Element('file').html('');
					this.Unlock();
					this.Handle_Delete();

					application.dialogErrorMessage(
						t('The upload failed.'),
						this.uploadResult.message
					);
					
					this.log('The upload failed. Error message: '+this.uploadResult.message, 'error');
					this.DisplayErrorMessage(t('The upload failed.'), false);
					return;
				}
				
				var uploadID = this.GetUploadID();

				this.log('The upload was successful. Upload ID: ['+uploadID+'].', 'data');
				this.DisplaySuccessMessage(t('The upload was successful.'), false);
				
				this.Unlock();
				this.Element('file').html('');
				this.Element('state').val('new');
				this.Element('id').val(uploadID);
				this.Element('filetype').html(this.GetUploadExtension()).show();
				this.Element('name').val(this.GenerateName()).change();
				this.Element('name').focus();
				this.Element('thumbnail').attr('src', application.url+'/media.php?source=upload&upload_id='+uploadID+'&width='+ImageUploader.thumbnailWidth+'&height='+ImageUploader.thumbnailHeight);
				this.Element('thumbnail').addClass('clickable');
			},
			
			GetUploadID:function()
			{
				return this.uploadResult.upload_id;
			},
			
			GetUploadExtension:function()
			{
				return this.uploadResult.file_type;
			},
			
			/**
			 * Creates the name to insert into the name field after the file has
			 * been uploaded: simply removes the extension from the file.
			 * @returns string
			 */
			GenerateName:function()
			{
				var name = this.file.name;
				
				// remove the extension from the file name
				var tokens = name.split('.');
				tokens.pop();
				name = tokens.join('.');
				
				// replace spaces with dashes
				name = name.split(' ').join('-');
				
				return name;
			},
			
			/**
			 * Called when an error occurrs during the upload.
			 * @param error
			 */
			Handle_Error:function(error)
			{
				this.log('An error occurred in the upload:', 'event');
				this.log(error, 'event');
				
				message = error.message;
				switch(error.code) {
					case -601:
						message = t('Only images may be uploaded.');
						break;
				}
			
				this.Unlock();
				this.DisplayErrorMessage(message);
			},
			
			Handle_ClickThumbnail:function()
			{
				var state = this.Element('state').val();
				if(state=='empty') {
					return;
				}
				
				if(state=='media') {
					url = application.url+'/media.php?source=media&media_id='+this.Element('id').val();
				} else {
					url = application.url+'/media.php?source=upload&upload_id='+this.Element('id').val();
				}
				
				window.open(url);
			},
			
			Unlock:function()
			{
				application.allowFormSubmission();
				this.Element('browse').html(UI.Icon().Upload()+'');
			},
			
			/**
			 * Used to display a status message for this upload field.
			 * 
			 * @param message
			 * @param type
			 * @param autoClear
			 */
			DisplayStatusMessage:function(message, type, autoClear)
			{
				this.ClearStatusMessage();

				clearTimeout(this.StatusMessageTimeout);

				var html = ''+
				UI.Icon()
					.Information()
					.MakeInformation()+' '+
					message;

				if(typeof(type)=='undefined') {
					type = 'warning';
				}

				this.Element('statusbar')
					.html(html)
					.addClass('active')
					.addClass(type);

				if(typeof(autoClear)=='undefined') {
					autoClear = true;
				}
				
				// make the message disappear automatically after a few seconds
				if(autoClear) {
					var obj = this;
					this.StatusMessageTimeout = setTimeout(
						function() {
							obj.ResetStatusbar();
						},
						3000
					);
				}
			},
			
			DisplayNeutralMessage:function(message, autoClear)
			{
				this.DisplayStatusMessage(message, 'neutral', autoClear);
			},

			DisplaySuccessMessage:function(message, autoClear)
			{
				this.DisplayStatusMessage(message, 'success', autoClear);
			},

			DisplayErrorMessage:function(message, autoClear)
			{
				this.DisplayStatusMessage(message, 'warning', autoClear);
			},

			ClearStatusMessage:function()
			{
				this.Element('statusbar')
					.html('')
					.removeClass('active')
					.removeClass('success')
					.removeClass('warning')
					.removeClass('neutral');
			},
			
			log:function(message, category)
			{
				application.log(
					'Image uploader [' + this.id + ']',
					message,
					category
				);
			}
		};
		
		handler.Init();
		this.uploaders[baseID] = handler;
	}
};