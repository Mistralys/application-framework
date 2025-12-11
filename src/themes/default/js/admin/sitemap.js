"use strict";

class Sitemap
{
    /**
     * @param {String} idTitle
     * @param {String} idPrefixProperty
     * @param {Object} properties
     */
    constructor(idTitle, idPrefixProperty, properties)
    {
        this.idTitle = idTitle;

        this.idPrefixProperty = idPrefixProperty;
        this.properties = properties;
        this.screens = {};
        this.titles = {};
        this.timer = null;
        this.shown = false;
    }

    Start()
    {
        this.elTitle = document.getElementById(this.idTitle);

        this.ClearScreenInfo();
    }

    /**
     * @param {String} id
     * @param {String} title
     * @param {Object} propertyValues
     */
    RegisterScreen(id, title, propertyValues)
    {
        this.titles[id] = title;
        this.screens[id] = propertyValues;
    }

    /**
     * @param {String} id
     */
    ShowScreenInfo(id)
    {
        clearTimeout(this.timer);

        if(!this.shown) {
            this.shown = true;
        }

        this.elTitle.innerHTML = this.titles[id];

        const propertyValues = this.screens[id];

        for (const property in this.properties) {
            const elementId = this.idPrefixProperty + property;
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = propertyValues[property];
            }
        }
    }

    ClearScreenInfo()
    {
        this.timer = setTimeout(() => {
            this.doClearScreenInfo();
        }, 600);
    }

    doClearScreenInfo()
    {
        this.elTitle.innerHTML = '<i>('+t('Hover over a screen')+')</i>';

        for (const property in this.properties) {
            const element = document.getElementById(this.idPrefixProperty + property);
            if (element) {
                element.innerHTML = '<span class="muted">-</span>';
            }
        }

        this.shown = false;
    }
}
