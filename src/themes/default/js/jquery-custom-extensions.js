"use strict";

/**
 * React to class changes on an element:
 *
 * ```
 * $('#element').onClassChange((el, className) => {
 *     // do something
 * });
 * ```
 *
 * @param cb
 * @return {jQuery}
 * @link https://stackoverflow.com/questions/19401633/how-to-fire-an-event-on-class-change-using-jquery
 */
$.fn.onClassChange = function(cb) {
    return $(this).each((_, el) => {
        new MutationObserver(mutations => {
            mutations.forEach(mutation => cb && cb(mutation.target, mutation.target.className));
        }).observe(el, {
            attributes: true,
            attributeFilter: ['class'] // only listen for class attribute changes
        });
    });
};
