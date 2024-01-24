"use strict";

class TreeNode
{
    /**
     * @param {TreeRenderer} renderer
     * @param {String} elementID
     * @param {String} checkboxID
     */
    constructor(renderer, elementID, checkboxID)
    {
        this.renderer = renderer;
        this.elementID = elementID;
        this.checkboxID = checkboxID;
        this.logger = new Logger(sprintf('TreeNode [%s]', elementID));
    }

    Start()
    {
        this.logger.logEvent('Node is starting.');

        this.element = $('#'+this.elementID);
        this.checkbox = $('#'+this.checkboxID);

        if(this.checkbox.length !== 0)
        {
            const node = this;

            this.checkbox.change(function() {
                node.HandleChanged();
            });
        }
    }

    HandleChanged()
    {
        this.logger.logUI('Node selection changed');

        if(this.checkbox.prop('checked')) {
            this.element.addClass('selected');
        } else {
            this.element.removeClass('selected');
        }
    }
}
