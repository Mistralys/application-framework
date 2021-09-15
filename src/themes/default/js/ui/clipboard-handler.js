
"use strict";

/**
 * Management class for clipboard-related features.
 *
 * One aspect is the code-styled text with a copy button
 * next to it, which can be added serverside with
 * `UI_StringBuilder::codeCopy()`. This adds the necessary
 * event handlers for the copy buttons.
 *
 * @package UI
 * @subpackage Clipboard
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClipboardHandler
{
    /**
     * Called serverside on page load. See `UI_StringBuilder::codeCopy()`.
     *
     * @param {String} selector
     * @param {Number} fadeoutDelay Seconds, float value
     * @constructor
     */
    constructor(selector, fadeoutDelay)
    {
        // The ClipboardJS adds the event handlers to all HTML
        // elements with the specified selector.
        this.clipBoard = new ClipboardJS(selector);
        this.timers = {};
        this.fadeoutDelay = fadeoutDelay * 1000;

        var handler = this;

        this.clipBoard.on('success', function(e)
        {
            handler.Handle_Success($(e.trigger), e.text);
            e.clearSelection();
        });

        this.clipBoard.on('error', function(e)
        {
            handler.Handle_Error($(e.trigger));
        });
    }

    /**
     * @param {jQuery} trigger
     */
    Handle_Error(trigger)
    {
        application.logError('ClipboardHandler', 'Could not copy text from trigger element ['+trigger.outerHTML+']');
    }

    /**
     * @param {jQuery} trigger
     * @param {String} text
     */
    Handle_Success(trigger, text)
    {
        application.log('Copied text: ' + text);

        var elID = trigger.attr('data-clipboard-target');
        var statusEl = $(elID + '-status');
        var handler = this;

        statusEl.fadeIn('slow');

        if(this.timers[elID] !== 'undefined')
        {
            clearTimeout(this.timers[elID]);
        }

        this.timers[elID] = setTimeout(
            function()
            {
                handler.Handle_FadeStatus(statusEl);
            },
            this.fadeoutDelay
        );
    }

    /**
     * @param {jQuery} statusEl
     */
    Handle_FadeStatus(statusEl)
    {
        statusEl.fadeOut('slow');
    }
}

// Fix for porting the class into the global scope when
// it is loaded via jquery.
window.ClipboardHandler = ClipboardHandler;
