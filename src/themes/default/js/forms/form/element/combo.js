"use strict";

class ComboElement
{
    /**
     * @param {String} inputID
     */
    constructor(inputID)
    {
        this.inputID = inputID;
        this.logger = new Logger('Combo element '+inputID);
    }

    Start()
    {
        this.logger.logEvent('Start | Initializing combo element');
    }
}
