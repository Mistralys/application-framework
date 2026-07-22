/**
 * BigSelection Checkable — client-side toggle handler.
 *
 * Manages the checked/unchecked visual state and form-submission behaviour
 * of checkable BigSelection items.
 *
 * Each item is a <li> that contains:
 *  - A hidden <input> whose disabled attribute controls form submission.
 *  - An <a> that acts as the click target.
 *
 * When the user clicks an anchor, the handler:
 *  1. Toggles the `active` CSS class on the parent <li>.
 *  2. Toggles the `disabled` attribute on the sibling hidden <input>,
 *     so only checked items are included in the form post.
 *
 * Usage (injected by the PHP template):
 *   (new UI_BigSelection_Checkable('<elementID>')).Start();
 */

'use strict';

class UI_BigSelection_Checkable
{
    /**
     * @param {string} elementID  The `id` attribute of the BigSelection wrapper <div>.
     */
    constructor(elementID)
    {
        this.elementID = elementID;
        this.widget    = null;
    }

    Start()
    {
        this.widget = document.getElementById(this.elementID);

        if (!this.widget) {
            return;
        }

        const self = this;

        this.widget.querySelectorAll(
            '.bigselection-checkable .bigselection-anchor'
        ).forEach(function(anchor) {
            anchor.addEventListener('click', function(event) {
                event.preventDefault();
                self._toggle(anchor);
            });
        });
    }

    /**
     * Toggles the active state of the item that owns the clicked anchor.
     *
     * @param {HTMLElement} anchor  The <a> element that was clicked.
     * @private
     */
    _toggle(anchor)
    {
        const li    = anchor.closest('.bigselection-checkable');
        const input = li ? li.querySelector('input[type="hidden"]') : null;

        if (!li || !input) {
            return;
        }

        const isActive = li.classList.contains('active');

        if (isActive) {
            li.classList.remove('active');
            input.setAttribute('disabled', 'disabled');
        } else {
            li.classList.add('active');
            input.removeAttribute('disabled');
        }
    }
}

window.UI_BigSelection_Checkable = UI_BigSelection_Checkable;
