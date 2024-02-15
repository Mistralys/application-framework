"use strict";

/**
 * Logger helper class that can be used in any
 * object to log messages namespaced to that
 * object using a source label.
 *
 * @package Application
 * @subpackage Logging
 */
class Logger {
    /**
     * @param {String} sourceLabel
     */
    constructor(sourceLabel) {
        this.sourceLabel = sourceLabel;
    }

    /**
     * @param {String} message
     */
    logEvent(message) {
        application.logEvent(this.sourceLabel, message);
    }

    /**
     * @param {Object} data
     */
    logData(data) {
        application.logData(this.sourceLabel, data);
    }

    logUI(message) {
        application.logUI(this.sourceLabel, message);
    }

    /**
     * @param {String} message
     */
    logError(message) {
        application.logError(this.sourceLabel, message);
    }

    /**
     * @param {String} message
     * @param {String|null} category
     */
    log(message, category=null) {
        application.log(
            this.sourceLabel,
            message,
            category
        );
    }

}
