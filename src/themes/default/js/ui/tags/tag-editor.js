"use strict";

/**
 * Handles the editing UI for a single taggable record's
 * tag list. The actual editing is done via a dialog,
 * {@link TaggingDialog}.
 *
 * @package Tagging
 */
class TagEditor
{
    /**
     * @param {jQuery} el The editor DOM element (selector: {@link TagEditorManager.SELECTOR_EDITORS}).
     * @see TagEditorManager.InitEditor
     */
    constructor(el) {
        this.ERROR_MISSING_PRIMARY_VALUE = 167201;
        this.ATTRIBUTE_PRIMARY = 'data-primary';

        this.el = el;
        this.primary = this._ResolvePrimary();
        this.logger = new Logger(sprintf('TagEditor [%s]', this.primary));
        this.logger.log('Starting.');

        /**
         * @type {TaggingDialog|null}
         */
        this.dialog = null;

        const self = this;
        el.click(function() {
            self.HandleClick();
        });

        // To enable visual cues that the tags can be edited
        el.addClass('started');
        el.attr('title', t('Click to edit the tags list'));
        UI.MakeTooltip(el);
    }

    /**
     * @return {string}
     * @private
     */
    _ResolvePrimary()
    {
        const primary = this.el.attr(this.ATTRIBUTE_PRIMARY)

        if(!isEmpty(primary)) {
            return String(primary);
        }

        throw new ApplicationException(
            'Primary attribute is required for tag editor.',
            sprintf(
                'The attribute [%s] did not exist or was empty on the tag editor element.',
                this.ATTRIBUTE_PRIMARY
            ),
            this.ERROR_MISSING_PRIMARY_VALUE
        );
    }

    HandleClick() {
        // Hide the tooltip in case it's still visible
        UI.HideTooltip(this.el);

        this.logger.logUI('Clicked the element, opening the tag editor.');

        this.CreateDialog().Show();
    }

    /**
     * @private
     */
    CreateDialog() {
        if(this.dialog !== null) {
            return this.dialog;
        }

        const self = this;
        const dialog = new TaggingDialog(
            this.primary,
            /**
             * @param {TaggableTag[]} connectedTags
             */
            function (connectedTags) {
                self.Handle_TagsUpdated(connectedTags);
            }
        );

        this.dialog = dialog;

        return dialog;
    }

    /**
     * Updates the connected tag list in the original editor UI.
     * @param {TaggableTag[]} connectedTags
     */
    Handle_TagsUpdated(connectedTags) {
        this.logger.logEvent('TagsUpdated | Updating connected tags list.');

        this.el.find('.tag-editor-list-tags > SPAN').remove();

        const elContainer = this.el.find('.tag-editor-list-tags');

        connectedTags.forEach(function (tag) {
            const tagElement = $('<span></span>');
            tagElement.text(tag.GetLabel());
            elContainer.append(tagElement);
            elContainer.append(' ');
        });
    }
}
