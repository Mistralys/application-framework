"use strict";

/**
 * Represents a tag connected to a taggable, with
 * its connection flag and sub-tags.
 *
 * @package Tagging
 */
class TaggableTag
{
    /**
     *
     * @param {Number} tagID
     * @param {String} label
     * @param {Boolean} connected
     * @param {Taggable} taggable
     */
    constructor(tagID, label, connected, taggable)
    {
        this.tagID = tagID;
        this.label = label;
        this.connected = connected;
        this.taggable = taggable;
        this.subTags = [];
    }

    GetID()
    {
        return this.tagID;
    }

    GetLabel()
    {
        return this.label;
    }

    IsConnected()
    {
        return this.connected;
    }

    /**
     * @return {Taggable}
     */
    GetTaggable()
    {
        return this.taggable;
    }

    HasSubTags()
    {
        return this.subTags.length > 0;
    }

    /**
     * @return {TaggableTag[]}
     */
    GetSubTags()
    {
        return this.subTags;
    }

    /**
     * @param {TaggableTag} tag
     */
    RegisterSubTag(tag)
    {
        this.subTags.push(tag);
    }
}
