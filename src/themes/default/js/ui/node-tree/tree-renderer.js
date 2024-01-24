"use strict";

class TreeRenderer
{
    /**
     * @param {String} elementID
     */
    constructor(elementID)
    {
        this.elementID = elementID;
        this.logger = new Logger(sprintf('NodeTree [%s]', elementID));
        this.nodes = [];
    }

    RegisterNode(elementID, checkboxID)
    {
        this.nodes.push(new TreeNode(this, elementID, checkboxID));
    }

    Start()
    {
        $.each(
            this.nodes,
            /**
             * @param {Number} idx
             * @param {TreeNode} node
             */
            function(idx, node) {
                node.Start();
            }
        );
    }
}
