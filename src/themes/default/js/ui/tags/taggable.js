"use strict";

class Taggable
{
    /**
     * @param {String} uniqueID
     * @param {String} label
     * @param {String} typeLabel
     */
    constructor(uniqueID, label, typeLabel) {
        this.uniqueID = uniqueID;
        this.label = label;
        this.typeLabel = typeLabel;

        /**
         * @type {TaggableTag[]}
         */
        this.tags = [];
    }

    GetUniqueID() {
        return this.uniqueID;
    }

    GetLabel() {
        return this.label;
    }

    GetTypeLabel() {
        return this.typeLabel;
    }

    /**
     * @param {TaggableTag} tag
     */
    RegisterTag(tag)
    {
        this.tags.push(tag);
    }

    /**
     * @return {TaggableTag[]}
     */
    GetTags()
    {
        return this.tags;
    }
}
