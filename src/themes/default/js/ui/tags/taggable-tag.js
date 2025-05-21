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
     * @param {ElementIds} elementIds Used to handle element IDs created for the tag.
     */
    constructor(tagID, label, connected, taggable, elementIds)
    {
        this.tagID = tagID;
        this.label = label;
        this.connected = connected;
        this.taggable = taggable;
        this.subTags = [];
        this.elementIds = elementIds
    }

    GetID()
    {
        return this.tagID;
    }

    GetElementID(suffix=null)
    {
        return this.elementIds.GetID(this.GetIDSuffix(suffix));
    }

    GetIDSuffix(suffix=null)
    {
        let id = 'T' + this.GetID();
        if(!isEmpty(suffix)) {
            id += this.elementIds.GetSeparatorChar() + suffix;
        }

        return id;
    }

    GetElement(suffix=null)
    {
        return this.elementIds.RequireElement(this.GetIDSuffix(suffix));
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
     * @param {Boolean} connected
     */
    SetConnected(connected)
    {
        this.connected = connected;
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
