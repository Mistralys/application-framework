"use strict";

/**
 * Handles simple dialogs: base class for implementing custom
 * dialogs based on this base skeleton. Offers methods and events
 * to make it easy to build simple dialogs.
 *
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
class BaseDialog
{
    /**
     * @param {String} loggingIdentifier
     */
    constructor(loggingIdentifier)
    {
        this.BUTTON_POSITION_LEFT = 'left';
        this.BUTTON_POSITION_RIGHT = 'right';

        this.rendered = false;
        this.rendering = false;
        this.simulate = false;
        this.dialog = null;
        this.footerDisabled = false;
        this.id = nextJSID();
        this.jsID = 'D' + this.id;
        this.elements = new ElementIds(this.jsID);
        this.dangerous = false;
        this.large = false;
        this.abstractText = '';
        this.icon = '';
        this.classes = [];
        this.data = {};
        this.buttons = {};
        this.logger = new Logger(loggingIdentifier);
        this.buttons[this.BUTTON_POSITION_LEFT] = [];
        this.buttons[this.BUTTON_POSITION_RIGHT] = [];
        this.isShown = false;
        this.preventClosing = false;
        this.eventHandlers = {
            'shown':[],
            'closed':[]
        };

        application.registerDialog(this);
    }

    /**
     * Shows the modal dialog, and renders the content as necessary.
     *
     * @public
     * @return {this}
     */
    Show()
    {
        if(!this.rendered) {
            this.log('Show | Not rendered yet, rendering...');
            this.Handle_Render();
            return this;
        }

        this.log('Show | Dialog is ready, showing...');

        // Using $() here to handle cases where bootstrap has been
        // reinitialized in the page (by re-including the js file),
        // which would cause the dialog reference to be invalid.
        //
        // This used to cause a `modal is not a function` error.
        //
        $(this.dialog).modal('show');

        this.HideAlerts();
        this.Handle_Shown();

        application.disallowAutoRefresh('dialogs');

        return this;
    }

    /**
     * @private
     */
    Handle_Render()
    {
        this.Render();

        const self = this;

        UI.RefreshTimeout(function() {
            self.PostRender();
            self.Show();
        });
    }

    /**
     * Hides the modal. Has no effect if it is not shown.
     *
     * @public
     * @return {this}
     */
    Hide()
    {
        this.isShown = false;

        if(this.dialog != null) {
            this.dialog.modal('hide');
        }

        application.allowAutoRefresh('dialogs');

        return this;
    }

    /**
     * Renders the dialog's markup.
     * @private
     */
    Render()
    {
        if(this.rendering) {
            return;
        }

        this.log('Render | Starting the render...');

        this.rendering = true;

        let footer = this.RenderFooter();
        if(this.footerDisabled === true) {
            footer = null;
        }

        this.dialog = DialogHelper.createDialog(
            this.RenderTitle(),
            this.RenderBody(),
            footer,
            {
                'id': this.id
            }
        );

        if(this.dangerous) {
            this.AddClass('modal-danger');
        }

        if(this.large) {
            this.AddClass('modal-large');
        }

        const self = this.dialog;

        $.each(this.classes, function(idx, className) {
            self.addClass(className);
        });

        this.log('Render | Complete.');
    }

    /**
     * @private
     * @returns {String}
     */
    RenderTitle()
    {
        return ''+
            '<span id="'+this.elementID('simulate')+'" class="dialog-simulation-badge">' + this.RenderSimulationBadge() + '</span>'+
            '<span id="' + this.elementID('icon') + '">' +
                this.icon +
            '</span> ' +
            '<span id="' + this.elementID('title') + '">' +
                this.GetTitle() +
            '</span>';
    }

    /**
     * Sets the title of the dialog. Can be used to set the title
     * before showing the dialog, as well as afterward.
     *
     * @param {String} title
     * @returns {this}
     * @public
     */
    SetTitle(title)
    {
        this.title = title;

        if(this.IsReady()) {
            this.element('title').html(title);
        }

        return this;
    }

    /**
     * Checks whether the dialog is ready, i.e., whether rendering is
     * done, and it can be modified further.
     *
     * @returns {Boolean}
     * @public
     */
    IsReady()
    {
        return this.rendered;
    }

    /**
     * Turns off the footer of the dialog.
     *
     * @public
     * @return {this}
     */
    DisableFooter()
    {
        this.footerDisabled = true;
        return this;
    }

    /**
     * Retrieves the title of the dialog. If the extending class
     * does not implement the {@link _GetTitle()} method, the
     * application's name is used instead.
     *
     * @return {String}
     * @see _GetTitle
     * @public
     */
    GetTitle()
    {
        if(this.title != null) {
            return this.title;
        }

        let title = this._GetTitle();
        if(!isEmpty(title)) {
            return title;
        }

        return application.appName;
    }

    /**
     * @return {String|null} If `null`, the title set via the {@link BaseDialog.SetTitle} method is used, and the application's name as fallback.
     * @protected
     * @abstract
     */
    _GetTitle()
    {
        return null;
    }

    /**
     * Renders the abstract for the dialog. This is optional,
     * and is only used if the extending class implements the
     * {@link _RenderAbstract} method and returns a valid string.
     *
     * @return {String}
     * @see _RenderAbstract
     * @private
     */
    RenderAbstract()
    {
        let html = this._RenderAbstract();
        if(!isEmpty(html)) {
            html = DialogHelper.renderAbstract(html);
        } else {
            html = '';
            if(this.abstractText !== '') {
                html = DialogHelper.renderAbstract(this.abstractText);
            }
        }

        return '<div id="' + this.elementID('abstract') + '">' + html + '</div>';
    }

    /**
     * @protected
     * @abstract
     * @returns {String|null}
     */
    _RenderAbstract()
    {
        return null;
    }

    /**
     * Sets the abstract text to display. Can be used after the
     * dialog has been rendered to replace the existing abstract,
     * add one if there was none.
     *
     * @param {String} text
     * @return {this}
     */
    SetAbstract(text)
    {
        this.abstractText = text;

        if(this.IsReady()) {
            if(isEmpty(text)) {
                this.element('abstract').hide();
            } else {
                this.element('abstract')
                    .show()
                    .html(DialogHelper.renderAbstract(text));
            }
        }

        return this;
    }

    /**
     * @param {UI_Icon|null} icon
     * @return {this}
     * @public
     */
    SetIcon(icon)
    {
        if(this.IsReady()) {
            let content = '';
            if(icon !== null) {
                content = icon.Render();
            }

            this.element('icon').html(content);
        }

        this.icon = icon;
        return this;
    }

    /**
     * @return {UI_Icon|null}
     * @public
     */
    GetIcon()
    {
        return this.icon;
    }

    /**
     * Renders the body markup for the dialog. The extending class
     * needs to implement the {@link _RenderBody} method to build
     * the markup.
     *
     * @return {String}
     * @see _RenderBody
     * @private
     */
    RenderBody()
    {
        let body = this._RenderBody();
        if(typeof(body) == 'undefined' || typeof(body.length) == 'undefined' || !body.length) {
            body = '';
        }

        return ''+
            this.RenderAbstract()+
            '<div id="'+this.elementID('messages')+'"></div>'+
            '<div id="'+this.elementID('body')+'">'+body+'</div>';
    }

    /**
     * @protected
     * @abstract
     * @returns {String}
     */
    _RenderBody()
    {
        return '';
    }

    /**
     * Renders the dialog's footer, i.e., the buttons to show in the
     * footer bar. Returns an HTML markup string. If the class extending
     * this class does not implement the _RenderFooter method, a standard
     * "Close" button will be shown.
     *
     * @private
     * @return {String}
     */
    RenderFooter()
    {
        let html = '' +
            '<div id="'+this.elementID('footer-left')+'" class="modal-footer-left">' +
            this.RenderFooterLeft() +
            '</div>';

        const custom = this._RenderFooter();
        if(!isEmpty(custom)) {
            html += custom;
            return html;
        }

        this._ConfigureButtons();

        if(this.buttons[this.BUTTON_POSITION_RIGHT].length === 0) {
            this.logger.logUI('No buttons on the right side, adding a default close button.');
            this.AddButtonClose();
        }

        html += this.RenderButtons(this.BUTTON_POSITION_RIGHT);

        return html;
    }

    /**
     * Renders the markup for all buttons available in the specified position.
     *
     * @private
     * @param {String} position
     * @returns {String}
     */
    RenderButtons(position)
    {
        if(typeof(this.buttons[position]) === 'undefined' || this.buttons[position].length < 1) {
            this.log('No buttons to render on the ['+position+'] side.');
            return '';
        }

        this.log(this.buttons[position].length + ' buttons to render on the ['+position+'] side.');

        let html = '';
        $.each(this.buttons[position], function(idx, button){
            html += button.Render() + ' ';
        });

        return html;
    }

    /**
     * Renders the left footer content: either the dialog returns its
     * own static HTML code via the _RenderFooterLeft() method, or any
     * buttons that have been added to the left side are used.
     *
     * @private
     * @returns {String}
     */
    RenderFooterLeft()
    {
        const custom = this._RenderFooterLeft();
        if(!isEmpty(custom)) {
            return custom;
        }

        this._ConfigureButtonsLeft();

        return this.RenderButtons(this.BUTTON_POSITION_LEFT);
    }

    /**
     * @protected
     * @abstract
     * @returns {String|null}
     */
    _RenderFooterLeft()
    {
        return null;
    }

    /**
     * Creates and adds a button styled as a primary button.
     * Inherits the dialog's icon if it has been set.
     *
     * @public
     * @param {String} label
     * @param {Function} clickHandler The click handler to use for the button. Has the dialog instance as <code>this</code>.
     * @param {String|null} [position=null]
     * @return {this}
     */
    AddButtonPrimary(label, clickHandler, position=null)
    {
        if(isEmpty(label)) {
            label = t('OK');
        }

        const button = UI.Button(label)
            .MakePrimary();

        if(!isEmpty(this.icon)) {
            let type = this.icon.GetType();
            if(type !== null) {
                button.SetIcon(UI.Icon().SetType(type, this.icon.GetPrefix()));
            }
        }

        const self = this;
        if(!isEmpty(clickHandler)) {
            button.Click(function() {
                clickHandler.call(self);
            });
        }

        return this.AddButton(button, '__primary', position);
    }

    /**
     * If present, will return the button instance for the
     * dialog's primary button (if added via the
     * {@link AddButtonPrimary} method).
     *
     * @return {UI_Button|null}
     */
    GetPrimaryButton()
    {
        return this.GetButton('__primary');
    }

    /**
     * Creates and adds a regular "close" button to the dialog, and returns the button instance.
     * @param {String} [label='Close']
     * @param {String} [name='close']
     * @return {this}
     */
    AddButtonClose(label, name)
    {
        if(isEmpty(label)) {
            label = t('Close');
        }

        if(isEmpty(name)) {
            name = 'close';
        }

        const self = this;

        return this.AddButtonRight(
            UI.Button(label)
                .SetIcon(UI.Icon().Close())
                .Click(function() {
                    self.Hide();
                }),
            name
        );
    }

    /**
     * Creates and adds a regular "cancel" button to the dialog, and returns the button instance.
     * @param {String} [name='cancel'] Optional name which can be used to retrieve the button later
     * @returns {this}
     */
    AddButtonCancel(name)
    {
        if(isEmpty(name)) {
            name = 'cancel';
        }

        return this.AddButtonClose(t('Cancel'), name);
    }

    /**
     * Adds a new button to the footer toolbar.
     * @param {UI_Button} button
     * @param {string} name Optional name which can be used to retrieve the button later
     * @param {String|null} [position=null] On which side the button should be shown.
     * @returns {this}
     */
    AddButton(button, name, position=null)
    {
        if(position !== this.BUTTON_POSITION_LEFT) {
            position = this.BUTTON_POSITION_RIGHT;
        }

        this.log('Adding button ['+button.GetLabel()+'] on the ['+position+'] side.', 'ui');

        if(!isEmpty(name)) {
            button.SetID(this.elementID('btn-'+name));
        }

        this.buttons[position].push(button);
        return this;
    }

    /**
     * @param {UI_Button} button
     * @param {String} name
     * @return {this}
     * @public
     */
    AddButtonRight(button, name)
    {
        return this.AddButton(button, name, this.BUTTON_POSITION_RIGHT);
    }

    /**
     * Retrieves the ID of the dialog, which is unique for
     * each dialog and used in all element IDs to keep them
     * separated from all other elements.
     *
     * @public
     * @returns {String}
     */
    GetJSID()
    {
        return this.jsID;
    }

    /**
     * Retrieves a named dialog button instance (if you specified
     * the name parameter when adding the button).
     *
     * @param {String} name
     * @returns {UI_Button|null}
     * @public
     */
    GetButton(name)
    {
        return UI.GetButton(this.elementID('btn-'+name));
    }

    /**
     * Adds a button on the left-hand side of the dialog's
     * bottom toolbar, instead of the default right side.
     *
     * @param {UI_Button} button
     * @param {String} name Optional name which can be used to retrieve the button later
     * @returns {this}
     * @public
     */
    AddButtonLeft(button, name)
    {
        return this.AddButton(button, name, this.BUTTON_POSITION_LEFT);
    }

    /**
     * @protected
     * @abstract
     * @returns {String|null} If `null`, a standard "Close" button will be shown.
     */
    _RenderFooter()
    {
        return null;
    }

    /**
     * Executes any routines like attaching event handlers once
     * the markup has been rendered and injected into the DOM.
     *
     * @private
     */
    PostRender()
    {
        this.log('Render | Executing post-render tasks...');

        this._PostRender();
        this.rendered = true;
        this.rendering = false;

        const self = this;

        this.dialog.on('hide', function(e) {
            self.Handle_BeforeClose(e);
        });

        this.dialog.on('hidden', function() {
            self.Handle_Closed();
        });

        this._Start();

        this.log('Render | All done.');
    }

    /**
     * This is called when the dialog has finished rendering and
     * post-rendering. Use this to add any routines the dialog needs
     * to do once everything is ready.
     *
     * @abstract
     * @protected
     * @returns {void}
     */
    _Start()
    {
    }

    /**
     * Called when the dialog's markup has been rendered
     * and is accessible in the DOM.
     *
     * @abstract
     * @protected
     * @returns {void}
     */
    _PostRender()
    {
    }

    /**
     * Called when the dialog is being closed. Prevents
     * the dialog from closing if closing is disabled.
     *
     * @private
     * @param {Event} event
     * @see BaseDialog.PreventClosing
     */
    Handle_BeforeClose(event)
    {
        if(this.preventClosing) {
            event.preventDefault();
            event.stopPropagation();
        }
    }

    /**
     * @private
     */
    Handle_Closed()
    {
        this._Handle_Closed();

        if(this.eventHandlers.closed.length > 0) {
            for(let i=0; i<this.eventHandlers.closed.length; i++) {
                this.eventHandlers.closed[i].call(undefined, this);
            }
        }
    }

    /**
     * Called when the dialog has been closed.
     *
     * @abstract
     * @protected
     * @returns {void}
     */
    _Handle_Closed()
    {

    }

    /**
     * Add any buttons in this method that should be shown on the
     * dialog's left (default) side. If none are configured, a
     * close button is added automatically.
     *
     * @abstract
     * @protected
     * @returns {void}
     */
    _ConfigureButtons()
    {

    }

    /**
     * Add any optional utility buttons on the left side of the dialog.
     *
     * @abstract
     * @protected
     * @returns {void}
     */
    _ConfigureButtonsLeft()
    {

    }

    /**
     * Shows an alert message within the dialog, styled for informational messages.
     *
     * @param {String} message
     * @returns {this}
     * @public
     */
    ShowAlertInfo(message)
    {
        return this.ShowAlert('info', message);
    }

    /**
     * Shows an alert message within the dialog, styled for error messages.
     *
     * @param {String} message
     * @returns {this}
     * @public
     */
    ShowAlertError(message)
    {
        return this.ShowAlert('error', message);
    }

    /**
     * Shows an alert message within the dialog, styled for success messages.
     *
     * @param {String} message
     * @returns {this}
     */
    ShowAlertSuccess(message)
    {
        return this.ShowAlert('success', message);
    }

    /**
     * Shows an alert of the specified type.
     *
     * @param {String} type A valid alert type, as supported by the application.renderAlert method, e.g. "success", "error".
     * @param {String} message
     * @returns {this}
     * @public
     */
    ShowAlert(type, message)
    {
        this.HideAlerts();
        this.element('messages').append(application.renderAlert(type, message, true));
        return this;
    }

    /**
     * Hides all alert messages currently shown in the dialog, if any.
     *
     * @public
     * @returns {this}
     */
    HideAlerts()
    {
        $('#'+this.elementID('messages')+' .alert').hide('fast');
        return this;
    }

    /**
     * @private
     */
    Handle_Shown()
    {
        this.isShown = true;

        // fix for clicking an element with a tooltip to open the
        // dialog, which then prevents the tooltip from being closed
        UI.CloseAllTooltips();

        var self = this;

        if(this.eventHandlers.shown.length > 0)
        {
            this.log(sprintf('Shown | Found [%s] event handlers.', this.eventHandlers.shown.length));

            for(var i=0; i<this.eventHandlers.shown.length; i++)
            {
                this.log(sprintf('Shown | - Calling handler [#%s]', i));

                try
                {
                    this.eventHandlers.shown[i].call(undefined, self);
                }
                catch (e)
                {
                    this.log('Shown | - Error calling handler: ' + e.message);
                    console.log(this.eventHandlers.shown[i]);
                }
            }
        }
    }

    /**
     * Called when the dialog has been shown.
     *
     * @protected
     * @abstract
     * @returns {void}
     */
    _Handle_Shown()
    {
    }

    /**
     * Changes the layout of the dialog to signify that the action being
     * confirmed is a potentially dangerous operation and needs to be
     * reviewed carefully.
     *
     * @return {this}
     */
    MakeDangerous()
    {
        this.dangerous = true;

        if(this.IsReady()) {
            this.dialog.addClass('modal-danger');
        }

        return this;
    }

    /**
     * Adds a class to the dialog's main container.
     *
     * @param {String} className
     * @return {this}
     */
    AddClass(className)
    {
        if(!in_array(className, this.classes, true)) {
            this.classes.push(className);
        }

        if(this.IsReady()) {
            this.dialog.AddClass(className);
        }

        return this;
    }

    /**
     * Changes the layout of the dialog to be larger to allow
     * more content inside.
     *
     * @returns {this}
     */
    MakeLarge()
    {
        this.large = true;

        if(this.IsReady()) {
            this.dialog.addClass('modal-large');
        }

        return this;
    }

    /**
     * Replaces the current body of the dialog with the specified HTML markup.
     *
     * NOTE: This will not have any effect if the dialog has not been rendered yet.
     * Use {@link IsReady()} to check beforehand.
     *
     * @param {String} html
     * @return {this}
     */
    ChangeBody(html)
    {
        if(!this.IsReady()) {
            return this;
        }

        this.element('body').html(html);

        const self = this;
        UI.RefreshTimeout(function() {
            self.PostChangeBody();
        });

        return this;
    }

    /**
     * @private
     */
    PostChangeBody()
    {
        this._PostChangeBody();
    }

    /**
     * Called when the body of the dialog has been changed.
     * @abstract
     * @protected
     */
    _PostChangeBody()
    {

    }

    /**
     * @param {String|null} [name=null]
     * @returns {jQuery}
     * @protected
     * @throws ApplicationException
     */
    element(name=null)
    {
        return this.elements.RequireElement(name);
    }

    /**
     * @param {String|null} [name=null]
     * @returns {String}
     * @protected
     */
    elementID(name=null)
    {
        return this.elements.GetID(name);
    }

    /**
     * @protected
     */
    log(message, category)
    {
        application.log('Dialog [' + this.jsID + ']', message, category);
    }

    /**
     * Adds an event handler to the "shown" event of the dialog.
     * This gets the dialog instance as sole parameter.
     *
     * @param {Function} handler
     * @return {this}
     */
    Shown(handler)
    {
        return this.SetEventHandler('shown', handler);
    }

    /**
     * Sets an event handling function for the specified event.
     *
     * @protected
     * @param {String} eventName
     * @param {Function} handler
     * @returns {this}
     */
    SetEventHandler(eventName, handler)
    {
        if(!isEmpty(handler)) {
            this.eventHandlers[eventName].push(handler);
        }

        return this;
    }

    /**
     * Adds an event handler that gets called when the dialog
     * is hidden/closed.
     *
     * @param {Function} handler
     * @returns {this}
     */
    Hidden(handler)
    {
        return this.SetEventHandler('closed', handler);
    }

    /**
     * Prevents the dialog from being closed by any of the
     * buttons and/or shortcut keys.
     *
     * @return {this}
     */
    PreventClosing()
    {
        this.preventClosing = true;
        return this;
    }

    /**
     * Allows the dialog to be closed after having been
     * set to prevent closing.
     *
     * @return {this}
     */
    AllowClosing()
    {
        this.preventClosing = false;
        return this;
    }

    /**
     * Destroys the dialog: removes it from the DOM.
     *
     * Warning: using the dialog instance after this is
     * still possible, but will cause errors.
     *
     * @return {this}
     */
    Destroy()
    {
        $('#'+this.jsID).remove();
        return this;
    }

    /**
     * Sets whether the simulation mode should be activated.
     * Note that this does not do anything on its own: the dialog
     * has to use this functionality for it to have any effect.
     *
     * @param {Boolean} simulate
     * @returns {this}
     * @see BaseDialog.IsSimulationActive
     */
    SetSimulation(simulate)
    {
        if(this.simulate === simulate) {
            return this;
        }

        this.simulate = simulate === true;

        this.log('Set simulation mode to ['+bool2string(this.simulate)+']');

        if(this.IsReady()) {
            this.elements.RequireElement('simulate').html(this.RenderSimulationBadge());
        }

        return this;
    }

    /**
     * @return {string|UI_Label}
     */
    RenderSimulationBadge()
    {
        if(this.IsSimulationActive()) {
            return application.renderBadgeWarning(t('Simulation'), t('Simulation mode is enabled.'));
        }

        return '';
    }

    /**
     * Checks whether the dialog's simulation mode is active.
     * @returns {Boolean}
     * @see BaseDialog.SetSimulation
     */
    IsSimulationActive()
    {
        return this.simulate;
    }

    /**
     * Set a custom data value: this simply allows storing
     * custom data in the dialog. It has no other functionality.
     *
     * @param {String} key
     * @param {*} value
     * @return {this}
     */
    SetData(key, value)
    {
        this.data[key] = value;
        return this;
    }

    /**
     * Retrieves the value of a previously set data value, or
     * the default value if it does not exist.
     * @param {String} key
     * @param {*} [defaultValue=null]
     * @returns {*}
     */
    GetData(key, defaultValue)
    {
        if(typeof(this.data[key]) != 'undefined') {
            return this.data[key];
        }

        if(typeof(defaultValue==='undefined')) {
            defaultValue = null;
        }

        return defaultValue;
    }

    /**
     * Whether the dialog is currently shown.
     * @returns {Boolean}
     */
    IsShown()
    {
        return this.isShown;
    }
}
