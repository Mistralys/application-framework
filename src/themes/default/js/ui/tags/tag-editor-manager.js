"use strict";

class TagEditorManager
{
    constructor() {
        this.editors = [];
        this.logger = new Logger('TagEditorManager');
    }

    Start() {
        this.logger.log('Starting.');

        const self = this;
        $('.tag-editor').each(
            function(idx, el) {
                self.InitEditor($(el));
            }
        );
    }

    /**
     * @param {jQuery} el
     */
    InitEditor(el) {
        this.editors.push(new TagEditor(el));
    }
}
