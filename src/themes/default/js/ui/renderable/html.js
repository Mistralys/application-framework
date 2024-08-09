"use strict";

/**
 * @package User Interface
 * @subpackage Renderable
 * @abstract
 */
class UI_Renderable_HTML extends UI_Renderable_Base {
    init() {
        this._super();

        /**
         * @type {String[]}
         */
        this.classes = [];

        /**
         * @type {{String:String}}
         */
        this.attributes = {};

        /**
         * @type {{String:String}}
         */
        this.styles = {};

        /**
         * Stores event handlers by event name => callback pairs.
         * @type {{String:Function[]}}
         */
        this.eventHandlers = {};
    }

    /**
     * Adds a class to the button tag.
     *
     * @param {String} className
     * @returns {this}
     */
    AddClass(className) {
        if (!this.HasClass(className)) {
            this.classes.push(className);
        }

        if (this.IsRendered()) {
            this.element().addClass(className);
        }

        return this;
    }

    /**
     * Removes a class from the button tag. Has no effect
     * if it does not have the class.
     *
     * @param {String} className
     * @returns {this}
     */
    RemoveClass(className) {
        let keep = [];
        $.each(this.classes, function (idx, item) {
            if (item !== className) {
                keep.push(item);
            }
        });

        this.classes = keep;

        if (this.IsRendered()) {
            this.element().removeClass(className);
        }

        return this;
    }

    /**
     * Checks if the button has the specified class.
     *
     * @return {Boolean}
     */
    HasClass(className) {
        for (let i = 0; i < this.classes.length; i++) {
            if (this.classes[i] === className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets an attribute of the element. Note that this will
     * not work for setting attributes like the type or class
     * attributes since these are handled separately.
     *
     * @param {String} name
     * @param {String} value
     * @returns {UI_Renderable_HTML}
     */
    SetAttribute(name, value) {
        this.attributes[name] = value;
        return this;
    }

    /**
     * Removes the specified attribute.
     *
     * @param {String} name
     * @return {UI_Renderable_HTML}
     */
    RemoveAttribute(name) {
        if (typeof this.attributes[name] !== 'undefined') {
            delete this.attributes[name];
        }

        return this;
    }

    /**
     * Sets a style of the element's style attribute.
     *
     * Examples:
     *
     * AddStyle('font-family', 'Arial');
     * AddStyle('display', 'none');
     *
     * @param {String} style The style to set
     * @param {String} value The value to set for the style
     * @returns {this}
     */
    SetStyle(style, value) {
        this.styles[style] = value;

        if (this.IsRendered()) {
            this.element().css(style, value);
        }

        return this;
    }

    /**
     * @param {String} style
     * @return {this}
     */
    RemoveStyle(style) {
        if (typeof this.styles[style] !== 'undefined') {
            delete this.styles[style];
        }

        if (this.IsRendered()) {
            this.element().css(style, '');
        }

        return this;
    }

    /**
     * Retrieves a style value.
     *
     * @param {String} style
     * @returns {String}
     */
    GetStyle(style) {
        if (typeof this.styles[style] !== 'undefined') {
            return this.styles[style];
        }

        return null;
    }

    /**
     * Renders all attributes to string. This includes class and
     * style attributes.
     *
     * @returns {String}
     */
    RenderAttributes() {
        let attributes = this.attributes;

        if (attributes['id'] == null) {
            attributes['id'] = this.jsID;
        }

        attributes['class'] = this.classes.join(' ');
        attributes['style'] = UI.CompileStyles(this.styles);

        return UI.CompileAttributes(attributes);
    }

    /**
     * Adds an event handler for the specified event. All event
     * handlers get the renderable object instance as first argument,
     * additional parameters depend on the event.
     *
     * @param {String} eventName
     * @param {Function} handler
     * @returns {this}
     */
    AddEventHandler(eventName, handler) {
        if (typeof this.eventHandlers[eventName] === 'undefined') {
            this.eventHandlers[eventName] = [];
        }

        this.eventHandlers[eventName].push(handler);
        return this;
    }

    /**
     * Triggers the specified event.
     *
     * The handler function gets the renderable instance
     * as first parameter. <code>this</code> is undefined.
     * Any additional arguments are passed on to the event
     * handling functions.
     *
     * @param {String} eventName
     */
    TriggerEvent(eventName) {
        if (typeof this.eventHandlers[eventName] === 'undefined' || this.eventHandlers[eventName].length === 0) {
            return;
        }

        let args = [];
        args.push(this);

        for (let i = 1; i < arguments.length; i++) {
            args.push(arguments[i]);
        }

        $.each(this.eventHandlers[eventName], function (idx, handler) {
            handler.apply(undefined, args);
        });
    }

    /**
     * Checks if at least one event handler has been added for the specified event.
     * @param {String} eventName
     * @returns {Boolean}
     */
    HasEventHandler(eventName) {
        return typeof this.eventHandlers[eventName] !== 'undefined' && this.eventHandlers[eventName].length > 0;
    }

    Hide() {
        if (this.IsRendered()) {
            this.element().hide();
        }

        return this.SetStyle('display', 'none');
    }

    Show() {
        if (this.IsRendered()) {
            this.element().show();
        }

        return this.RemoveStyle('display');
    }
}
