"use strict";

class ErrorLogger
{
    constructor() {
        this.KEY_CALL_TRACE = 'call_trace';
        this.KEY_CAUSE_DATA = 'cause_data';
        this.KEY_CODE = 'code';
        this.KEY_COLUMN = 'column';
        this.KEY_DETAILS = 'details';
        this.KEY_LINE = 'line';
        this.KEY_LOG_LINES = 'log_lines';
        this.KEY_MESSAGE = 'message';
        this.KEY_REFERER = 'referer';
        this.KEY_TYPE = 'type';
        this.KEY_URL = 'url';

        this.logger = new Logger('ErrorLogger');

        const logger = this;

        window.onerror = function(errorMsg, url, lineNumber, column, errorObj) {
            logger.HandleError(errorMsg, url, lineNumber, column, errorObj);
        }
    }

    HandleError(errorMsg, url, lineNumber, column, errorObj)
    {
        let code = 0;
        let type = 'Error';
        let details = '';
        let causeData = null;
        let callTrace = '';

        if(!isEmpty(errorObj))
        {
            type = errorObj.name;
            causeData = errorObj.cause;

            if(typeof errorObj.stack !== 'undefined') {
                callTrace = errorObj.stack.toString();
            }

            if(errorObj instanceof ApplicationException)
            {
                code = errorObj.GetCode();
                details = errorObj.GetDeveloperInfo();
                errorMsg = errorObj.GetMessage();
                callTrace = errorObj.GetTrace();
            }
        }

        let payload = {}
        payload[this.KEY_CODE] = code;
        payload[this.KEY_COLUMN] = column;
        payload[this.KEY_DETAILS] = details;
        payload[this.KEY_LINE] = lineNumber;
        payload[this.KEY_MESSAGE] = errorMsg;
        payload[this.KEY_REFERER] = window.location.href;
        payload[this.KEY_TYPE] = type;
        payload[this.KEY_URL] = url;
        payload[this.KEY_LOG_LINES] = application.getLoggedLines();
        payload[this.KEY_CALL_TRACE] = callTrace;
        payload[this.KEY_CAUSE_DATA] = causeData;

        this.logger.logError('A runtime error occurred: '+errorMsg);
        this.logger.logData(payload);

        $.ajax({
            'dataType': 'json',
            'type': "POST",
            'contentType': 'application/json',
            'url': application.getAjaxURL('AddJSErrorLog'),
            'data': JSON.stringify(payload)
        });
    }
}
