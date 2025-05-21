"use strict";

/**
 * @package User Interface
 * @subpackage Renderable
 */
class UI_Renderable_Base
{
    constructor() {
        this.ERROR_MISSING_METHOD = 15101;

        this.jsID = nextJSID();
        this.rendered = false;
    }

    /**
     * Retrieves a dom element by its name.
     *
     * @param {String|null} name
     * @returns {jQuery}
     */
    element(name = null) {
        return $('#' + this.elementID(name));
    }

    /**
     * Retrieves a dom element name unique to this class.
     * The specified name can be used to retrieve it later,
     * it is automatically namespaced.
     *
     * @param {String|null} name
     * @returns {String}
     */
    elementID(name = null) {
        if (name !== null) {
            return this.jsID + '_' + name;
        }

        return String(this.jsID);
    }

    /**
     * Renders the element to string.
     * @returns {String}
     */
    Render() {
        const markup = this._Render();

        const renderable = this;
        UI.RefreshTimeout(function ()  {
            renderable.Handle_PostRender();
        });

        return markup;
    }

    /**
     * The concrete class should implement actual
     * implementation of the element's rendering.
     *
     * @abstract
     * @protected
     */
    _Render() {
        return '';
    }

    Handle_PostRender() {
        this._PostRender();
        this.rendered = true;
    }

    _GetTypeName() {
        throw new ApplicationException(
            'Missing method',
            'The [_GetTypeName] method has to be implemented.',
            this.ERROR_MISSING_METHOD
        );
    }

    _PostRender() {

    }

    toString() {
        return this.Render();
    }

    IsRendered() {
        return this.rendered;
    }

    log(message, category) {
        application.log(this._GetTypeName() + ' [' + this.jsID + ']', message, category);
    }

    logError(message) {
        this.log(message, 'error');
    }
}
