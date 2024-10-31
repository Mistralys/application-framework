"use strict";

class AJAXDropdown
{
    /**
     * @param {String} baseID
     * @param {String} methodName
     * @param {Object|null} data
     */
    constructor(baseID, methodName, data)
    {
        this.ERROR_REQUEST_FAILED = 166801;

        if(data === null) {
            data = {};
        }

        this.loaded = false;
        this.open = false;
        this.baseID = baseID;
        this.methodName = methodName;
        this.data = data;
        this.logger = new Logger('AJAXDropdown [#'+this.baseID+']');
    }

    Start()
    {
        this.logger.logEvent('Starting AJAX dropdown.');

        // Because bootstrap 2 has no APIs to attach an event to the
        // dropdown open and close events, we observe the class changes
        // of the dropdown container element.
        $('#'+this.baseID).onClassChange((el, className) => {
            if(className.indexOf('open') !== -1) {
                this.HandleOpen();
            } else {
                this.HandleClose();
            }
        });
    }

    HandleOpen()
    {
        if(this.loaded) {
            return;
        }

        this.logger.logEvent('Sending AJAX request to render the menu.');
        this.logger.log('Target method: ['+this.methodName+']');

        this.loaded = true;

        this.SetBody(application.renderSpinner(t('Loading menu...')));

        const dropdown = this;

        application.createAJAX(this.methodName)
            .SetPayload(this.data)
            .MakeHTML()
            .Error(t('Failed to load menu'), this.ERROR_REQUEST_FAILED, null)
            .Success(function(response) {
                dropdown.HandleLoadSuccess(response);
            })
            .Send();
    }

    HandleClose()
    {

    }

    SetBody(html)
    {
        $('#' + this.baseID+'-ajax-body').html(html);
    }

    HandleLoadSuccess(menuHTML)
    {
        this.logger.logEvent('Menu loaded successfully.');

        $('#' + this.baseID+'-ajax-body').parents('UL').first().html(menuHTML);
    }
}
