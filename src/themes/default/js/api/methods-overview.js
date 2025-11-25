"use strict";

/**
 * Class that handles the filtering of API methods in the documentation overview
 * using a search field.
 *
 *
 * @package API
 * @subpackage Documentation
 * @class MethodsOverview
 */
class MethodsOverview
{
    static ERROR_MISSING_FILTER_ELEMENT = 185501;
    static DEBOUNCE_DELAY_MS = 300;

    /**
     * This is instantiated server-side in the header scripts.
     *
     * @param {String} idFilterInput
     * @param {String} className
     * @param {String} filterAttribute
     */
    constructor(idFilterInput, className, filterAttribute)
    {
        this.idFilterInput = idFilterInput;
        this.className = className;
        this.filterAttribute = filterAttribute;
        this.elFilterInput = null;
        this.filterTimer = null;
        this.debounceMs = MethodsOverview.DEBOUNCE_DELAY_MS;
    }

    /**
     * Initialize the filtering functionality.
     *
     * > NOTE: This is called server-side on page load.
     */
    Start()
    {
        this.elFilterInput = document.getElementById(this.idFilterInput);

        if (!this.elFilterInput) {
            throw new ApplicationException(
                'Could not find filter element.',
                sprintf(
                    'Was looking for element with id [%s].',
                    this.idFilterInput
                ),
                MethodsOverview.ERROR_MISSING_FILTER_ELEMENT
            );
        }

        this.elFilterInput.addEventListener('input', () => this.checkFilter(null));

        // Input events do not include key information, so we need a separate listener.
        this.elFilterInput.addEventListener('keyup', (e) => this.checkFilter(e));

        this.elFilterInput.focus();
    }

    /**
     * Checks if a keyboard event was forwarded, and if the Enter key was pressed.
     * @param {Event} e
     * @return {Boolean}
     * @private
     */
    isEnterEvent(e)
    {
        const key = e.key || e.keyCode;

        // Accept both modern string keys and legacy numeric keyCode values (13)
        if (key === 'Enter' || key === 13 || key === '13') {
            //
            if (typeof e.preventDefault === 'function') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param {Event|null} e Optional keyboard event. If provided and Enter is pressed,
     *                        the method will prevent default and run immediately.
     * @private
     */
    checkFilter(e = null)
    {
        // Debounce the actual filter operation so it doesn't run on every keystroke.
        if (this.filterTimer) {
            clearTimeout(this.filterTimer);
        }

        // If the Enter key was pressed, run immediately and return.
        if (e !== null && this.isEnterEvent(e)) {
            // avoid form submission / default behavior
            e.preventDefault();
            this.applyFilter();
            return;
        }

        // Set a new timer to run the filter after the debounce delay.
        this.filterTimer = setTimeout(
            () => {
                this.applyFilter();
            },
            this.debounceMs
        );
    }

    /**
     * Perform the actual filtering work. Extracted from the timeout callback
     * to keep ApplyFilter small and readable.
     *
     * @private
     */
    applyFilter()
    {
        const filterValue = this.elFilterInput.value.toLowerCase();
        const methodItems = document.querySelectorAll("." + this.className);

        methodItems.forEach((item) =>
        {
            const filterText = (item.getAttribute(this.filterAttribute) || "").toLowerCase();

            if (filterText.includes(filterValue)) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });

        // clear timer reference
        this.filterTimer = null;
    }

    /**
     * Clears the filter input and reapplies the filter to show all methods.
     *
     * @public
     */
    ClearFilter()
    {
        this.elFilterInput.value = "";
        this.applyFilter();
        this.elFilterInput.focus();
    }
}
