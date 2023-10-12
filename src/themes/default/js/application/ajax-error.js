/**
 * Holds information for an AJAX request that failed.
 *
 * Unless specifically turned off, instantiating an
 * error will automatically send an error report to
 * the server, to be added to the error log.
 *
 * @class Application_AJAX_Error
 * @package Application
 * @subpackage AJAX
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Application_AJAX_Error =
{
    'message':null,
    'code':null,
    'details':null,
    'trace':null,
    'data':null,
    'ajax':null,
    'logWritten':null,

    /**
     * @param {Application_AJAX} ajax
     * @param {String} message
     * @param {Number} code
     * @param {String} details
     * @param {Object} trace
     * @param {Object|null} data
     * @param {Boolean} autoLog
     */
    init:function(ajax, message, code, details, trace, data, autoLog)
    {
        this.message = message;
        this.code = code;
        this.details = details;
        this.trace = trace;
        this.data = data;
        this.ajax = ajax;
        this.logWritten = false;

        if(autoLog !== false) {
            this.log('AutoLog | ENABLED | Sending error report to the server.');
            this.WriteLog()
        } else {
            this.log('AutoLog | DISABLED | Ignoring this error report.');
        }
    },

    GetAJAX:function()
    {
        return this.ajax;
    },

    GetCode:function()
    {
        return this.code;
    },

    GetMessage:function()
    {
        return this.message;
    },

    GetDetails:function()
    {
        return this.details;
    },

    GetData:function()
    {
        return this.data;
    },

    GetMethodName:function()
    {
        return this.ajax.GetMethodName();
    },

    GetPayload:function()
    {
        return this.ajax.GetPayload();
    },

    IsServerException:function()
    {
        return typeof(this.data) === 'object' && this.data.isExceptionData !== undefined;
    },

    /**
     * Logs any ajax requests that fail, by inserting an image
     * in the document which calls the ajax error script with
     * the relevant error information.
     *
     * @protected
     */
    WriteLog:function()
    {
        if(this.logWritten) {
            return;
        }

        // no logging required if this is an exception that
        // occurred on the server initially.
        if(this.IsServerException()) {
            this.log('No need to log the error on the server: the error originated on the server.');
            return;
        }

        this.log('Logging the error on the server.');

        this.logWritten = true;

        application.log(this, 'event');

        var url = URI(application.getBaseURL() + '/ajax/error.php')
            .addSearch('url', window.location.href)
            .addSearch('method', this.GetMethodName())
            .addSearch('message', this.GetMessage())
            .addSearch('code', this.GetCode())
            .addSearch('details', this.GetDetails())
            .addSearch('payload', JSON.stringify(this.GetPayload()))
            .addSearch('data', JSON.stringify(this.GetData()));

        $('body').append('<img src="'+url+'" style="display:none" alt="">');
    },

    toString:function()
    {
        return 'AJAX Error | ['+this.GetMethodName()+'] | [#'+this.GetCode()+'] | '+this.GetMessage();
    },

    log:function(message, category)
    {
        application.log('AJAX Error ['+this.GetCode()+']', message, category);
    }
};

Application_AJAX_Error = Class.extend(Application_AJAX_Error);
