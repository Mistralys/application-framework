var LockManager = 
{
	'ERROR_CANNOT_SEND_RELEASE_REQUEST':14401,
		
    'Primary':null, // set serverside
    'UrlPath':null, // set serverside
    'ScreenURL':null, // set serverside
    'CritLevels':null, // set serverside
    'Locked':null, // initially set serverside, updated via ajax
    'LockedBy':null, // initially set serverside, updated via ajax
    'LockedUntil':null, // initially set serverside, updated via ajax
    
   /**
    * The amount of time to wait until the page is automatically unlocked, in seconds
    * @var int autoUnlockDialogDelay The amount of time in seconds.
    */
    'autoUnlockDialogDelay':null, // set serverside
    
   /**
    * The amount of time to wait before we ask the user if he's still active
    * @var int autoUnlockDelay The amount of time in minutes.
    */
    'autoUnlockDelay':null, // set serverside
    
   /**
    * The amount of time in seconds to wait between keep alive requests
    */
    'keepAliveDelay':null, // set serverside
    
   /**
    * The amount of time to wait before we refresh the locking status for a visiting user.
    * @var int refreshStatusDelay The amount of time in seconds.
    */
    'refreshStatusDelay':null, // set serverside
    
    'autoUnlockTimer':null,
    
    'lastActivity':null,
    
    'LabelsCutLength':40,
    
    Start: function () 
    {
    	if(this.Locked) 
    	{
    		this.StartVisitor();
    	} 
    	else 
    	{
    		this.StartOwner();
    	}
    
        $('#locking-active-locks')
        .addClass('clickable')
        .click(function() {
        	LockManager.ToggleActiveLocks();
        });
        
        $('#locking-unlock-requests')
        .addClass('clickable')
        .click(function() {
        	if(LockManager.currentResponse != null && LockManager.currentResponse.HasUnlockRequests()) {
        		LockManager.Owner_DialogUnlockRequests(LockManager.currentResponse.GetUnlockRequests());
        	}
        })
    },
    
    StartVisitor:function()
    {
    	this.Visitor_RefreshStatus();
    },
    
    'isOwner':false,
    
    StartOwner:function()
    {
    	this.isOwner = true;
    	
    	// whenever the user has activity in the document, we reset
    	// the timer for the inactivity dialog.
    	registerCrossbrowserEvent(document, 'mousemove', this.Owner_ResetAutoUnlock);
    	registerCrossbrowserEvent(document, 'keypress', this.Owner_ResetAutoUnlock);
		registerCrossbrowserEvent(document, 'scroll', this.Owner_ResetAutoUnlock);

        this.Owner_ResetAutoUnlock();
        this.Owner_KeepAlive();

        registerCrossbrowserEvent(
        	window,
    		'beforeunload', 
    		function (e) {
        		console.log('Page unload, releasing lock!');
	            LockManager.Owner_ReleaseLock(LockManager.currentResponse.GetCurrentLock(), null, null, true);
	        }
		);
    },
    
    Owner_ResetAutoUnlock:function()
    {
    	LockManager.lastActivity = new Date();
    	
    	clearTimeout(LockManager.autoUnlockTimer);
    	
        LockManager.autoUnlockTimer = setTimeout(
    		function() {
    			LockManager.Owner_DialogUserInactive();
    		}, 
    		LockManager.autoUnlockDelay * 1000
		);
    },
    
    DialogActionDisabled:function()
    {
    	application.createDialogMessage(
			t('The page is locked by %1$s, no changes may be made.', this.LockedBy.Name), 
			t('Page locked')
		)
		.SetIcon(UI.Icon().Locked())
		.Show();
    },

    elSendMessageDialog: null,

    Visitor_SendMessageDialog: function () 
    {
        var items = $('input[type=checkbox]:checked');

        if (!items.length) {
            return;
        }

        if (this.elSendMessageDialog != null) {
            return this.elSendMessageDialog.modal('show');
        }

        this.elSendMessageDialog = DialogHelper.createDialog(
            UI.Icon().Changelog() + ' ' + t('Send message...'),
            '<div class="control-group">' +
            '   <textarea id="text" class="input-xxlarge"></textarea>' +
            '</div>',
            DialogHelper.renderButton_primary(t('Send'), 'LockManager.Visitor_SendMessage()')
        );

        this.elSendMessageDialog.modal('show');

    },

    Visitor_SendMessage: function () 
    {
        var items = $('input[type=checkbox]:checked');
        var text = $('#text');

        if (text.val() != '') {

            $('#btn3').html(UI.Icon().Refresh().Spinner() + ' ' + t('Send'));

            var selected = [];

            for (var x = 0; x < items.length; x += 1) {
                selected.push($(items[x]).val());
            }

            var payload = {
                'key': selected,
                'message': text.val()
            };

            $.ajax({
                'dataType': 'json',
                'url': application.getAjaxURL('SendMessage'),
                'data': payload,
                'success': function (data, textStatus, jqXHR) {
                    LockManager.elSendMessageDialog.modal('hide');
                    items.prop('checked', false);
                    $('#btn3 i').remove();
                    text.val('');
                }
            });

        }
    },

    'autoUnlockDialogTimer':null,
    
    Owner_KeepAlive:function() 
    {
    	if(this.activeLocksInitialized) {
    		var id = $('#locking-active-locks').data('container-id')+'-title-loader';
    		$('#'+id).html(application.renderSpinner());
    	}
    	
        application.createAJAX('LockingKeepAlive')
        .SetPayload(this.GetPayload())
        .DisableLogging()
        .Success(function(data) {
    			LockManager.Owner_KeepAliveSuccess(new LockManager_StatusResponse(data));
    	})
    	.Send();
    },

    'keepAliveTimer':null,
    'currentResponse':null,
    
    Owner_KeepAliveSuccess:function(statusResponse)
    {
    	if(this.activeLocksInitialized) {
    		var id = $('#locking-active-locks').data('container-id')+'-title-loader';
    		$('#'+id).html('');
    	}
    	
    	// this can happen when the user reloads the page, and another user is
    	// already waiting for it to be unlocked: depending on whose request
    	// comes through first, the waiting user may win the challenge.
    	if(!statusResponse.IsLocked()) {
    		application.closeAllDialogs(true);
    		application.refreshPage(UI.Icon().Locked() + ' ' + t('Please wait, another user won the locking challenge.'));
    		return;
    	}
    	
    	this.RefreshFromResponse(statusResponse);
    	
    	$('#locking-editing-time-elapsed').html(
			t('You have been editing for %1$s.', statusResponse.GetTimeElapsed())
		);
    	
    	clearTimeout(this.keepAliveTimer);
    	
        this.keepAliveTimer = setTimeout(
    		function () {
    			LockManager.Owner_KeepAlive();
    		},
    		this.keepAliveDelay * 1000
		);
    },
    
    'currentLock':null,
    
    RefreshFromResponse:function(statusResponse)
    {
		this.log('Refreshing the status from a server response.', 'event');
		
    	this.currentResponse = statusResponse;
    	this.currentLock = statusResponse.GetCurrentLock();

    	if(this.IsOwner() && this.currentLock.IsForcedRelease()) {
    		application.showLoader(t('Please wait, the page has been unlocked manually.'));
    		application.redirect({
    			'page':'settings',
    			'redirect_reason':'lock_released'
    		});
    		return;
    	}
    	
    	if(statusResponse.HasCurrentLock()) {
    		var time = string2datetime(statusResponse.GetCurrentLock().GetLastActivity());
    		var valid = isDate(time);

    		if(!valid) 
    		{
    			this.log('Invalid last activity time returned: ['+statusResponse.GetCurrentLock().GetLastActivity()+'].', 'error');
    		} 
    		else if(this.lastActivity == null || this.lastActivity.toUTCString() != time.toUTCString()) 
    		{
    			this.log('The last activity time has changed, updating.', 'event');
	    		// ensure that we postpone the auto unlock when the 
	    		// last activity is updated by the server.
	    		if(this.IsOwner()) {
	    			this.Owner_ResetAutoUnlock();
	    		}
	    		this.lastActivity = time;
    		}
		}
    	
		// remove all queued extend lock requests that were honored on the server.
		var extended = this.currentResponse.GetExtendedLockIDs();
		
		var keep = {};
		$.each(this.extendLocks, function(idx, lock) {
			// keep only locks to extend that have not already been extended
			if(!in_array(lock.GetID(), extended)) {
				keep[lock.GetID()] = lock;
			}
		});
		
		this.extendLocks = keep;
		
		
		var released = this.currentResponse.GetReleasedLockIDs();
		
		var keep = {};
		$.each(this.releaseLocks, function(idx, lock) {
			// keep only locks to release that have not already been released
			if(!in_array(lock.GetID(), released)) {
				keep[lock.GetID()] = lock;
			}
		});
		
		this.releaseLocks = keep;
		

		this.RefreshTimeToUnlock();
		this.RefreshUnlockRequests();
    	this.RefreshActiveLocks();
    },
    
    IsOwner:function()
    {
    	return this.isOwner;
    },
    
    IsVisitor:function()
    {
    	return !this.isOwner;
    },
    
    RefreshTimeToUnlock:function()
    {
    	var type = 'inactive';
    	var title = '';
    	
    	if(this.IsOwner()) 
    	{
    		title = t('Time until the page is unlocked if you are inactive.');
    		type = 'info';
    	}
    	else
    	{
    		type = 'warning';
    		title = t('Time until the page is unlocked if the locking user is inactive.');
    		
        	if(this.currentLock.IsReleased()) {
    	    	type = 'success';
        	} 
    	}
    	
    	$('#locking-time-to-unlock').html(application.renderLabelOrBadge(
			'label',
			type,
			UI.Icon().Countdown() + ' ' + this.currentLock.GetTimeToUnlockShort()).Render(),
			title
		);
    },
    
    Owner_DialogUnlockRequests:function(requests)
    {
    	var currentLock = this.currentLock;
    	var entries = {};
    	var body = ''+
    	'<ul class="bigselection">';
	    	$.each(requests, function(idx, request) {
	    		var jsid = nextJSID();
	    		entries[jsid] = request;
	    		
	    		var pageLabel = '';
	    		if(request.GetLockID() == currentLock.GetID()) {
	    			pageLabel = '<i>'+t('Current page')+'</i>';
	    		} else {
	    			pageLabel = request.GetLabel() + ' - ' + request.GetScreenName();
	    		}
	    		
	    		body += ''+
	    		'<li id="'+jsid+'">'+
	    			'<b>' + request.GetFromUserName() + '</b> - ' + t('%1$s ago', request.GetAgePretty()) + '<br>' +
	    			t('Page:') +  ' ' + pageLabel +'<br/>'+
	    			t('Comments:') + ' <i>' + request.GetMessage() + '</i>'+
    			'</li>';
	    	});
	    	body += ''+
    	'</ul>'+
    	'<hr/>'+
    	'<p class="text-warning">'+
    		UI.Icon().Warning() + ' ' + t('Warning:') + ' ' + 
    		t('Make sure that you have saved all your changes before you unlock a page.') +
    	'</p>';
    	
    	var dialog = application.createGenericDialog(t('Unlock requests'), body);
    	
    	dialog
    	.SetIcon(UI.Icon().Unlocked())
    	.SetAbstract(
			t('The following users have requested to edit this page.') + ' ' + 
			t('While you remain on this page, they will not be able to do their edits.') + ' ' +
			'<b>' + t('Click on a user entry to grant them the lock on the page.') + '</b>' 
		)
    	.AddButtonRight(
			UI.Button(t('Continue editing'))
			.SetIcon(UI.Icon().Locked())
			.MakeSuccess()
			.Click(function() {
				dialog.Hide();
			})
    	)
    	.Shown(function() {
    		$.each(entries, function(jsid, request) {
    			$('#'+jsid).click(function() {
    				dialog.Hide();
    				LockManager.Owner_ConfirmUnlock(request);
    			});
    		});
    	})
    	.Show();
    },
    
   /**
    * Called when the owner of a lock clicked a user to relinquish his lock to.
    * @param {LockManager_UnlockRequest} request
    */
    Owner_ConfirmUnlock:function(request)
    {
    	var current = false;
    	if(request.GetLockID() == this.currentResponse.GetCurrentLockID()) {
    		current = true;
    	}
    	
		// avoid additional UI refreshs if we're to release the lock on the current page.
    	if(current) {
    		clearTimeout(this.keepAliveTimer);
    	}
    	
		application.showLoader(UI.Icon().Locked() + ' ' + t('Please wait, unlocking the page...'));
		
    	this.Owner_ReleaseLock(
			request.GetLock(),
			function() {
				if(current) {
					LockManager.Owner_LockReleased(true);
				}
			},
			request.GetFromUserID()
		);
    },
    
    Owner_LockReleased:function(reload)
    {
    	var loaderText = UI.Icon().Unlocked() + ' ' + t('The lock has been removed, refreshing...');
    	
    	if(reload==true) {
    		application.refreshPage(loaderText);	
    	} else {
    		application.redirect({
    			'page':'settings',
    			'redirect_reason':'lock_expired'
    		}, loaderText);
    	}
    },
    
    Owner_DialogUserInactive:function()
    {
    	// stop keeping the lock alive
        clearTimeout(this.keepAliveTimer);
        
        application.dialogConfirmation(
            '<p>' + t('Are you still there? If you are not longer present this page will be unlocked.') + ' ' + t('Any changes that have been made but not saved will be lost.') + '</p>' +
            '<p><b>' + t('After %1$s seconds you will be redirected automatically', this.autoUnlockDialogDelay) + '</b></p>',
            function () {
            	LockManager.Owner_UserStillAlive();
            },
            function () {
            	LockManager.Owner_UserStillAlive();
        	}
        );

        // trigger the timer to release the lock after the specified delay
        this.autoUnlockDialogTimer = setTimeout(
    		function() {
    			LockManager.Owner_AutoUnlockDelayExpired();
    		}, 
    		this.autoUnlockDialogDelay * 1000
		);
    },
    
    Owner_UserStillAlive:function()
    {
        window.clearTimeout(this.autoUnlockDialogTimer);
        
        LockManager.Owner_KeepAlive();
    },

    Owner_AutoUnlockDelayExpired:function()
    {
    	application.closeAllDialogs(true);
    	
    	application.showLoader(UI.Icon().Unlock() + ' ' + t('Please wait, releasing your lock on the page...'));
    	
    	this.Owner_ReleaseLock(
			this.currentResponse.GetCurrentLock(),
			function() {
				LockManager.Owner_LockReleased();
			}
		);
    },
    
    GetPayload:function()
    {
    	var payload = {
            'url_path':this.UrlPath,
            'primary':this.Primary,
            'extend_locks':[],
            'release_locks':[]
        };

    	if(this.Locked) {
        	payload['user_id'] = this.LockedBy.ID;
            payload['visitor_id'] = User.id;
            payload['request_unlock'] = this.requestUnlock;
            payload['request_unlock_message'] = this.requestUnlockMessage;
        	
        	if(this.requestUnlock) {
        		this.unlockRequested = true;
        	}
        	
        	this.requestUnlock = false;
        	this.requestUnlockMessage = '';
    	} 
    	else 
    	{
    	    payload['user_id'] = User.id;
            payload['last_activity'] = this.lastActivity.toUTCString();	
    	}

    	// lock owners and visitors alike can have active locks,
    	// and request these to be extended.
    	$.each(this.extendLocks, function(idx, lock) {
    		payload.extend_locks.push(lock.GetID());
    	});
    	
    	$.each(this.releaseLocks, function(idx, lock) {
    		payload.release_locks.push(lock.GetID());
    	});
    	
    	return payload;
    },
    
    'releaseLockSent':false,
    
   /**
    * Sends a request to release the user's lock on the page.
    * If a user ID is specified, the lock will be transferred
    * to this user instead.
    * 
    * @param {Integer} transferToUser
    */
    Owner_ReleaseLock:function(lock, successCallback, transferToUser, unloading) 
    {
    	if(this.releaseLockSent) {
    		return;
    	}
    	
    	this.releaseLockSent = true;
    	
    	var payload = {
			'lock_id':lock.GetID()
    	};
    	
    	if(!isEmpty(transferToUser)) {
    		payload['transfer_to_user'] = transferToUser;
    	}
    	
        ajax = application.createAJAX('LockingReleaseLock')
		.SetPayload(payload)
		.Success(successCallback)
		.Error(t('Could not release the page\'s lock.'), this.ERROR_CANNOT_SEND_RELEASE_REQUEST);
		
        if(unloading && isFirefox()) {
        	this.log('Unload lock, Firefox edition not asynchronous.');
        	ajax.SetAsync(false);
        }
        
        ajax.Send();
    },
    
   /**
    * Sends an AJAX request to get the locking status of the current 
    * page for the current user when the page is locked.
    * 
    * @see Visitor_RefreshStatusSuccess()
    */
    Visitor_RefreshStatus:function()
    {
    	application.createAJAX('LockingGetStatus')
		.SetPayload(this.GetPayload())
		.DisableLogging()
		.Success(function(data) {
				LockManager.Visitor_RefreshStatusSuccess(new LockManager_StatusResponse(data));
		})
		.Send();
    },
    
    'refreshVisitorTimer':null,
    
    Visitor_RefreshStatusSuccess:function(statusResponse)
    {
    	if(!statusResponse.IsLocked()) {
			application.closeAllDialogs(true);
    		application.refreshPage(UI.Icon().Unlocked() + ' ' + t('The page has been unlocked, please wait...'));
    		return;
    	}
    	
    	this.RefreshFromResponse(statusResponse);
    	
    	var lock = statusResponse.GetCurrentLock();
    	
    	if(lock.HasUnlockRequests()) {
	    	var requests = lock.GetUnlockRequests();
	    	var unlockRequest = null;
	    	$.each(requests, function(idx, req){
	    		if(req.GetFromUserID() == User.id) {
	    			unlockRequest = req;
	    			return false;
	    		}
	    	});
	    	
	    	if(unlockRequest) {
	    		$('#locking-unlock-request-link').html(UI.Icon().OK() + ' ' + t('You requested unlocking.'));
	    	}
    	}
    	
    	this.refreshVisitorTimer = setTimeout(
			function() {
				LockManager.Visitor_RefreshStatus();
			},
			this.refreshStatusDelay * 1000
		);
    },
    
    Visitor_DialogRequestUnlock:function()
    {
    	if(!this.Locked) {
    		return;
    	}
    	
    	var dialog = application.createGenericDialog(t('Request a page unlock'), '');
		
    	var form = FormHelper.createForm()
    	.Submit(function() {
    		LockManager.Visitor_ValidateUnlockRequest(dialog);
    	});
    	
    	form.AddTextarea('message', t('Message'))
    	.SetHelpText(t('Optional message to send with the request.'))
    	.MakeWidthXXL();
    	
    	dialog
    	.ChangeBody(form.Render())
    	.SetData('form', form)
		.SetAbstract(t('This will send a message to %1$s, requesting him/her to release the lock on the page.', this.LockedBy.Name))
		.SetIcon(UI.Icon().Unlock())
		.AddButton(
			UI.Button(t('Request unlock'))
			.MakePrimary()
			.SetIcon(UI.Icon().Unlock())
			.Click(function() {
				form.Submit();
			}),
			'confirm'
		)
		.AddButtonCancel()
		.SetIcon(UI.Icon().Unlock())
		.Shown(function() {
			form.FocusElement('message');
		})
		.Show();
    },
    
    Visitor_ValidateUnlockRequest:function(dialog)
    {
    	var form = dialog.GetData('form');
    	if(!form.Validate()) {
    		return;
    	}
    	
    	dialog.Hide();
    	
    	var values = form.GetValues();
    	this.Visitor_RequestUnlock(values.message);
    },
    
    'unlockRequested':false,
    'requestUnlock':null,
    'requestUnlockMessage':null,
    
    Visitor_RequestUnlock:function(message)
    {
    	// the user has already sent an unlock request.
    	if(this.unlockRequested) {
    		return;
    	}
    	
    	this.requestUnlock = true;
    	this.requestUnlockMessage = message;
    	
    	$('#locking-unlock-request-link').html(application.renderSpinner(t('Requesting unlock...')));
    },
    
    log:function(message, category)
    {
    	application.log('Lock manager', message, category);
    },
    
    'activeLocksInitialized':false,
    'activeLockShown':false,
    
    ToggleActiveLocks:function()
    {
    	if(!this.activeLocksInitialized) 
    	{
    		var jsID = nextJSID();
    		$('#locking-active-locks').data('container-id', jsID);
	    	$('#locking-active-locks').popover({
	    		'html':true,
	    		'placement':'bottom',
	    		'trigger':'manual',
	    		'title':t('Your active locks')+'<span class="pull-right" id="'+jsID+'-title-loader"></span>',
	    		'content':'<div id="'+jsID+'"></div>'
	    	});
	    	
	    	this.activeLocksInitialized = true;
    	} 
    	
    	if(this.activeLockShown) {
    		$('#locking-active-locks').popover('hide');
    		this.activeLockShown = false;
    	} else {
    		$('#locking-active-locks').popover('show');
    		this.activeLockShown = true;
    		this.RefreshActiveLocks();
    	}
    },
    
    'activeLocksRendered':false,
    
    GetCritLevelLabelType:function(critLevel, defaultType)
    {
    	if(isEmpty(defaultType)) {
    		defaultType = 'inactive';
    	}
    	
    	switch(critLevel) 
    	{
			case 'critical':
				return 'warning';
				
			case 'warning':
				return 'warning';
				
			case 'attention':
				return defaultType;
				
			case 'minimal':
				return defaultType;
    	}
    	
    	return defaultType;
	},

    RefreshActiveLocks:function()
    {
    	var activeCount = this.currentResponse.CountActiveLocks();
    	var message = '';
    	var type = 'inactive';
    	
		if(activeCount == 1) {
    		message = t('1 active lock.');
    		type = 'info';
    	} else if(activeCount > 1) {
    		message = t('%1$s active locks.', activeCount);
    		type = 'info';
    	} else {
    		message = t('No active locks.');
    	}
		
		type = this.GetCritLevelLabelType(this.currentResponse.GetCritLevel(), type);
		
		$('#locking-active-locks').html(
			application.renderLabelOrBadge(
				'label', 
				type, 
				UI.Icon().Locked() + ' ' + message
			)
			.Render()
		);
    	
    	if(!this.activeLocksInitialized) {
    		return;
    	}
    	
    	if(!this.activeLocksRendered) {
    		this.RenderActiveLocks();
    		UI.RefreshTimeout(function() {LockManager.RefreshActiveLocks();});
    		return;
    	}
    	
    	var container = $('#'+$('#locking-active-locks').data('container-id'));

    	if(this.currentResponse.CountActiveLocks()==0) 
    	{
    		container.find('.empty-results').show();
    		container.find('.locks-list').hide();
    	} 
    	else 
    	{
    		container.find('.empty-results').hide();
    		container.find('.locks-list').show();
    		
    		var html = '';
    		var hasActiveLock = this.currentResponse.HasCurrentLock();
    		var activeLockID = this.currentResponse.GetCurrentLockID();
    		var locks = [];
    		
    		$.each(this.currentResponse.GetActiveLocks(), function(idx, lock) {
    			if(lock.GetID() != activeLockID) {
    				locks.push(lock);
    			}
    		});
    		
    		locks.sort(function(a, b) {
    			if(a.GetLastActivity() > b.GetLastActivity()) {
    				return 1;
    			}
    			
    			return -1;
    		});
    		
    		// add the current lock as first item
    		if(hasActiveLock) {
    			locks.unshift(this.currentResponse.GetCurrentLock());
    		}
    		
    		$.each(locks, function(idx, lock) 
			{
    			var classes = [
	               'lock-entry-'+lock.GetID(),
	               'active-lock',
	               'lock-critlevel-'+lock.GetCritLevel()
               ];
    			
    			var current = false;
    			if(lock.GetID() == activeLockID) {
    				current = true;
    				classes.push('current-lock');
    			}
    			
    			var labelType = LockManager.GetCritLevelLabelType(lock.GetCritLevel(), 'info');
				var extendable = true;
				var icon = UI.Icon().Countdown();
				var unlockIcon = '';
				var deleteButton = '';
				
				if(lock.HasUnlockRequests()) {
					unlockIcon = UI.Icon().Unlock()
					.MakeWarning()
					.Click(function() {
						LockManager.Owner_DialogUnlockRequests(lock.GetUnlockRequests());
					});
				}
				
				if(lock.IsReleased() || current) 
				{
					icon = UI.Icon().NotAvailable();
					extendable = false;
				} 
				
				if(!current && !lock.IsReleased()) {
					deleteButton = application.renderLabelError(UI.Icon().Delete())
					.Click(function() {
						LockManager.Owner_DeleteLockClicked(lock);
					});
				}
    			
				var label = application.renderLabelOrBadge(
					'label', 
					labelType, 
					icon + ' '+
					lock.GetTimeToUnlockShort()
				);

				if(extendable) {
					label.Click(function() {
						LockManager.Owner_ExtendLock(lock);
					});
				}
				
    			html += ''+
    			'<tr class="'+classes.join(' ')+'">'+
	    			'<td style="width:1%;white-space:nowrap;vertical-align:middle;" class="lock-time">'+
	    				label+
					'</td>'+
	    			'<td style="width:1%;vertical-align:middle;" class="lock-releasebtn">'+deleteButton+'</td>'+
					'<td class="lock-name">';
    					if(current) {
    						html += '<i>' + t('Current page') + '</i>';
    					} else {
    						html += ''+
    						LockManager.RenderLabel(lock.GetLabel())+'<br>'+
    						lock.GetScreenName();
    					}
    					html += ''+
	    			'</td>'+
	    			'<td style="width:1%">'+unlockIcon+'</td>'+
    			'</tr>';
    		});

    		container.find('.lock-entries').html(html);
    	}
    },
    
    'releaseLocks':{},
    
    Owner_DeleteLockClicked:function(lock)
    {
    	var lockID = lock.GetID();
    	if(typeof(this.releaseLocks[lockID]) != 'undefined') {
    		return;
    	}
    	
    	this.releaseLocks[lockID] = lock;
    	
    	$('.lock-entry-'+lockID).find('.lock-time').html(application.renderSpinner(t('Releasing...')));
    	$('.lock-entry-'+lockID).find('.lock-releasebtn').html('');
    },
    
    RefreshUnlockRequests:function()
    {
    	var activeCount = this.currentResponse.CountUnlockRequests();
    	var message = '';

    	if(activeCount == 1) {
    		message = application.renderLabelWarning(UI.Icon().Unlock() + ' ' + t('1 unlock request.'));
    	} else if(activeCount > 1) {
    		message = application.renderLabelWarning(UI.Icon().Unlock() + ' ' + t('%1$s unlock requests.', activeCount));
    	} else {
    		message = application.renderLabelInactive(UI.Icon().Unlock() + ' ' + t('No unlock requests.'));
    	}
		
		$('#locking-unlock-requests').html(message.Render());
		
		if(activeCount > 0) {
			$('#locking-unlock-requests').addClass('clickable');
		} else {
			$('#locking-unlock-requests').removeClass('clickable');
		}
    },
    
    RenderLabel:function(label)
    {
    	if(label.length <= this.LabelsCutLength) {
    		return label;
    	}
    	
    	label = label.substring(0, this.LabelsCutLength)+'...';
    	return label;
    },
    
    RenderActiveLocks:function()
    {
    	infoID = nextJSID();
    	
    	var html = ''+
		'<i class="empty-results" style="display:none">'+ t('You are not locking any pages.') + '</i>'+
		'<table class="table table-hover table-first-unstyled table-condensed table-popover table-nomargin locks-list" style="display:none">'+
			/*'<thead>'+
				'<tr>'+
					'<th style="width:1%;white-space:nowrap;">'+t('Time')+'</th>'+
					'<th>'+t('Page')+'</th>'+
					'<th></th>'+
				'</tr>'+
			'</thead>'+*/
			'<tfoot>'+
				'<tr>'+
					'<td colspan="4" class="muted" style="padding-top:13px">'+
						'<span id="'+infoID+'" class="help" title="'+t('The lock of the current page or expiring pages cannot be extended, as shown with their disabled icon.')+'">'+
							UI.Icon().Information()+' '+
							t('Click an expiry timer to extend the lock of that page.')+
						'</span>'+
					'</td>'+
				'</tr>'+
			'</tfoot>'+
			'<tbody class="lock-entries"></tbody>'+
		'</table>';
    	
    	var jsID = $('#locking-active-locks').data('container-id');
    	$('#'+jsID).html(html);
    	
    	UI.MakeTooltip('#'+infoID);
    	
    	this.activeLocksRendered = true;
    },
    
    'extendLocks':{},

   /**
    * Schedules a lock to be extended. Will be requested on
    * the next status refresh.
    * 
    * @param {LockManager_LockInfo} lock
    */
    Owner_ExtendLock:function(lock)
    {
    	var lockID = lock.GetID();
    	if(typeof(this.extendLocks[lockID]) != 'undefined') {
    		return;
    	}
    	
    	this.extendLocks[lockID] = lock;
    	
    	$('.lock-entry-'+lockID).find('.lock-time').html(application.renderSpinner(t('Extending...')));
    	$('.lock-entry-'+lockID).find('.lock-releasebtn').html('');
    },
    
	GetCritLevelPercent:function(level)
	{
		var result = null;
		$.each(this.CritLevels, function(percent, critLevel) {
			if(level == critLevel) {
				result = percent;
				return false;
			}
		});
		
		return result;
	}
};

var LockManager_StatusResponse = 
{
	'data':null,
	'currentLock':null,
	'activeLocks':null,
	'unlockRequests':null,
	
	init:function(responseData)
	{
		this.data = responseData;
		this.currentLock = null;
		this.activeLocks = {};
		this.unlockRequests = [];

		if(!this.IsLocked()) {
			return;
		}
		
		var activeLocks = {};
		$.each(this.data.active_locks, function(id, lockData) {
			var lock = new LockManager_LockInfo(lockData);
			activeLocks[lock.GetID()] = lock;
		});
		
		this.activeLocks = activeLocks;
		
		this.currentLock = new LockManager_LockInfo(this.data.current_lock);
		
		var reqs = [];
		$.each(this.GetActiveLocks(), function(idx, lock) {
			if(!lock.HasUnlockRequests()) {
				return;
			}
			
			$.each(lock.GetUnlockRequests(), function(idx, req) {
				reqs.push(req);
			});
		});
		
		this.unlockRequests = reqs;
	},
	
	IsLocked:function()
	{
		return this.data.locked;
	},
	
	HasCurrentLock:function()
	{
		if(this.currentLock != null) {
			return true;
		}
		
		return false;
	},
	
	GetCurrentLockID:function()
	{
		return this.data.current_lock_id;
	},
	
	GetActiveLocks:function()
	{
		return this.activeLocks;
	},
	
	CountActiveLocks:function()
	{
		return this.data.active_locks_count;
	},
	
	CountUnlockRequests:function()
	{
		return this.unlockRequests.length;
	},
	
	GetCurrentLock:function()
	{
		return this.currentLock;
	},
	
	GetTimeElapsed:function()
	{
		var lock = this.GetCurrentLock();
		if(lock != null) {
			return lock.GetEditTimeElapsed();
		}
		
		return '';
	},
	
	GetExtendedLockIDs:function()
	{
		return this.data.extended_locks;
	},
	
	GetReleasedLockIDs:function()
	{
		return this.data.released_locks;
	},
	
	GetTimeToUnlock:function()
	{
		var lock = this.GetCurrentLock();
		if(lock != null) {
			return lock.GetTimeToUnlock();
		}
		
		return '';
	},
	
	HasUnlockRequests:function()
	{
		if(this.unlockRequests.length > 0) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Retrieves all unlock requests for all active locks of the user.
    * @returns {LockManager_UnlockRequest[]}
    */
	GetUnlockRequests:function()
	{
		return this.unlockRequests;
	},
	
   /**
    * Retrieves the highest criticality level of all the active locks.
    * @return {String}
    */
	GetCritLevel:function()
	{
		var minPercent = 100;
		var highestLevel = null;
		$.each(this.GetActiveLocks(), function(idx, lock) {
			var level = lock.GetCritLevel();
			var percent = LockManager.GetCritLevelPercent();
			if(percent < minPercent) {
				minPercent = percent;
				highestLevel = level;
			}
		});
		
		return highestLevel;
	}	
};

LockManager_StatusResponse = Class.extend(LockManager_StatusResponse);

var LockManager_UnlockRequest = 
{
	'lock':null,
	'data':null,
		
	init:function(lock, data)
	{
		this.lock = lock;
		this.data = data;
	},
	
	GetLock:function()
	{
		return this.lock;
	},
		
	GetLockID:function()
	{
		return this.lock.GetID();
	},
	
	GetFromUserID:function()
	{
		return this.data.from_user;
	},
	
	GetFromUserName:function()
	{
		return this.data.from_user_name;
	},
	
	GetMessage:function()
	{
		return this.data.message;
	},
	
	GetAgePretty:function()
	{
		return this.data.age_pretty;
	},
	
	GetScreenName:function()
	{
		return this.lock.GetScreenName();
	},
	
	GetLabel:function()
	{
		return this.lock.GetLabel();
	}
	
	/*
	'message_id' => $this->getID(),
    'is_reply' => $this->isReply(),
    'in_reply_to' => $this->getInReplyToID(),
    'from_user' => $this->getFromUserID(),
    'from_user_name' => $this->getFromUser()->getName(),
    'to_user' => $this->getToUserID(),
    'to_user_name' => $this->getToUser()->getName(),
    'message' => $this->getMessage(),
    'priority' => $this->getPriority(),
    'priority_pretty' => $this->getPriorityPretty(),
    'date_sent' => $this->getDateSent()->format('Y-m-d H:i:s'),
    'date_received' => $dateReceived,
    'responded' => $responded,
    'date_responded' => $dateResponded,
    'response' => $this->getResponse(),
    'custom_data' => $this->getCustomData(),
    'age_pretty' => $this->getAgePretty()
    */
};

LockManager_UnlockRequest = Class.extend(LockManager_UnlockRequest);

var LockManager_LockInfo = 
{
	'data':null,
	'unlockRequests':null,
	
	init:function(lockData)
	{
		this.data = lockData;
		this.unlockRequests = null;
	},
	
	GetID:function()
	{
		return this.data.lock_id;
	},
	
	GetLabel:function()
	{
		return this.data.lock_label;
	},
	
	GetScreenName:function()
	{
		return this.data.screen_name;
	},
	
	GetCritLevel:function()
	{
		return this.data.time_to_unlock_critlevel;
	},
	
	GetLockedUntil:function()
	{
		return this.data.locked_until;
	},
	
	GetLastActivity:function()
	{
		return this.data.last_activity;
	},
	
	GetScreenURLPath:function()
	{
		return this.data.screen_url_path;
	},
	
	GetItemPrimary:function()
	{
		return this.data.item_primary;
	},
	
	GetLockedByID:function()
	{
		return this.locked_by;
	},
	
	GetEditTimeElapsed:function()
	{
		return this.data.time_editing_elapsed;
	},
	
	GetTimeToUnlock:function()
	{
		return this.data.time_to_unlock;
	},
	
	GetTimeToUnlockShort:function()
	{
		return this.data.time_to_unlock_short;
	},
	
	GetLockedByName:function()
	{
		return this.data.user_name;
	},
	
	GetUnlockRequests:function()
	{
		if(this.unlockRequests != null) {
			return this.unlockRequests;
		}
		
		var lock = this;
		var reqs = [];
		$.each(this.data.unlock_requests, function(idx, reqData) {
			reqs.push(new LockManager_UnlockRequest(lock, reqData));
		});
		
		this.unlockRequests = reqs;
		
		return reqs;
	},
	
	HasUnlockRequests:function()
	{
		if(this.CountUnlockRequests() > 0) {
			return true;
		}
		
		return false;
	},
	
	CountUnlockRequests:function()
	{
		return this.data.unlock_requests.length;
	},
	
	IsReleased:function()
	{
		return this.data.is_released;
	},
	
	IsForcedRelease:function()
	{
		return this.data.is_forced_release;
	}
};

LockManager_LockInfo = Class.extend(LockManager_LockInfo);