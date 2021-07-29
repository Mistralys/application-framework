/**
 * Handles the edit dialog for a single feature table field.
 * Uses subclasses to handle every available tab.
 *
 * @package Maileditor
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
var TabbedDialog = 
{
	'dialog':null,
	'jsID':null,
	'tabs':null,
	'renderedTabs':null,
	'initializedTabs':null,
	'tabStates':null,
	'activeTab':null,
	'messages':null,
	'shown':null,
	'lastOpenedTab':null,
	'confirmChanges':null,
			
	init:function()
	{
		this.dialog = null;
		this.jsID = nextJSID();
		this.activeTab = null;
		this.shown = false;
		this.confirmChanges = true;
		this.tabs = {};
		this.tabStates = {};
		this.renderedTabs = {};
		this.initializedTabs = {};
		this.messages = [];
		
		application.registerDialog(this);
	},
	
   /**
    * Retrieves the ID of the dialog, which is unique for 
    * each dialog and used in all element IDs to keep them
    * separated from all other elements.
    * 
    * @returns {String}
    */
	GetJSID:function()
	{
		return this.jsID;
	},
	
   /**
    * Tells the dialog to disregard if any tabs have 
    * unsaved changes, and not display the closing
    * warning message.
    * 
    * @returns {TabbedDialog}
    */
	IgnoreChanges:function()
	{
		this.confirmChanges = false;
		return this;
	},
	
   /**
    * Restores the tab modified checks after disabling them
    * using the {@link IgnoreChanges()} method.
    * 
    * @returns {TabbedDialog}
    */
	ConfirmChanges:function()
	{
		this.confirmChanges = true;
		return this;
	},
	
   /**
    * Adds a tab to the dialog.
    * 
    * @param {TabbedDialogTab} tab
    */
	AddTab:function(tab)
	{
		if(!tab instanceof TabbedDialogTab) {
			throw new ApplicationException(
				t('Error while initializing dialog tabs.'),
				'The specified tab is not a valid tab object, it must inherit from the [TabbedDialogTab] class.'
			);
		}
		
		if(this.AliasExists(tab.GetAlias())) {
			throw new ApplicationException(
				t('Error while initializing dialog tabs.'),
				'The specified tab with alias ['+tab.GetAlias()+'] cannot be added, a tab with the same alias already exists.'
			);
		}
		
		tabID = tab.GetID();
		this.tabs[tabID] = tab;
		this.tabStates[tabID] = 'not-modified';
	},
	
   /**
    * Opens the dialog.
    * 
    * @param {String} [showTabName=null] The name/alias of the tab to open. Defaults to the first tab in the dialog.
    */
	Show:function(showTabName)
	{
		if(this.shown) {
			return;
		}
		
		if(this.dialog==null) {
			this.Render();
			var instance = this; 
			setTimeout(
				function() {
					instance.Handle_PostRender();
					instance.Show(showTabName);
				}
			);
			
			return;
		} 
		
		this.shown = true;
		
		var dialog = this;
		Mousetrap.bind(['alt+t'], function() {
			dialog.Handle_NextTab();
			return false;
		});
		
		this.dialog.modal('show');

		this.ResolveActiveTab();
		this.Handle_ActivateTab(this.activeTab);
		this.DisplayMessages();

		application.disallowAutoRefresh('dialogs');
	},
	
	ResolveActiveTab:function()
	{
		this.log('Determining tab to open.', 'ui');
		
		// the default tab to open is the first in the collection...
		var tabs = array_values(this.tabs);
		var activeTabName = tabs[0].GetAlias();
		
		this.log('Default tab is ['+activeTabName+'].', 'ui');
		
		// ...or the tab type the user accessed previously (dialog-independent)
		// Of course this only works if the current dialog has a tab of the same name.
		var lastOpened = application.getPref('tabbedDialog_lastOpenedTab', null);
		if(lastOpened != null) {
			this.log('Last opened tab was ['+lastOpened+']', 'ui');
			var tab = this.GetTabByAlias(lastOpened);
			if(tab!=null) {
				activeTabName = tab.GetAlias();
				this.log('Using the last opened tab.', 'ui');
			}
		}
		
		// ...or the one the user accessed previously in this dialog.
		if(this.activeTab != null) {
			activeTabName = this.GetTabByID(this.activeTab).GetAlias();
			this.log('Using the tab the user selected last, ['+activeTabName+'].', 'ui');
		}
		
		// a specific tab has been requested
		if(typeof(showTabName) != 'undefined') {
			if(this.AliasExists(showTabName)) {
				activeTabName = showTabName;
			}
		}
		
		activeTab = this.GetTabByAlias(activeTabName);
		this.activeTab = activeTab.GetID();
		application.setPref('tabbedDialog_lastOpenedTab', activeTab.GetAlias());
	},
	
   /**
    * Checks whether the dialog is currently open.
    * @returns {Boolean}
    */
	IsShown:function()
	{
		return this.shown;
	},
	
   /**
    * Checks whether a tab alias name exists within the dialog.
    * 
    * @param {String} alias
    * @returns {Boolean}
    */
	AliasExists:function(alias)
	{
		var tab = this.GetTabByAlias(alias);
		if(tab) {
			 return true;
		}

		return false;
	},
	
   /**
    * Retrieves a tab instance of the dialog by its alias.
    * 
    * @param {String} alias
    * @returns {TabbedDialogTab|NULL}
    */
	GetTabByAlias:function(alias)
	{
		var found = null;
		$.each(this.tabs, function(idx, tab) {
			if(tab.GetAlias()==alias) {
				found = tab;
			}
		});
		
		return found;
	},
	
   /**
    * Retrieves a tab by its ID.
    * 
    * @param {String} id
    * @returns {TabbedDialogTab|NULL}
    */
	GetTabByID:function(id)
	{
		var found = null;
		$.each(this.tabs, function(idx, tab) {
			if(tab.GetID()==id) {
				found = tab;
			}
		});
		
		return found;
	},
	
	Handle_NextTab:function()
	{
		this.log('Switching to next tab.', 'event');
		
		var activeTab = this.activeTab;
		var list = array_values(this.tabs);
		var nextID = null;
		
		for(var i=0; i<list.length; i++) {
			tab = list[i];
			if(tab.GetID()==activeTab) {
				idx = i+1;
				if(typeof(list[idx]) == 'undefined') {
					idx = 0;
				}
				
				nextID = list[idx].GetID();
				break;
			}
		}
		
		this.element('tab_title_'+nextID).tab('show');
	},
	
   /**
    * Hides the dialog. Has no effect if it was not shown.
    */
	Hide:function()
	{
		this.dialog.modal('hide');
		this.shown = false;

		application.allowAutoRefresh('dialogs');
	},
	
   /**
    * Adds an error message alert to the dialog. This gets displayed
    * directly if the dialog is already open, or once the user opens
    * the dialog otherwise.
    * 
    * @param {String} message The message to display. Can contain HTML.
    */
	AddErrorMessage:function(message)
	{
		this.AddMessage(application.MESSAGE_TYPE_ERROR, message);
	},
	
   /**
    * Adds a success message alert to the dialog. This gets displayed
    * directly if the dialog is already open, or once the user opens
    * the dialog otherwise.
    * 
    * @param {String} message The message to display. Can contain HTML.
    */
	AddSuccessMessage:function(message)
	{
		this.AddMessage(application.MESSAGE_TYPE_SUCCESS, message);
	},
	
   /**
    * Adds an information message alert to the dialog. This gets displayed
    * directly if the dialog is already open, or once the user opens
    * the dialog otherwise.
    * 
    * @param {String} message The message to display. Can contain HTML.
    */
	AddInfoMessage:function(message)
	{
		this.AddMessage(application.MESSAGE_TYPE_INFO, message);
	},
	
	AddMessage:function(type, message)
	{
		this.messages.push({
			'type':type,
			'message':message
		});
		
		if(this.IsShown()) {
			this.DisplayMessages();
		}
	},
	
	DisplayMessages:function()
	{
		if(this.messages.length < 1) {
			return;
		}
		
		var container = this.element('messages');
		
		for(var i=0; i<this.messages.length; i++) {
			msg = this.messages[i];
			container.append(application.renderAlert(
				msg.type, 
				msg.message, 
				true
			));
		}

		// clear the messages collection now that we've shown them all
		this.messages = [];
	},
	
   /**
    * Clears all messages that may still be shown in the UI.
    */
	ClearMessages:function()
	{
		this.element('messages').html('');
	},

	Render:function()
	{
		this.log('Rendering scaffold.');
		
		var dialog = this;
		var body = ''+
		'<div class="dialog-messages" id="'+this.elementID('messages')+'">'+
		'</div>'+
		'<div class="nav-tabs-hint">'+
			'<span class="keyboard-shortcut">'+
				'<span class="keyboard-shortcut-label">'+
					t('Switch tabs:')+' '+
				'</span>'+
				'ALT+T'+
			'</span>'+
		'</div>'+
		'<ul class="nav nav-tabs" id="'+this.elementID('tabs')+'">';
			$.each(this.tabs, function(tabID, tab) {
				body += ''+
				'<li>'+
					'<a href="#'+dialog.elementID('tab_'+tabID)+'" data-toggle="tab" rel="'+tabID+'" id="'+dialog.elementID('tab_title_'+tabID)+'">'+
						tab.GetTitle()+
					'</a>'+
				'</li>';
			});
			body += ''+
		'</ul>'+
		'<div class="tab-content">';
			$.each(this.tabs, function(tabID, tab) {
				body += ''+
				'<div class="tab-pane" id="'+dialog.elementID('tab_'+tabID)+'">'+
					tab.GetTitle()+
				'</div>';
			});
			body += ''+
		'</div>';
		
		var footer = '<div id="'+this.elementID('tab_footer')+'"></div>';
		
		this.dialog = DialogHelper.createLargeDialog(
			this.GetTitle(),
			body,
			footer
		);
		
		this.dialog.on('hide', function(e) {
			return dialog.Handle_HideDialog(e);
		});
		
		this.log('Scaffold render complete.');
	},
	
	GetTitle:function()
	{
		return Application.appNameShort;
	},
	
	Handle_HideDialog:function(e)
	{
		if(!this.HasModifiedTabs()) {
			this.ClearMessages();
			this.shown = false;
			return true;
		}
		
		if(!this.confirmChanges) {
			return true;
		}
		
		var confirm = window.confirm(
			t('Note:')+' '+
			t('If you close the dialog now, your changes will be ignored when you save the structure.')
		);
		
		if(confirm) {
			this.ClearMessages();
			this.shown = false;
		}
		
		return confirm;
	},
	
	HasModifiedTabs:function()
	{
		var modified = false;
		var dialog = this;
		$.each(this.tabs, function(tabID, tab) {
			if(dialog.IsTabRendered(tabID) && tab.IsModified()) {
				modified = true;
			}
		});
		
		return modified;
	},
	
	SetTabModified:function(tabID)
	{
		if(this.tabStates[tabID]=='modified') {
			return;
		}
		
		var tab = this.tabs[tabID];
		this.element('tab_title_'+tabID).html(
			tab.GetTitle()+' '+
			application.renderLabelWarning(t('Modified'))
		);
		
		this.tabStates[tabID] = 'modified';
		
		tab.Handle_Modified();
	},
	
	SetTabNotModified:function(tabID)
	{
		if(this.tabStates[tabID]=='not-modified') {
			return;
		}
		
		var tab = this.tabs[tabID];
		this.element('tab_title_'+tabID).html(
			tab.GetTitle()
		);
		
		this.tabStates[tabID] ='not-modified';
		
		tab.Handle_NotModified();
	},
	
	Handle_PostRender:function()
	{
		var dialog = this;
		
		$('#'+this.elementID('tabs')+' a').on('shown', function (e) {
			var tabID = $(e.target).attr('rel');
			dialog.Handle_ActivateTab(tabID);
		});
	},
	
	Handle_ActivateTab:function(tabID)
	{
		if(!this.IsTabRendered(tabID)) {
			this.RenderTab(tabID);
			
			var dialog = this;
			UI.RefreshTimeout(function() {
				dialog.Handle_ActivateTab(tabID);
			});
			return;
		}
		
		this.DoActivateTab(tabID);
	},
	
	IsTabRendered:function(tabID)
	{
		if(typeof(this.renderedTabs[tabID])=='undefined' || this.renderedTabs[tabID] == false) {
			return false;
		}
		
		return true;
	},
	
	RenderTab:function(tabID)
	{
		if(this.renderedTabs[tabID]==false) {
			return;
		}
		
		this.log('Rendering tab ['+tabID+'].', 'ui');
		
		try{
			// render the body
			var html = this.tabs[tabID].RenderBody(this.elementID('tab_form_'+tabID));
			this.element('tab_'+tabID).html(html);
	
			// render the footer (buttons and co)
			var html = this.tabs[tabID].RenderFooter();
			this.element('tab_footer').append(
				'<div id="'+this.elementID('tab_footer_'+tabID)+'" style="display:none;">'+
					html+
				'</div>'
			);
			
			this.renderedTabs[tabID] = true;
		} catch(err) {
			application.handle_errorThrown(err);
			
			this.element('tab_'+tabID).html(application.renderAlertError(
				UI.Icon().Warning()+' '+
				t('An error occurred.')+' '+
				t('This tab will not be functional.')
			));
			
			this.renderedTabs[tabID] = false;
		}

		this.log('Rendering done.', 'ui');
	},
	
	DoActivateTab:function(tabID)
	{
		this.log('Activating tab ['+tabID+'].', 'event');
		
		var dialog = this;
		$.each(this.tabs, function(id, label) {
			if(id==tabID) {
				dialog.element('tab_footer_'+id).show();
				return;
			}
			
			dialog.element('tab_footer_'+id).hide();
		});
		
		if(typeof(this.initializedTabs[tabID])=='undefined') {
			this.PostRenderTab(tabID);
		}
		
		this.element('tab_title_'+tabID).tab('show');

		this.tabs[tabID].DoActivate();
		this.activeTab = tabID;
		
		application.setPref('tabbedDialog_lastOpenedTab', this.tabs[tabID].GetAlias());
	},
	
	PostRenderTab:function(tabID)
	{
		this.log('Post rendering tab ['+tabID+'].', 'ui');
		
		var dialog = this;
		var form = this.element('tab_form_'+tabID);
		
		form.submit(function(e) {
			e.preventDefault();
			dialog.Handle_SubmitTab(tabID);
		});
		
		this.tabs[tabID].elForm = form;
		this.tabs[tabID].PostRender();
		
		// disable the apply and ok buttons, as after rendering
		// no changes have been made yet.
		this.tabs[tabID].Handle_NotModified();
		
		this.initializedTabs[tabID] = true;
	},
	
   /**
    * Called when the form of a tab is submitted. This calls
    * the tab's own Handle_Submit handler, and the return value
    * of this method decides whether the operation was successful.
    * 
    * @return {Boolean} Whether the tab could be submitted.
    */
	Handle_SubmitTab:function(tabID)
	{
		var tab = this.tabs[tabID];
		
		this.log('Submitting tab ['+tabID+'].', 'event');
		
		// the tab's submit handler returns a boolean flag which
		// indicates whether any changes were made.
		if(!tab.Handle_Submit()) {
			this.log('The tab says it can not be submitted.', 'event');
			return false;
		}
		
		this.log('Tab submitted successfully.', 'event');
		tab.SetNotModified();
		
		// only close the dialog if the tab is not in apply mode,
		// otherwise we keep it open so the user can continue working.
		if(!tab.IsApplyMode()) {
			this.Hide();
		}
		
		// we assume that any messages that may still be shown
		// become obsolete with the submit operation, so we remove them.
		this.ClearMessages();
		
		return true;
	},
	
	elementID:function(part)
	{
		if(typeof(part)=='undefined') {
			return this.jsID;
		}
		
		return this.jsID+'_'+part;
	},
	
	element:function(part)
	{
		var id = this.elementID(part);
		return $('#'+id);
	},
	
	log:function(message, category)
	{
		application.log(
			'Field Edit Dialog ['+this.field.alias+']',
			message,
			category
		);
	}
};

TabbedDialog = Class.extend(TabbedDialog);
