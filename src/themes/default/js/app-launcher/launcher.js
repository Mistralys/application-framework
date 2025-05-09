"use strict";

class AppLauncher
{
    static ERROR_CONTAINER_NOT_FOUND = 177401;

    /**
     * @param {String} containerID
     */
    constructor(containerID)
    {
        this.containerID = containerID;
        this.container = null;
        this.elements = {};
        this.loaded = false;
    }

    Show()
    {
        this.GetContainer().show();

        this.load();
    }

    /**
     * @returns {jQuery}
     */
    GetContainer()
    {
        return this.getElement('#' + this.containerID);
    }

    GetAppContainer()
    {
        return this.getElement('#' + this.containerID + ' .launcher-apps');
    }

    GetToolContainer()
    {
        return this.getElement('#' + this.containerID + ' .launcher-tools');
    }

    /**
     * Loads the list of apps and tools from the server.
     * @private
     */
    load()
    {
        if(this.loaded) {
           return;
        }

        this.GetToolContainer().html(application.renderSpinner());
        this.GetAppContainer().html(application.renderSpinner());
    }

    /**
     * @param {String} selector
     * @returns {jQuery}
     * @private
     */
    getElement(selector)
    {
        if(this.elements[selector] !== undefined) {
            return this.elements[selector];
        }

        this.elements[selector] = $(selector);

        if(this.elements[selector].length === 0) {
            throw new ApplicationException(
                'AppLauncher container element not found.',
                'Could not find element by selector ['+selector+']',
                AppLauncher.ERROR_CONTAINER_NOT_FOUND
            );
        }

        return this.elements[selector];
    }
}

window.AppLauncher = AppLauncher;
