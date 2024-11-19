"use strict";

/**
 * Tagging manager used in screens where there are multiple
 * taggable records whose tag list can be edited.
 *
 * Uses the selector {@link TagEditorManager.SELECTOR_EDITORS}
 * to find matching elements, and creates a {@link TagEditor}
 * instance for each of them.
 *
 * @package Tagging
 */
class TagEditorManager
{
    constructor() {
        this.SELECTOR_EDITORS = '.tag-editor';
        this.editors = [];
        this.logger = new Logger('TagEditorManager');
    }

    Start() {
        this.logger.log(sprintf('Starting using selector [%s].', this.SELECTOR_EDITORS));

        const self = this;
        $(this.SELECTOR_EDITORS).each(
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
