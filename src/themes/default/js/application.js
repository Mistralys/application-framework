/**
 * @namespace Application
 */

/**
 * Clientside application class that offers a global framework
 * for handling common UI tasks, from message dialogs to AJAX
 * requests.
 *
 * @package Application
 * @class
 * @static
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var application =
{
    'ERROR_SENDING_FEEDBACK_FAILED': 599011,
    'ERROR_COULD_NOT_LOAD_WHATSNEW_DIALOG':599012,
    'ERROR_UNHANDLED_AJAX_ERROR':599013,
    'ERROR_AJAX_RESOURCE_DOES_NOT_EXIST':599014,
    'ERROR_FAILED_TO_RETRIEVE_SCRIPT_LOADKEY':599015,
    'ERROR_TRANSLITERATION_FAILED':599016,
    'ERROR_FAILED_TO_FETCH_HTML':599017,

    'url': null,
    'fieldTypes': [],
    'dialogWidth': 450,
    'dialogAnimation': '', // fade
    'contentLocales': {},
    'contentLocale': '',
    'units': [],
    'strings': {},
    'deletionDelay': '',
    'scrollToDelay': 900,
    'locale': null, // the application locale - duh
    'printmode': false, // whether print mode is active
    'appName': null, // The full application name
    'appNameShort': null, // shorthand of the application's name
    'className':null, // the driver class name, set serverside
    'instanceID':null, // set serverside. ID of the SPIN instance, "hosting" or "access".

    // miliseconds to wait after the user typed into a form
    // to auto-validate the form
    'formValidationDelay': 600,

    // miliseconds to wait to give the browser time to build the
    // DOM after inserting dynamic elements, so that events can
    // be attached properly for ex. 100 is a good value, lower
    // values must be experimented with.
    //
    // seems to be required mostly by Chrome, with several other
    // types of hack available to get the browser to re-render the
    // page, but nothing as reliable as just giving it the time to
    // do so naturally.
    'uiRefreshDelay': 40,

    // categories for logging messages: this is used to determine
    // which kinds of messages to display in the console.
    'loggingCategories': {},

    toggleRevisionsPanel: function (panelID, linkEl) {
        var panel = $('#' + panelID);
        if (panel.is(':visible')) {
            panel.hide();
            linkEl.addClass('link_collapsible_expand');
            linkEl.removeClass('link_collapsible_collapse');
        } else {
            panel.show();
            linkEl.removeClass('link_collapsible_expand');
            linkEl.addClass('link_collapsible_collapse');
        }
    },

    isLoggingActive: function (loggingCategory) {
    	this.setUp();
    	
        if (typeof(loggingCategory) == 'undefined') {
            loggingCategory = 'misc';
        }

        if (typeof(this.loggingCategories[loggingCategory]) == 'undefined') {
            console.log('Warning: Logging category [' + loggingCategory + '] does not exist.');
            return false;
        }

        if (this.getPref('logging_' + loggingCategory) == 'yes') {
            return true;
        }

        if (this.loggingCategories[loggingCategory] == 'yes') {
            return true;
        }

        return false;
    },
    
    setLoggingFor: function(loggingCategory, state)
    {
	    if (typeof(this.loggingCategories[loggingCategory]) == 'undefined') {
	        console.log('Cannot change logging for [' + loggingCategory + '], it has not been registered.');
	        return false;
	    }
	
	    var value = bool2string(state, true);
	    
	    this.loggingCategories[loggingCategory]['active'] = value;
	    this.setPref('logging_' + loggingCategory, value);
    },

    enableLoggingFor: function (loggingCategory) {
        return this.setLoggingFor(loggingCategory, true);
    },

    disableLoggingFor: function (loggingCategory) {
        return this.setLoggingFor(loggingCategory, false);
    },

    registerLoggingCategory: function (name, label, active) {
        if (typeof(active) == 'undefined') {
            active = 'no';
        } else {
            active = 'yes';
        }

        this.loggingCategories[name] = {
            'name': name,
            'label': label,
            'active': active
        };
    },

    addContentLocale: function (name, label) {
        this.contentLocales[name] = label;
    },

    addString: function (hash, text) {
        this.strings[hash] = text;
    },

    selectLocale: function (localeName) {
        this.contentLocale = localeName;
    },

    submitForm: function (id, develMode, removeHandlers) 
    {
    	FormHelper.submit(id, develMode, removeHandlers);
    },
    
    'setupDone':false,

    /**
     * Sets up the application environment: this is called automatically
     * server-side before the onload event.
     */
    setUp: function () {
    	if(this.setupDone) {
    		return;
    	}
    	
    	this.setupDone = true;
    	
        this.registerLoggingCategory('misc', t('Miscellaneous / Uncategorized'), true);
        this.registerLoggingCategory('ui', t('User interface related'), true);
        this.registerLoggingCategory('data', t('Modification of data records'), true);
        this.registerLoggingCategory('error', t('Error messages'), true);
        this.registerLoggingCategory('event', t('Event handling'), true);
        this.registerLoggingCategory('debug', t('Debugging output (objects, variables, etc.)'), true);
    },

    /**
     * Starts the application framework. This is called automatically
     * server-side with the page's onload.
     */
    start: function () {

        this.handle_JavaScriptError();

        this.readCookies();

        // give numeric fields the ability to be incremented
        // via the up/down arrow keys.
        $('input').each(
            function () {
                if ($(this).attr('rel') == 'intfield') {
                    $(this).keyup(function (event) {
                        if (event.which == KeyCodes.ArrowUp) {
                            var newval = $(this).val();
                            newval = newval.replace(/,/, '.');
                            newval = (newval * 1) + 1;
                            $(this).val(newval);
                        } else if (event.which == KeyCodes.ArrowDown) {
                            var newval = $(this).val();
                            newval = newval.replace(/,/, '.');
                            newval = (newval * 1) - 1;
                            if (newval < 0) {
                                newval = 0;
                            }
                            $(this).val(newval);
                        }
                    });
                }
            }
        );

        // make the sidebar sticky so it scrolls along with the user
        Sidebar.Start();

        // we resize the layout once at startup, but after a short delay to
        // allow for dynamic resizing on initialization to have happened before
        // we do it.
        this.timedAdjustLayout();

        // add tooltips where applicable
        this.tooltipify();

        // when the content area is resized (which may happen on the clientside via
        // scripts when content is added or when using accordeons and the like, we
        // need to update the layout so the sticky sidebar behaves as intended.
        $('#content').resize(function () {
            application.adjustLayout();
        });

        // avoid the bootstrap menus to close when clicking in a form in the menu
        // for this, the <li> with the form elements needs to have the [.dropdown-form]
        // class.
        // http://stackoverflow.com/questions/8110356/dropdown-with-a-form-inside-with-twitter-bootstrap/10131791#10131791
        $('html').off('click.dropdown.data-api');
        $('html').on('click.dropdown.data-api', function (e) {
            if (!$(e.target).parents().is('.dropdown-form')) {
                $('.dropdown').removeClass('open');
                $('.btn-group').removeClass('open');
            }
        });

        this.refreshSelectables();

        $.ajaxSetup({cache: true});
        
        UI.Start();
    },

    refreshSelectables: function () {
        $('.noselection')
            .attr('unselectable', 'on')
            .css('user-select', 'none')
            .css('-webkit-user-select', 'none')
            .css('-moz-user-select', 'none')
            .css('MozUserSelect', 'none')
            .on('selectstart', false);
    },

    /**
     * Uses selectors to add tooltips to all acronym and i tags that
     * have the rel="tooltip" attribute set.
     */
    tooltipify: function () {
    	UI.MakeTooltip('[data-toggle="tooltip"]');
        UI.MakeTooltip('acronym[rel="tooltip"]');
        UI.MakeTooltip('i[rel="tooltip"]');
    },

    /**
     * This should be called every time clientside script modify the height
     * of the page: it adjusts the size of the sidebar to the height of the
     * page itself to make sure it does not go out of bounds.
     * @deprecated
     */
    adjustLayout: function () {
    },

    timedAdjustLayout: function () {
    },

    /**
     * Builds and returns an URL to an image from the application's main img/ folder.
     * @param {String} imageFile
     * @returns {String}
     */
    imageURL: function (imageFile) {
        return this.url + '/img/' + imageFile;
    },

    addFieldType: function (id, label) {
        var item = {
            'id': id,
            'label': label
        };

        this.fieldTypes.push(item);
    },

    /**
     * Redirects the user to the specified URL. If an object
     * is used as parameter, builds an internal application URL
     * using the properties of the object as URL parameters.
     *
     * @param {String|Object} urlOrParams
     * @param {String} [loaderText] Optional text to show while the user waits for the request to process
     */
    redirect: function (urlOrParams, loaderText) 
    {
        this.redirectWithoutLoader(urlOrParams);
     
        if(isEmpty(loaderText)) {
    		loaderText = t('Please wait, refreshing...');
    	}
        
        this.showLoader(loaderText);
    },
    
    redirectWithoutLoader: function(urlOrParams)
    {
        var url = urlOrParams;
        if (typeof(urlOrParams) == 'object') {
            url = this.buildURL(urlOrParams);
        }

        document.location = url.replace(/&amp;/gi, '&');
    },
    
   /**
    * Refeshes the page, with an optional loading text that
    * will be shown in a loader while the user waits for the
    * request to process.
    * 
     * @param {String} [loaderText] Optional text to show while the user waits for the request to process
    */
    refreshPage:function(loaderText)
    {
    	if(isEmpty(loaderText)) {
    		loaderText = t('Please wait, refreshing...');
    	}
    	
    	this.showLoader(loaderText);
    	
    	document.location.reload();
    },

    /**
     * Builds an internal application url from the specified parameters.
     *
     * @param {Object} params Key => value pairs of parameters to insert into the URL
     * @param {String} [script=index.php] The dispatcher script to use
     * @returns {String}
     */
    buildURL: function (params, script) 
    {
        if (isEmpty(script)) { var script = ''; }
        if (isEmpty(params)) { var params = {}; }

        var url = this.url;
        if (url.slice(-1) != '/') {
            url += '/';
        }

        url += script;

        var tokens = [];
        $.each(params, function (key, val) {
            tokens.push(key + '=' + encodeURIComponent(val));
        });

        if (tokens.length > 0) {
            url += '?';
        }

        return url + tokens.join('&');
    },
    
    'elDialogLogging': null,

    /**
     * Displays a modal dialog that allows modifying logging settings
     * regarding messages that are shown in the browser's console.
     * For each type of logging category, it can be chosen whether
     * messages in that category should be shown. The settings are
     * persisted using cookies.
     */
    dialogLogging: function () {
        if (this.elDialogLogging == null) {
            this.elDialogLogging = new Application_Dialog_Logging();
        }

        this.elDialogLogging.Show();
    },

    /**
     * Displays a modal dialog with a custom message. This
     * dialog only has a button to close it, it has no
     * additional features.
     *
     * @param {String} message The message to display. Can contain HTML.
     * @param {String} [title]
     * @param {Function} [closeHandler] Function that gets called when the dialog is closed.
     */
    dialogMessage: function (message, title, closeHandler) 
    {
		var dialog = this.createDialogMessage(message, title, closeHandler).Show();

        // Pre-select the dismiss button so the user can dismiss the
        // dialog just by pressing enter. We need to do this with
        // a timeout because in some situations, the browser will not
        // focus on the button.
        UI.RefreshTimeout(function () {
        	var btn = dialog.GetButton('close');
        	if(btn) {
        		btn.Focus();
        	}
        });
        
        return dialog;
    },
    
    createDialogMessage:function(message, title, closeHandler)
    {
        if (typeof(title) == 'undefined') {
            var title = t('%1$s message', this.appNameShort);
        }

    	var dialog = new Dialog_Generic(title, message);

        if(typeof(closeHandler)!='undefined') {
        	dialog.Hidden(closeHandler);
        }

        return dialog;
    },

    'elDialogWhatsnew': null,

    dialogWhatsnew: function () 
    {
    	if(typeof(Application_Dialog_Whatsnew)=='undefined') {
    		this.showLoader(t('Please wait, loading...'));
        	this.loadScript(
    			'application/dialog/whatsnew.js', 
    			function() {
    				application.hideLoader();
    				application.dialogWhatsnew();
    			},
    			function(errorText, data) {
    				application.hideLoader();
    				application.createDialogAJAXError(
						errorText, 
						data, 
						t('Could not include a required file.'), 
						application.ERROR_COULD_NOT_LOAD_WHATSNEW_DIALOG
					).Show()
    			}
			);
        	return;
    	}
    	
        if (this.elDialogWhatsnew == null) {
            this.elDialogWhatsnew = new Application_Dialog_Whatsnew();
        }

        this.elDialogWhatsnew.Show();
    },
    
    dialogNotImplemented:function()
    {
    	this.dialogMessage(
			application.renderAlertInfo(
				UI.Icon().Information() + ' ' + 
				'<b>' + t('This feature is not available yet:') + '</b> ' + t('It is still being developed.')
			)
		);
    },
    
   /**
    * Checks whether the specified script has already been loaded.
    * 
    * @public
    * @param {String} script For example <code>forms.js</code>
    * @returns {Boolean}
    */
    isScriptLoaded:function(script)
    {
    	var keys = this.getLoadKeys();
    	for(var i=0; i<keys.length; i++) {
    		if(keys[i].script == script) {
    			return true;
    		}
    	}
    	
    	return false;
    },
    
   /**
    * Loads several scripts sequentially.
    * 
    * @param {Array} scripts Indexed array with paths to the script files
    * @param {Function} successHandler
    * @param {Function} failureHandler Gets the error message as unique parameter
    * @param {Object} [queue] Not required: used internally to track scripts load progress.
    */
    loadScripts:function(scripts, successHandler, failureHandler, queue)
    {
    	if(isEmpty(queue)) {
    		queue = {
				'scripts':scripts,
				'pos':0
    		};
    	} else {
    		queue.pos = queue.pos + 1;
    	}
    	
    	if(queue.pos == queue.scripts.length) {
    		successHandler.call(undefined);
    		return;
    	}
    	
    	var activeScript = queue.scripts[queue.pos];
    	
    	this.loadScript(
			activeScript, 
			function() {
				application.loadScripts(null, successHandler, failureHandler, queue);
			}, 
			function(message) {
				if(!isEmpty(failureHandler)) {
					failureHandler.call(undefined, message);
				} else {
					alert(t('Could not load script %1$s.', activeScript) + ' ' + message);
				}
			}
		);
    },
    
   /**
    * Loads a javascript include file dynamically. This automatically determines
    * the script's load key ID (if necessary fetching it via AJAX first) so it
    * integrates seamlessly into the existing scripts in the page, and also to 
    * avoid loading it multiple times.
    * 
    * @public
    * @param {String} script The script to load, relative to the js folder.
    * @param {Function} [successHandler] Function to call once the script is loaded. Has no arguments.
    * @param {Function} [failureHandler] Function to call if the load fails. Has one argument: the error message.
    */
    loadScript:function(script, successHandler, failureHandler)
    {
    	if(this.isScriptLoaded(script)) {
    		if(!isEmpty(successHandler)) {
    			successHandler.call(undefined);
    		}
    		return;
    	}
    	
    	this._log('Script [' + script + '] | Loading include file dynamically.', 'data');
    	
    	// before we can do any loading, we need to know
    	// the load key of the script.
    	this.getScriptLoadKey(
			script, 
			function(data) {
				application.handle_loadScriptKeyLoaded(script, data.key, data.url, successHandler, failureHandler);
			},
			function(errorText) {
				application._log('Script [' + script + '] | Failed to retrieve the load key.', 'event');
				
				if(!isEmpty(failureHandler)) {
					failureHandler.call(undefined, t('Failed to retrieve the load key.') + t('Native message:') + errorText);
				} else {
					application.dialogErrorMessage(
						t('Failed to retrieve the load key.'), 
						t('Native message:') + errorText, 
						application.ERROR_FAILED_TO_RETRIEVE_SCRIPT_LOADKEY
					);
				}
			}
		);
    },
    
   /**
    * Called when the load key for the script to load dynamically has been 
    * loaded successfully, and the script can be loaded.
    * 
    * @protected
    * @param {String} script
    * @param {String} loadKey
    * @param {String} source The source URL of the script
    * @param {Function} [successHandler]
    * @param {Function} [failureHandler]
    */
    handle_loadScriptKeyLoaded:function(script, loadKey, source, successHandler, failureHandler)
    {
    	var date = new Date();
    	var sourceURL = source+'?ts='+date.getTime();
    
    	this._log('Script [' + script + '] | Fetched load key [' + loadKey + '], now loading the script.', 'event');
    	
    	$.ajax({
		  'url':sourceURL,
		  'dataType':'script',
		  'success': function (data, textStatus, jqXHR) {
			  application._log('Script [' + script + '] | Loaded successfully with load key [' + loadKey + '].', 'event');
              if (!isEmpty(successHandler)) {
                  successHandler.call(undefined);
              }
          },
          'error': function (jqXHR, textStatus, errorThrown) 
          {
              var message = application.getAJAXError(errorThrown);
              if(jqXHR.status == 404) {
            	  message = t('The resource could not be found.');
              }
              
              var error = {
        		  'message':message,
				  'code':application.ERROR_UNHANDLED_AJAX_ERROR,
				  'details':t('An unhandled error occurred.'),
				  'trace':'',
				  'data':null
              };
              
              if (!isEmpty(failureHandler)) 
              {
                  failureHandler.call(undefined, message, null);
              } 
              else 
              {
            	  application.createDialogAJAXError(
            		  error.details, 
        			  error.data, 
        			  error.message, 
        			  error.code
    			  ).Show();
              }
          }
		});
    },
    
   /**
    * Retrieves the unique load key for the specified javascript include file.
    * Checks already loaded scripts first, and if not found, launches an AJAX
    * request to retrieve the key.
    * 
    * @public
    * @param {String} url
    * @param {Function} [successHandler]
    * @param {Function} [failureHandler]
    */
    getScriptLoadKey:function(script, successHandler, failureHandler)
    {
    	var keys = this.getLoadKeys();
    	var found = false;
    	$.each(keys, function(idx, keyDef) {
    		if(keyDef.script==script) {
    			found = keyDef.key;
    		}
    	});
    	
    	if(found) {
    		return found;
    	}
    	
    	this._log('Retrieving the load key for script [' +  script + ']', 'data');
    	
    	application.AJAX(
			'GetScriptLoadKey', 
			{'script':script}, 
			function(data) {
				// cache this result
				application.registerLoadKey(
					data.key,
					data.relative
				);
				
				if(!isEmpty(successHandler)) {
					successHandler.call(undefined, data);
				}
			},
			failureHandler
		);
    },
    
    /**
     * Renders the markup for a spinning load icon and returns it.
     * If a label is set, it will be shown on the right of the icon.
     *
     * @public
     * @param {String} [label]
     * @returns {String}
     */
    renderSpinner: function (label) {
        if (typeof(label) == 'undefined') {
            label = '';
        }

        return UI.Icon().Spinner() + ' ' + label;
    },

    /**
     * Creates the markup for a spinning load icon, and injects it directly
     * into the specified DOM element.
     *
     * @param {DOMElement|String} elementOrID
     * @param {String} [label] Label to display next to the icon
     */
    injectSpinner: function (elementOrID, label) {
        $(elementOrID).html(this.renderSpinner(label));
    },

    /**
     * Displays a modal dialog with a custom message asking
     * the user to confirm his current action. It has Ok
     * and Cancel buttons.
     *
     * If you pass a function to any of the handler parameters,
     * it will be used instead of the default behavior.
     *
     * @param {String} message
     * @param {Function} okHandler
     * @param {Function} [cancelHandler]
     * @param {Function} [shownHandler]
     * @param {Function} [hiddenHandler]
     * @return {Dialog_Confirmation}
     */
    dialogConfirmation: function (message, okHandler, cancelHandler, shownHandler, hiddenHandler) {
        var dialog = this.createConfirmationDialog(message, okHandler)
            .Cancel(cancelHandler)
            .Shown(shownHandler)
            .Hidden(hiddenHandler)
            .Show();

        return dialog;
    },

    /**
     * Creates a new confirmation modal dialog instance and returns it.
     * Does not open the dialog, so it can be configured further before
     * opening it using its API.
     *
     * @param {String} message
     * @param {Function} okHandler
     * @returns {Dialog_Confirmation}
     */
    createConfirmationDialog: function (message, okHandler) {
        return new Dialog_Confirmation()
            .OK(okHandler)
            .SetContent(message);
    },

    /**
     * Same as the regular modal confirm dialog, but requires the user
     * to specify a comment that will be sent along with the form
     * data in the confirm_comments variable.
     *
     * @param message
     * @param data
     * @param okHandler
     * @param cancelHandler
     */
    displayCommentConfirmation: function (message, okHandler, cancelHandler) {
        message +=
            '<p>' +
            '<label for="confirm_comments">' + t('Comments:') + '</label>' +
            '<textarea id="confirm_comments" cols="40" rows="4" onkeyup="application.handler_checkDialogConfirmation()"></textarea>' +
            '<div id="confirm_comments_warning" style="display:none;" class="form_label_error">' + t('Please enter a reason for this change into the comments field.') + '</div>' +
            '</p>';

        this.displayConfirmation(message, okHandler, cancelHandler);

        // trigger a key up event to initialize the dialog validation
        $('#confirm_comments').keyup();
    },

    /**
     * Event handler that checks the comments field in a comment confirm
     * dialog and disables / enables the OK button as needed according to
     * the value of the comments field.
     */
    handler_checkDialogConfirmation: function () {
        var el = $('#confirm_comments');
        if (el.length == 0) {
            return;
        }

        var comments = trim(el.val());
        if (comments.length > 1) {
            $('#dialog_confirm_ok').button('enable');
            $('#dialog_confirm_ok').click(function () {
                $('#main_form').append('<input type="hidden" name="confirm_comments" value="' + $('#confirm_comments').val() + '"/>');
                $('#ui_confirm').data('okHandler')($('#ui_confirm').data('data'));
            });
        } else {
            $('#dialog_confirm_ok').button('disable');
            $('#dialog_confirm_ok').unbind('click');
        }
    },

    addUnit: function (value, label) {
        this.units.push({'value': value, 'label': label});
    },

    'elLoaderDialog': null,

    /**
     * Displays a modal dialog (not dismissable) with a spinning load
     * icon and optional loading text to tell the user what he's waiting for.
     * You can hide it again with the {@link hideLoader} method.
     *
     * @param {String} [loadingText]
     */
    showLoader: function (loadingText) {
        if (typeof(loadingText) == 'undefined') {
            loadingText = '';
        }

        var html =
            '<p class="loader_text">' + loadingText + '</p>' +
            '<div class="loader_spinner">' + this.renderSpinner() + '</div>';

        this.showCustomLoader(html);
    },

    /**
     * Shows a modal dialog (not dismissable) with custom HTML content.
     * Has to be hidden manually using the {@link hideLoader} method.
     *
     * @param {String} content
     */
    showCustomLoader: function (content) {
        if (this.elLoaderDialog == null) {
            this.renderLoaderDialog();
        }

        $('#loader_container').html(content);
        this.elLoaderDialog.modal('show');
    },

    /**
     * Hides the loader modal dialog. Has no effect if it is not active.
     */
    hideLoader: function () {
        if (this.elLoaderDialog != null) {
            this.elLoaderDialog.modal('hide');
        }
    },

    renderLoaderDialog: function () {
        this.elLoaderDialog = DialogHelper.createDialog(
            null,
            '<div id="loader_container"></div>'
        );

        this.elLoaderDialog.modal({
            'keyboard': false,
            'backdrop': 'static',
            'show': false
        });
    },

    /**
     * Retrieves the absolute URL to the specified AJAX method.
     * Note: Does not validate the method name.
     *
     * @param {String} methodName
     * @param {Object} params
     * @returns {String}
     */
    getAjaxURL: function (methodName, params) {
        if (typeof(params) == 'undefined') {
            params = {};
        }

        params.method = methodName;
        var url = this.url + '/ajax/?';
        var tokens = [];
        $.each(params, function (name, value) {
            tokens.push(name + '=' + value);
        });

        url += tokens.join('&');

        return url;
    },
    
    getBaseURL:function()
    {
    	return this.url;
    },

    getAppName:function()
    {
    	return this.appName;
    },
    
    getAppNameShort:function()
    {
    	return this.appNameShort;
    },
    
    getLocaleLabel: function (localeName) {
        if (typeof(this.contentLocales[localeName]) != 'undefined') {
            return this.contentLocales[localeName];
        }

        return null;
    },

    renderContentLangSelector: function (id, activeItem) {
        if (typeof(activeItem) == 'undefined') {
            activeItem = null;
        }

        var html =
            '<select id="' + id + '" class="input-small">';
        $.each(this.contentLocales, function (name, label) {
            var sel = '';
            if (name == activeItem) {
                sel = ' selected="selected"';
            }
            html +=
                '<option value="' + name + '"' + sel + '>' + label + '</option>';
        });
        html +=
            '</select>';

        return html;
    },

    addErrorMessage: function (message, okHandler, failHandler) {
        this.addMessage('error', message, okHandler, failHandler);
    },

    addSuccessMessage: function (message, okHandler, failHandler) {
        this.addMessage('success', message, okHandler, failHandler);
    },

    addInfoMessage: function (message, okHandler, failHandler) {
        this.addMessage('info', message, okHandler, failHandler);
    },

    addMessage: function (type, message, okHandler, failHandler) 
    {
        var payload = {
            'application_locale': application.locale,
            'type': type,
            'message': message
        };
        
        var ajax = this.createAJAX('AddMessage')
        .SetPayload(payload);
        
        if(!isEmpty(okHandler)) {
        	ajax.Success(okHandler);
        }
        
        if(!isEmpty(failHandler)) {
        	ajax.Failure(failHandler);
        } 
        
        ajax.Send();
    },

    /**
     * Puts the focus on the specified element. Accepts an element
     * ID or an existing jquery DOM element. Delays the focus in case
     * the element does not exist in the DOM yet.
     *
     * Alias for the FormHelper method.
     *
     * @param {DOMElement|String} elementOrID
     */
    focusField: function (elementOrID) 
    {
    	FormHelper.focusField(elementOrID);
    },

    'cookieExpiry': 90,
    'cookies': null,
    'cookieValues': {},

    /**
     * Gets a user preference setting. These are stored in
     * user-specific cookies.
     *
     * @param {String} name
     * @param {String} [defaultValue=null]
     * @returns {String}
     */
    getPref: function (name, defaultValue) {
        return this.getCookie('pref-' + name, defaultValue);
    },

    /**
     * Sets a user-specific preference setting. These are
     * stored in user-specific cookies.
     *
     * @param {String} name
     * @param {String} value
     */
    setPref: function (name, value) {
        this.setCookie('pref-' + name, value);
    },

    /**
     * Sets a cookie value.
     *
     * @param {String} name
     * @param {String} value
     */
    setCookie: function (name, value) 
    {
        name = 'spin_' + name;
        
    	// if local storage is available, use that
    	if(typeof(localStorage) != 'undefined') 
    	{
    		localStorage.setItem(name, value);
    	}
    	// otherwise, use the traditional cookie implementation
    	else 
    	{
        var date = new Date();
        date.setTime(date.getTime() + (this.cookieExpiry * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();

        document.cookie = name + "=" + value + expires + "; path=" + this.url;
    	}

        // cache the value for the request
        this.cookieValues[name] = value;
    },

    /**
     * Gets the value of a previously set cookie.
     *
     * @param {String} name
     * @param {String} [defaultValue=null] The value to return if the cookie is not set.
     * @return {String}
     */
    getCookie: function (name, defaultValue) 
    {
        name = 'spin_' + name;
    	
        // use the value cached during this request
        if (typeof(this.cookieValues[name]) != 'undefined') {
            return this.cookieValues[name];
        }

        // get from local storage if that's available
        if(typeof(localStorage) != 'undefined') 
        {
        	return localStorage.getItem(name);
        }
        // otherwise, use the traditional cookie implementation
        else
    	{
        this.readCookies();

        if (typeof(this.cookies[name]) != 'undefined') {
            return this.cookies[name];
        }

        if (typeof(defaultValue) != 'undefined') {
            return defaultValue;
        }
    	}

        return null;
    },

    readCookies: function () {
        if (this.cookies !== null) {
            return;
        }

        var list = {};
        var items = document.cookie.split('; ');
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var sepPos = item.indexOf('=');
            var name = item.substring(0, sepPos);
            if (name.substring(0, 4) != 'spin') {
                continue;
            }

            var value = item.substring(sepPos + 1);

            list[name] = value;
        }

        this.cookies = list;
    },

    /**
     * Retrieves the common text that is used in case an AJAX request fails
     * likely because of a network error.
     *
     * @return string
     */
    getTextTemporaryNetworkError: function () {
        return t('You can try again, it may just have been a temporary network error.');
    },

    'elDialogFeedback': null,

    /**
     * Opens the dialog for users to add feedback on the application.
     *
     * @param {String} feedback_type The feedback type to pre-select, [improvement, bug, other]
     */
    dialogFeedback: function (feedback_type) {
        if (this.elDialogFeedback == null) {
            this.renderFeedbackDialog();
        }

        if (typeof(feedback_type) == 'undefined') {
            feedback_type = 'improvement';
        }

        $('#f_feedback_type').val(feedback_type);
        $('#f_feedback_scope').val('page');
        $('#f_feedback').val('');

        this.elDialogFeedback.modal('show');
    },

    renderFeedbackDialog: function () {
        var body = '';
        var types = {
            'improvement': t('Improvement suggestion'),
            'bug': t('Bug report'),
            'other': t('Other')
        };

        var scopes = {
            'page': t('This specific page'),
            'application': t('%1$s as a whole', this.appNameShort)
        };

        body = '' +
        '<form class="form-horizontal" onsubmit="return application.handle_submitFeedbackDialog();">' +
        FormHelper.renderItem(
            t('Regarding'),
            'f_feedback_type',
            FormHelper.renderSelect(
                'f_feedback_type',
                types
            )
        ) +
        FormHelper.renderItem(
            t('Concerns'),
            'f_feedback_scope',
            FormHelper.renderSelect(
                'f_feedback_scope',
                scopes
            )
        ) +
        FormHelper.renderItem(
            t('Comments'),
            'f_feedback',
            '<textarea id="f_feedback" rows="8" class="input-xlarge">' +
            '</textarea>'
        ) +
        FormHelper.renderDummySubmit() +
        '</form>';

        var footer = '' +
            DialogHelper.renderButton_primary(
                UI.Icon().Feedback() + ' ' + t('Send feedback'),
                'application.handle_submitFeedbackDialog()',
                t('Please wait, sending...'),
                'btn_feedback_send'
            ) +
            DialogHelper.renderButton_close(t('Cancel'));

        this.elDialogFeedback = DialogHelper.createDialog(
            t('Feedback on %1$s', this.appNameShort),
            body,
            footer
        );

        this.elDialogFeedback.on('shown', function () {
            $('#f_feedback').focus();
        });
    },

    handle_submitFeedbackDialog: function () {
        if (this.feedbackFormLocked) {
            return false;
        }

        var isValid = true;

        var feedback = trim($('#f_feedback').val());
        if (feedback.length < 6) {
            FormHelper.makeError('f_feedback', t('Please enter a comment.'));
            isValid = false;
        } else {
            FormHelper.resetErrorStatus('f_feedback');
        }

        if (!isValid) {
            return false;
        }

        this.lockFeedbackForm();

        var payload = {
            'scope': $('#f_feedback_scope').val(),
            'type': $('#f_feedback_type').val(),
            'feedback': feedback,
            'url': window.location.href.toString()
        };

        $.ajax({
            'dataType': 'json',
            'url': application.getAjaxURL('AddFeedback'),
            'data': payload,
            'success': function (data, textStatus, jqXHR) {
                application.handle_feedbackSuccess(data);
            },
            'error': function (jqXHR, textStatus, errorThrown) {
                application.handle_feedbackFailure(errorThrown);
            },
            'complete': function () {
                application.unlockFeedbackForm();
            }
        });

        return false;
    },

    handle_feedbackSuccess: function (data) {
        var message = '';

        switch (data.feedback_type) {
            case 'bug':
                message = t('Thank you for your bug report, it is pending review.') + ' ' +
                t('We appreciate your taking the time to report this issue.');
                break;

            case 'improvement':
                message = t('Thank you for your suggestions, they will be taken into consideration in the ongoing %1$s development.', this.appNameShort);
                break;

            default:
                message = t('Thank you for your thoughts, if need be we will contact you.');
                break;
        }

        var number = '<b>' + data.feedback_id + '</b>';
        this.elDialogFeedback.modal('hide');
        this.dialogMessage(
            '<div class="alert alert-success">' +
            UI.Icon().Feedback() + ' ' +
            t('Your feedback was sent successfully.') +
            '</div>' +
            '<p>' + message + '</p>' +
            '<p>' + t('Your ticket number: %1$s', number) + '</p>',
            t('%1$s feedback', this.appNameShort)
        );
    },

    handle_feedbackFailure: function (errorText) {
        this.elDialogFeedback.modal('hide');

        this.createDialogAJAXError(
            errorText,
            {},
            t('Could not send feedback.'),
            this.ERROR_SENDING_FEEDBACK_FAILED
        ).Show();
    },

    'feedbackFormLocked': false,

    lockFeedbackForm: function () {
        this.feedbackFormLocked = true;
        $('#btn_feedback_send').button('loading');
    },

    unlockFeedbackForm: function () {
        this.feedbackFormLocked = false;
        $('#btn_feedback_send').button('reset');
    },

    MESSAGE_TYPE_INFO: 'info',
    MESSAGE_TYPE_ERROR: 'error',
    MESSAGE_TYPE_SUCCESS: 'success',

    /**
     * Displays an error message/alert just like the serverside
     * added user messages. The message is appended to existing
     * messages, and is dismissable by default.
     *
     * @param string type
     * @param string message
     * @param boolean dismissable
     */
    displayMessage: function (type, message, dismissable) {
        var html = application.renderAlert(type, message, dismissable);
        var container = $('#messages-container');

        container.append(html);
        UI.ScrollToElement(container);
    },

    /**
     * Clears all messages that are still being shown in the messages
     * area. Call this before adding a new message if you want to avoid
     * messages being stacked.
     */
    clearMessages: function () {
        $('#messages-container .alert').hide(500);
    },

    /**
     * Displays an error message/alert just like the serverside
     * added user messages. The message is appended to existing
     * messages, and is dismissable by default.
     *
     * @param {String} message
     * @param {Boolean} [dismissable=true]
     */
    displayErrorMessage: function (message, dismissable) {
        this.displayMessage(
            this.MESSAGE_TYPE_ERROR,
            message,
            dismissable
        );
    },

    /**
     * Displays a success message/alert just like the serverside
     * added user messages. The message is appended to existing
     * messages, and is dismissable by default.
     *
     * @param {String} message
     * @param {Boolean} [dismissable=true]
     */
    displaySuccessMessage: function (message, dismissable) {
        this.displayMessage(
            this.MESSAGE_TYPE_SUCCESS,
            message,
            dismissable
        );
    },

    /**
     * Displays an informational message/alert just like the serverside
     * added user messages. The message is appended to existing
     * messages, and is dismissable by default.
     *
     * @param {String} message
     * @param {Boolean} [dismissable=true]
     */
    displayInfoMessage: function (message, dismissable) {
        this.displayMessage(
            this.MESSAGE_TYPE_INFO,
            message,
            dismissable
        );
    },

    /**
     * Renders an error-styled alert message box and returns the HTML markup for it.
     *
     * @param {String} message Can contain HTML.
     * @param {Boolean} [dismissable=false]
     * @returns {String}
     */
    renderAlertError: function (message, dismissable) {
        return this.renderAlert('error', message, dismissable);
    },

    /**
     * Renders an information-styled alert message box and returns the HTML markup for it.
     *
     * @param {String} message Can contain HTML.
     * @param {Boolean} [dismissable=false]
     * @returns {String}
     */
    renderAlertInfo: function (message, dismissable) {
        return this.renderAlert('info', message, dismissable);
    },

    /**
     * Renders an success-styled alert message box and returns the HTML markup for it.
     *
     * @param {String} message Can contain HTML.
     * @param {Boolean} [dismissable=false]
     * @returns {String}
     */
    renderAlertSuccess: function (message, dismissable) {
        return this.renderAlert('success', message, dismissable);
    },

    /**
     * Renders a warning-styled alert message box and returns the HTML markup for it.
     *
     * @param {String} message Can contain HTML.
     * @param {Boolean} [dismissable=false]
     * @returns {String}
     */
    renderAlertWarning: function (message, dismissable) {
        return this.renderAlert('warning', message, dismissable);
    },
    
    'alertTypes': ['error', 'info', 'success', 'warning'],

    renderAlert: function (type, message, dismissable) {
        if (typeof(dismissable) == 'undefined') {
            dismissable = false;
        }

        if (!this.isValidAlertType(type)) {
            this._log('Invalid alert type [' + type + ']', 'error');
            type = 'info';
        }

        var html = '' +
            '<div class="alert alert-' + type + '">';
        if (dismissable) {
            html += '' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        }
        html += '' +
        message +
        '</div>';

        return html;
    },

    isValidAlertType: function (type) {
        for (var i = 0; i < this.alertTypes.length; i++) {
            if (this.alertTypes[i] == type) {
                return true;
            }
        }

        return false;
    },

    /**
     * Renders an error-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelError: function (message, title) {
        return this.renderLabelOrBadge('label', 'important', message, title);
    },

    /**
     * Renders an information-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelInfo: function (message, title) {
        return this.renderLabelOrBadge('label', 'info', message, title);
    },

    /**
     * Renders a success-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelSuccess: function (message, title) {
        return this.renderLabelOrBadge('label', 'success', message, title);
    },

    /**
     * Renders an inactive-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelInactive: function (message, title) {
        return this.renderLabelOrBadge('label', 'default', message, title);
    },

    /**
     * Renders a warning-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelWarning: function (message, title) {
        return this.renderLabelOrBadge('label', 'warning', message, title);
    },

    /**
     * Renders a blocked-styled label and returns the HTML markup for it.
     *
     * @param {String} message
     * @param {String} [title] Optional title attribute for the hover tooltip.
     * @returns {String}
     */
    renderLabelBlocked: function (message, title) {
        return this.renderLabelOrBadge('label', 'inverse', message, title);
    },

    renderBadgeError: function (message, title) {
        return this.renderLabelOrBadge('badge', 'important', message, title);
    },
    renderBadgeInfo: function (message, title) {
        return this.renderLabelOrBadge('badge', 'info', message, title);
    },
    renderBadgeSuccess: function (message, title) {
        return this.renderLabelOrBadge('badge', 'success', message, title);
    },
    renderBadgeInactive: function (message, title) {
        return this.renderLabelOrBadge('badge', 'default', message, title);
    },
    renderBadgeWarning: function (message, title) {
        return this.renderLabelOrBadge('badge', 'warning', message, title);
    },
    renderBadgeBlocked: function (message, title) {
        return this.renderLabelOrBadge('badge', 'inverse', message, title);
    },

    /**
     * Creates and returns a badge or label instance that can be
     * customized further. Convertable to string.
     *
     * @param {String} tagType Either "badge" or "label"
     * @param {String} type The layout variation type, e.g. "success", "warning", etc. Default is "default"
     * @param {String} content The content of the badge or label
     * @param {String} [title] Optional title, will be used as tooltip for the badge or label
     * @returns {UI_Label}
     */
    renderLabelOrBadge: function (tagType, type, content, title) {
        var label = new UI_Label(content);
        label.SetType(tagType);
        label.SetVariant(type);

        if (typeof(title) != 'undefined' && title != null) {
            label.SetTooltip(title);
        }

        return label;
    },

    /**
     * Displays a modal dialog with an error message.
     *
     * @param {String} errorDescription Short description of the error
     * @param {String} errorReasons Extended explanation and possible reasons for the error
     * @param {Integer} [errorCode] Optional error code to display
     */
    dialogErrorMessage: function (errorDescription, errorReasons, errorCode) {
        this.createDialogErrorMessage(errorDescription, errorReasons, errorCode).Show();
    },

    /**
     * Creates a modal dialog with an error message, without displaying it.
     *
     * @param {String} errorDescription Short description of the error
     * @param {String} errorReasons Extended explanation and possible reasons for the error
     * @param {Integer} [errorCode] Optional error code to display for easy identification
     * @return {Dialog_Generic}
     */
    createDialogErrorMessage: function (errorDescription, errorReasons, errorCode) {
        var label = t('Error:');
        if (typeof(errorCode) != 'undefined') {
            label = t('Error %s:', '#' + errorCode);
        }

        var content = '' +
        '<p>' +
        	'<b class="text-error">' + label + '</b> <b>' + errorDescription + '</b> ' +
        '</p>';
        
        if(!isEmpty(errorReasons)) {
        	content += ''+
            '<p>' +
            	errorReasons +
            '</p>';
        }

        var dialog = this.createGenericDialog(t('An error occurred.'), content);
        
        dialog
        .SetIcon(UI.Icon().Warning())
        .MakeDangerous()
        .EnablePageDetails()
        .Shown(function() {
        	application.closeAllDialogs(true, [dialog]);
        });
        
        return dialog;
    },

    /**
     * Creates an error dialog with details about an AJAX request failure.
     * If the user is a developer, additional information is shown if
     * available in the data from the response. The dialog is already fully
     * configured, but can be customized further as needed.
     *
     * @param {String} errorText The error message as provided by the AJAX call
     * @param {Object} data The additional data as provided by the AJAX call
     * @param {String} message The main error message to show (should be concise)
     * @param {Integer} [errorCode] The code of the error for easy identification
     * @param {Function} [retryHandler] If present, adds a "Retry" button that if clicked, closes the dialog and calls this callback function 
     * @return {Dialog_Generic}
     */
    createDialogAJAXError: function (errorText, data, message, errorCode, retryHandler) 
    {
    	var errorID = '';
        var details = [];
        
        if(!isEmpty(errorText)) {
        	details.push('<p>'+errorText+'</p>');
        }
        
        var isException = !isEmpty(data) && typeof(data.isExceptionData) != 'undefined'; 
        
        if(isException) 
        {
        	details.push('<p>' + t('This error has been logged on the server.') + '</p>');
        	details.push('<p>' + t('If you wish to contact the developers regarding this issue, please provide the following information:')+'</p>');
        	details.push(
    			'<table class="table">'+
    				'<tbody>'+
    					'<tr>'+
	    					'<th width="18%" class="align-right">'+t('Error ID')+'</th>'+
	    					'<td style="font-family:monospace">'+data.eid+'</td>'+
    					'</tr>'+
    					'<tr>'+
    						'<th class="align-right">'+t('Environment')+'</th>'+
    						'<td>'+application.host+':'+application.environment+' v'+Driver.version+'</td>'+
    					'</tr>'+
    				'</tbody>'+
    			'</table>'
        	);
        }
        
    	if(details.length == 0) {
    		details = '<span class="muted">('+t('No additional information available')+')</span>';
    	} else {
    		details = details.join(''); 
    	}
        
        var dialog = application.createDialogErrorMessage(
            message,
            details,
            errorCode
        );
        
        if(!isEmpty(retryHandler)) {
        	dialog.AddButton(
    			UI.Button(t('Try again'))
    			.SetIcon(UI.Icon().Refresh())
    			.MakeSuccess()
    			.Click(function() {
    				dialog.Hide();
					retryHandler.call(dialog);
				})
        	);
        	dialog.AddButtonClose();
        }

        return dialog;
    },

    preventFormSubmission: function (reason) 
    {
        FormHelper.preventSubmission(reason);
    },

    allowFormSubmission: function () 
    {
        FormHelper.allowSubmission();
    },

    shakeElement: function (elementID, delay) {
        if (typeof(delay) == 'undefined') {
            delay = 5000;
        }

        setTimeout(function () {
            application.handle_shakeElement(elementID, delay);
        }, delay);
    },

    handle_shakeElement: function (elementID, delay) {
        $('#' + elementID).effect('shake', {'direction': 'up', 'distance': 5, 'times': 5});
        setTimeout(function () {
            application.handle_shakeElement(elementID, delay);
        }, delay);
    },

    renderButtonDeveloper: function (label, statement) {
        return '' +
            '<button class="btn btn-developer" onclick="' + statement + '">' +
            '<b>' + t('DEV:') + '</b> ' + label +
            '</button>';
    },

    getAJAXError: function (ajaxErrorThrown) 
    {
        var message = ajaxErrorThrown;
        if (typeof(ajaxErrorThrown) == 'object') {
            message = ajaxErrorThrown.message;
        }

        return message;
    },

    /**
     * Sends an AJAX request to an application AJAX method. Calls the
     * specified handler functions when specified.
     *
     * The success handler gets these parameters: 
     * 1) the returned data payload
     * 
     * The failure handler gets these parameters:
     * 1) The error message
     * 2) The error object
     * 
     * The error objet has the following keys:
     * - message - The error message
     * - code - The error code, if any
     * - details - Detailed information, if any
     * - trace - Error trace in the case of an exception 
     * - data - Additional data the error specified, if any
     *
     * @param {String} methodName
     * @param {Object} payload
     * @param {Function} [successHandler]
     * @param {Function} [failureHandler]
     * @param {Boolean} [simulation=false]
     * @see application.createAJAX
     */
    AJAX: function (methodName, payload, successHandler, failureHandler, simulation)
    {
    	return this.createAJAX(methodName)
    	.SetPayload(payload)
    	.Success(successHandler)
    	.Failure(failureHandler)
    	.Simulate(simulation)
    	.Send();
    },
    
   /**
    * Creates a new AJAX helper class instance.
    * 
    * @param {String} methodName
    * @returns {Application_AJAX}
    */
    createAJAX:function(methodName)
    {
    	return new Application_AJAX(methodName);
    },

	registerLoadKey:function(loadKey, script)
	{
		this.loadKeys[loadKey] = script;
	
		this.log('Resource Manager', 'Registered resource ['+script+'] with key ['+loadKey+'].');
	},

    'loadKeys':{},

   /**
    * Retrieves the load keys for all client scripts (javascript and css)
    * that have been loaded in this page.
    *
    * @returns {Array} Indexed array with objects containing two keys: <code>key</code> and <code>script</code>.
    */
    getLoadKeys:function()
    {
		return this.loadKeys;
    },
    
   /**
    * Retrieves an indexed array with load key ids.
    * @returns {Array}
    */
    getLoadKeyIDs:function()
    {
    	var keys = this.getLoadKeys();

    	var ids = [];
    	$.each(keys, function(key, script) {
    		ids.push(key);
    	});

		ids.sort();
    	
    	return ids;
    },

    /**
     * Creates an AJAX instance to retrieve HTML content.
     *
     * @param {String} methodName The Ajax method name
     * @param {Object} payload
     * @param {Function} successHandler Gets the HTML code as parameter. 
     * @return {Application_AJAX}
     */
    createFetchHTML:function(methodName, payload, successHandler)
    {
    	var ajax = application.createAJAX(methodName)
    	.MakeHTML()
    	.SetAsync(true)
    	.SetPayload(payload)
    	.Success(function(data) 
		{
    		successHandler.call(undefined, data);
    	});
    	
    	return ajax;
    },
    
    /**
     * Like the AJAX method, but retrieves HTML content.
     *
     * @param {String} methodName The Ajax method name
     * @param {Object} payload
     * @param {Function} successHandler
     * @param {Function} [failureHandler]
     * @return {Application_AJAX}
     */
    fetchHTML:function(methodName, payload, successHandler, failureHandler)
    {
    	var ajax = this.createFetchHTML(methodName, payload, successHandler);
    	
    	if(!isEmpty(failureHandler)) {
    		ajax.Failure(failureHandler);
    	}
    	
    	ajax.Send();
    	
    	return ajax;
    },

    _log: function (message, category) {
        this.log('Application', message, category);
    },

    /**
     * Adds a clientside logging message that is logged to the
     * browser's console.
     *
     * @param {String} source The source of the logging message, e.g. "Application"
     * @param {String} message The actual logging message
     * @param {String} [category=misc] Possible values: misc, ui, data, error, event, debug
     * 
     * @see setUp()
     */
    log: function (source, message, category) 
    {
        // log data types other than strings
        if (typeof(message) != 'string') {
            console.log(message);
            return;
        }

        if (typeof(category) == 'undefined') {
            category = 'misc';
        }

        if (!application.isLoggingActive(category)) {
            return;
        }

        var indent = '';
        if (this.logIndent > 0) {
            indent = Array(this.logIndent + 1).join('  ');
        }

        var text = indent + category.toUpperCase() + ' | ' + source + ' | ' + message;
        
        if(category=='error') {
        	console.error(text);
        	return;
        }
        
        console.log(text);
    },

	logUI:function(source, message)
	{
		this.log(source, message, 'ui');
	},

	logEvent:function(source, message)
	{
		this.log(source, message, 'event');
	},
	
	logData:function(source, message)
	{
		this.log(source, message, 'data');
	},
	
	logError:function(source, message)
	{
		this.log(source, message, 'error');
	},

    'logIndent': 0,

    /**
     * Starts a new logging section: all log entries coming after
     * this will be indented to visually nest them within this
     * log entry. Logging sections can be nested as many times as
     * you like, just make sure you close them properly - no errors
     * are triggered if you don't.
     *
     * For the message categories, see the init() method where all
     * available categories are registered.
     *
     * @param {String} source The source of the logging message, e.g. "Application"
     * @param {String} message The actual logging message
     * @param {String} [category=misc] The category for the message, this can be any of the following: misc, ui, data, error, event, debug
     */
    logStart: function (source, message, category) {
        this.log(source, message, category);
        this.logIndent++;
    },

    /**
     * Closes a previously opened logging section: restores the
     * previous indenting level.
     *
     * For the message categories, see the init() method where all
     * available categories are registered.
     *
     * @param {String} source The source of the logging message, e.g. "Application"
     * @param {String} message The actual logging message
     * @param {String} [category=misc] The category for the message, this can be any of the following: misc, ui, data, error, event, debug
     */
    logEnd: function (source, message, category) {
        this.logIndent--;
        if (this.logIndent < 0) {
            this.logIndent = 0;
        }

        this.log(source, message, category);
    },

    /**
     * Formats the current time in a HH:ii:ss format.
     *
     * @returns {String}
     */
    getCurrentTime: function () {
        var date = new Date();

        var time = '' +
            sprintf('%02d', date.getHours()) +
            ":" +
            sprintf('%02d', date.getMinutes()) +
            ":" +
            sprintf('%02d', date.getSeconds());

        return time;
    },

    handle_errorThrown: function (err) {
        if (err instanceof ApplicationException) {
            err.Display();
            return;
        }

        throw err;
    },

    createGenericDialog: function (title, content) {
        return new Dialog_Generic(title, content);
    },

    keepAliveInterval: null,

    /**
     * Sends AJAX requests regularly to keep the session alive.
     * This can be useful when the user stays on a single page for a long time.
     */
    keepAlive: function () {
        if (!this.keepAliveInterval) {
            var interval = 1000 * 60 * 2; // every 2 minutes
            this.keepAliveInterval = window.setInterval(application.handle_keepAlive, interval);
        }
    },

    handle_JavaScriptError:function() 
    {
        window.onerror = function(errorMsg, url, lineNumber, column, errorObj) 
        {
        	var code = 0;
        	var type = 'Error';
        	var details = '';
        	
        	if(!isEmpty(errorObj))
    		{
        		type = 'Exception';
        		
        		if(errorObj instanceof ApplicationException) 
            	{
            		code = errorObj.GetCode();
            		type = 'ApplicationException';
            		details = errorObj.GetDeveloperInfo();
            		errorMsg = errorObj.GetMessage();
            	}
    		}
        	
            application.log('A javaScript error occurred: '+errorMsg, 'error');

            var payload = {
        		'code':code,
        		'type':type,
                'message': errorMsg,
                'details':details,
                'referer':window.location.href,
                'source': url,
                'line': lineNumber,
                'column': column
            };
            
            $.ajax({
                'dataType': 'json',
                'url': application.getAjaxURL('AddJSErrorLog'),
                'data': payload
            });
        }
    },

    handle_keepAlive: function () {
        application.AJAX('KeepAlive', {});
    },
    
    dialogSaveComments:function(message, confirmHandler)
    {
    	var dialog = this.createDialogSaveComments(message, confirmHandler);
    	dialog.Show();
    },
    
    createDialogSaveComments:function(message, confirmHandler)
    {
    	return new Application_Dialog_SaveComments()
    		.SetMessage(message)
    		.SetConfirmHandler(confirmHandler);
    },
    
    /**
     * Creates a new instance of a batch processor helper class.
     * 
     * @param {String} ajaxMethodName
     * @return {ProgressBar_BatchProcessor}
     */
 	createBatchProcessor:function(ajaxMethodName)
 	{
 		return new ProgressBar_BatchProcessor(ajaxMethodName);
 	},

   /**
    * Creates a new dialog to select items from a list.
    * @return {Dialog_SelectItems}
    */
    createDialogSelectItems:function()
    {
    	return new Dialog_SelectItems();
    },
    
    'dialogs':[],
    
   /**
    * Automatically called by every dialog to register it in
    * the collection.
    * 
    * @param {Dialog_Basic|TabbedDialog} dialog
    */
    registerDialog:function(dialog)
    {
    	this.dialogs.push(dialog);
    },
    
   /**
    * Closes all dialogs currently open in the page.
    * @param {Boolean} force Whether to close even dialogs that are set to prevent closing.
    * @param {Dialog_Basic[]} excludeDialogs Dialogs to exclude from closing.
    */
    closeAllDialogs:function(force, excludeDialogs)
    {
    	this.log('Closing all dialogs | Forced: ['+bool2string(force)+'].', 'ui');
    	
    	if(isEmpty(excludeDialogs)) {
    		excludeDialogs = [];
    	}
    	
    	$.each(this.dialogs, function(idx, dialog) 
		{
    		if(typeof(dialog.IsShown) == 'undefined') {
    			return;
    		}
    		
    		if(!dialog.IsShown()) {
    			return;
    		}
    		
    		if(excludeDialogs.length > 0) {
    			for(var i=0; i<excludeDialogs.length; i++) {
    				if(excludeDialogs[i].GetJSID() == dialog.GetJSID()) {
    					return;
    				}
    			}
    		}
    		
    		if(force==true && !dialog.AllowClosing) {
    			dialog.AllowClosing();
    		}
    		
    		dialog.Hide();
    	});
    },
    
   /**
    * Transliterates the specified string. Uses a serverside script
    * via AJAX to do this.
    * 
    * @param {String} string
    * @param {Function} successHandler
    * @param [{Function}] failureHandler
    * @param [{String}] spaceCharacter
    * @param [{Boolean}] lowercase
    */
    transliterate:function(string, successHandler, failureHandler, spaceCharacter, lowercase)
    {
    	var payload = {
			'string':string,
			'spaceCharacter':spaceCharacter,
			'lowercase':bool2string(lowercase)
    	};
    	
    	var ajax = application.createAJAX('Transliterate')
    	.SetPayload(payload)
    	.Success(function(data) {
    		if(!isEmpty(successHandler)) {
    			successHandler.call(undefined, data.string);
			}
		});
    		
		if(!isEmpty(failureHandler)) {
			ajax.Failure(failureHandler);
		} else {
			ajax.Error(t('Could not transliterate string.'), this.ERROR_TRANSLITERATION_FAILED);
		}
    	
		ajax.Send();
		
    	return this;
    },
    
    refreshRevisionableTitle:function(label, stateBadge, prettyRevision, revision)
    {
    	$('.revisionable-title-label').html(label);
    	$('.revisionable-title-state').html(stateBadge);
    	$('.revisionable-title-prettyrev').html(prettyRevision);
    	$('.revisionable-title-revision').html(revision);
    },
    
    dialogResetUsercache:function()
    {
    	this.createConfirmationDialog(
			'<p>'+
				'<b class="text-warning">'+
					t('This will delete all your %1$s user settings.', this.appNameShort)+
				'</b>'+
			'</p>'+
			'<p>'+
				t('This includes: miscellaneous preferences, current list filters, stored filter presets and more.')+' '+
				t('No critical settings will be deleted.')+' '+
				t('This is usually meant to be used if a %1$s administrator asks you to.', this.appNameShort)+
			'</p>', 
			function() {
				application.redirect(
					{
						'page':'settings',
						'reset-usercache':'yes'
					},
					t('Please wait, resetting...')
				);
			}
		)
		.Show();
    },
    
   /**
    * Called serverside when there has been a new release of
    * the application that the user has not seen yet.
    */
    registerNewRelease:function()
    {
    	
    }
};
