"use strict";

/**
 * A unique ID generator for HTML elements.
 *
 * ## Usage
 *
 * 1. Instantiate it and store it in a variable.
 * 2. Call the {@link GetID} method to get a new ID.
 * 3. Use the ID in your HTML.
 * 4. To get an element, use {@link GetElement} or {@link RequireElement}.
 *
 * @package UI
 */
class ElementIds
{
    /**
     * @param {String|null} [id=null] Use a specific ID if given. Generates a new one otherwise.
     */
    constructor(id=null) {
        this.ERROR_ELEMENT_NOT_FOUND = 168101;

        this.ID_SEPARATOR_CHAR = '_';

        if(isEmpty(id)) {
            id = 'E' + nextJSID();
        }

        this.baseID = id;
    }

    GetSeparatorChar() {
        return this.ID_SEPARATOR_CHAR;
    }

    /**
     * @param {String|null} [suffix=null]
     * @return {string}
     */
    GetID(suffix=null) {
        let id = this.baseID;
        if(!isEmpty(suffix)) {
            id += this.ID_SEPARATOR_CHAR + suffix;
        }

        return id;
    }

    /**
     * Attempts to find an element within the base ID namespace.
     * @param {String|null} [suffix=null] Get sub-elements by suffix.
     * @return {jQuery|null}
     */
    GetElement(suffix=null) {
        const el = $('#'+this.GetID(suffix));

        if(el.length > 0) {
            return el;
        }

        return null;
    }

    /**
     * Like {@link GetElement}, but throws an exception if the element
     * is not found.
     *
     * @param {String|null} [suffix=null]
     * @return {jQuery}
     * @throws ApplicationException
     */
    RequireElement(suffix=null) {
        let el = this.GetElement(suffix);
        if(el !== null) {
            return el;
        }

        throw new ApplicationException(
            'Element not found',
            sprintf('The element with ID [%s] was not found.', this.GetID(suffix)),
            this.ERROR_ELEMENT_NOT_FOUND
        );
    }
}
