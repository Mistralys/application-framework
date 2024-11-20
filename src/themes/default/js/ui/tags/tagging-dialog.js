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
     * @param {String} primary The unique ID of the taggable item.
     * @param {Function} saveCallback The callback to call when the user saved the changes.
     */
    constructor(primary, saveCallback) {
        super('TaggingDialog ['+primary+']');

        this.ERROR_FAILED_TO_LOAD_DATA = 167501;
        this.ERROR_FAILED_TO_SAVE_DATA = 167502;

        this.AJAX_METHOD_LOAD_DATA = 'GetTaggableInfo';
        this.AJAX_METHOD_SAVE_DATA = 'SetTaggableTags';
        this.AJAX_PARAM_UNIQUE_ID = 'unique_id';

        this.KEY_LABEL = 'label';
        this.KEY_TYPE_LABEL = 'typeLabel';
        this.KEY_TAGS = 'tags';
        this.KEY_TAG_ID = 'id';
        this.KEY_TAG_LABEL = 'label';
        this.KEY_TAG_CONNECTED = 'connected';
        this.KEY_TAG_SUBTAGS = 'subTags';

        this.primary = primary;
        this.loaded = false;
        this.loading = false;
        this.saveCallback = saveCallback;
        this.flatTags = [];

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

        const payload = {}
        payload[this.AJAX_PARAM_UNIQUE_ID] = this.primary;

        const self = this;

        this.logger.logEvent('Loading tagging data...');

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
        this.logger.logEvent('Data has been loaded successfully. Initializing taggable...');
        this.logger.logData(data);

        this.loaded = true;

        this.taggable = new Taggable(
            this.primary,
            data[this.KEY_TYPE_LABEL],
            data[this.KEY_LABEL]
        );

        const self = this;
        $.each(data[this.KEY_TAGS], function (index, tagData) {
            self.taggable.RegisterTag(self.CreateTag(tagData));
        });

        this.logger.logEvent('Taggable has been initialized.');
        this.logger.logData(this.taggable);

        this.SetTitle(sprintf(
            '%s - %s / %s',
            t('Tag Editor'),
            this.taggable.GetTypeLabel(),
            this.taggable.GetLabel()
        ));

        this.ChangeBody(this.RenderTagsList());


    }

    /**
     * @param {Object} tagData
     * @return {TaggableTag}
     * @private
     */
    CreateTag(tagData)
    {
        const tag = new TaggableTag(
            tagData[this.KEY_TAG_ID],
            tagData[this.KEY_TAG_LABEL],
            tagData[this.KEY_TAG_CONNECTED],
            this.taggable,
            this.elements
        );

        const self = this;
        $.each(tagData[this.KEY_TAG_SUBTAGS], function (index, subTagData) {
            tag.RegisterSubTag(self.CreateTag(subTagData));
        });

        // Also store the tag in a flat list for easier access
        // when saving changes.
        this.flatTags.push(tag);

        return tag;
    }

    /**
     * @return {string}
     * @private
     */
    RenderTagsList()
    {
        const self = this;
        let html = '';

        this.taggable.GetTags().forEach(function(tag) {
            html += self.RenderTag(tag);
        });

        return '<ul>'+html+'</ul>';
    }

    /**
     * @param {TaggableTag} tag
     * @private
     */
    RenderTag(tag) {
        let html = '<li>';

        let checked = ''
        if(tag.IsConnected()) {
            checked = ' checked="checked"';
        }

        const idCheck = tag.GetElementID('connected');

        html += ''+
        '<label>'+
            '<input type="checkbox" value="yes" id="'+idCheck+'" '+checked+'/>'+
            tag.GetLabel()+
        '</label>';

        if(tag.HasSubTags()) {
            html += '<ul>';
            const self = this;
            tag.GetSubTags().forEach(function(subTag) {
                html += self.RenderTag(subTag);
            });
            html += '</ul>';
        }

        html += '</li>';
        return html;
    }

    Save()
    {
        this.logger.logEvent('Saving tagging data...');

        this.KEY_SET_TAGS = 'tags';
        this.KEY_SET_TAG_ID = 'id';
        this.KEY_SET_TAG_CONNECTED = 'connected';

        const payload = {};
        payload[this.AJAX_PARAM_UNIQUE_ID] = this.primary;
        payload[this.KEY_SET_TAGS] = [];

        const self = this;
        this.flatTags.forEach(function(tag) {
            payload[self.KEY_SET_TAGS].push(self.SaveTag(tag));
        });

        this.logger.logData(payload);

        application.createAJAX(this.AJAX_METHOD_SAVE_DATA)
            .SetPayload(payload)
            .Success(function() {
                self.Handle_SaveSuccess();
            })
            .Error(t('Failed to save the tagging data.'), this.ERROR_FAILED_TO_SAVE_DATA)
            .Send();
    }

    /**
     * @param {TaggableTag} tag
     */
    SaveTag(tag)
    {
        tag.SetConnected(tag.GetElement('connected').prop('checked'));

        let tagData = {};
        tagData[this.KEY_SET_TAG_ID] = tag.GetID();
        tagData[this.KEY_SET_TAG_CONNECTED] = tag.IsConnected();

        return tagData;
    }

    Handle_SaveSuccess()
    {
        this.Hide();

        this.saveCallback(this.GetConnectedTags());
    }

    /**
     * @return {TaggableTag[]}
     */
    GetConnectedTags()
    {
        const connectedTags = [];
        this.flatTags.forEach(function(tag) {
            if(tag.IsConnected()) {
                connectedTags.push(tag);
            }
        });

        return connectedTags;
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
        }
    }

    _PostChangeBody() {
    }

    _PostRender() {
    }

    _ConfigureButtons() {
        const self = this
        this.AddButtonPrimary(t('Save now'), function() {
            self.Save();
        });

        this.GetPrimaryButton().SetIcon(UI.Icon().Save());

        this.AddButtonClose();
    }

    _ConfigureButtonsLeft() {

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

    // endregion
}
