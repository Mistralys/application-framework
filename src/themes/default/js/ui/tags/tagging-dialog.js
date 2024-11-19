"use strict";

/**
 * Specialized tag editor dialog that lets users
 * choose tags for the selected item.
 *
 * @package Tagging
 */
class TaggingDialog extends BaseDialog
{
    /**
     * @param {String} primary
     */
    constructor(primary) {
        super();
        this.ERROR_FAILED_TO_LOAD_DATA = 167501;
        this.AJAX_METHOD_LOAD_DATA = 'GetTaggingData';
        this.primary = primary;
        this.loaded = false;
        this.loading = false;

        this.SetIcon(UI.Icon().Tags());
    }

    /**
     * @private
     */
    Load() {
        if(this.loaded || this.loading) {
            return;
        }

        this.loading = true;

        const payload = {};
        const self = this;

        application.createAJAX(this.AJAX_METHOD_LOAD_DATA)
            .SetPayload(payload)
            .Success(
                /**
                 * @param {Object} data
                 */
                function (data) {
                    self.Handle_DataLoaded(data);
                }
            )
            .Error(t('Failed to load tagging data.'), this.ERROR_FAILED_TO_LOAD_DATA)
            .Retry(function() {
                self.Load();
            })
            .Always(function () {
                self.loading = false;
            })
            .Send();
    }

    /**
     * @param {Object} data
     * @private
     */
    Handle_DataLoaded(data)
    {
        this.loaded = true;
        this.data = data;
    }

    // region: Abstract methods

    _GetTitle() {
        return t('Tag editor');
    }

    _Handle_Closed() {
    }

    _Handle_Shown() {
        if(!this.loaded) {
            this.Load();
            return;
        }
    }

    _PostChangeBody() {
    }

    _PostRender() {
    }

    _RenderAbstract() {
        return null;
    }

    _RenderBody() {
        return application.renderSpinner(t('Please wait, loading tags...'));
    }

    _RenderFooter() {
        return null;
    }

    _RenderFooterLeft() {
        return null;
    }

    _Start() {
    }

    _Init() {
    }

    // endregion
}
