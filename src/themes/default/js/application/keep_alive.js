"use strict";

/**
 * Sends AJAX requests regularly to attempt to keep the session alive,
 * and check whether the user is still logged in.
 *
 * Via the browser javascript console, you can set the interval for the
 * requests, and set a target flag to simulate a specific response from
 * the server.
 *
 * Example:
 *
 * <pre>
 * // Sets the interval to 20 seconds, and simulates a CAS redirect.
 * application.keepAlive.SetInterval(20).SetTarget('redirect');
 * </pre>
 *
 * @class KeepAlive
 * @package Application
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class KeepAlive
{
    constructor()
    {
        this.enabled = true;
        this.interval = 120;
        this.intervalInstance = null;
        this.target = null;
        this.failureDialog = null;
        this.requestRunning = false;
    }

    /**
     * Sends AJAX requests regularly to keep the session alive.
     * This can be useful when the user stays on a single page for a long time.
     *
     * @private
     */
    Start()
    {
        if (this.intervalInstance !== null || !this.enabled) {
            return;
        }

        this.log('Initializing with an interval of [' + this.interval + '] seconds.');

        const handler = this;

        this.intervalInstance = window.setInterval(
            function() {
                handler.HandleRequest();
            },
            (1000 * this.interval)
        );
    }

    Disable()
    {
        this.enabled = false;
    }

    /**
     * Called server-side to set the interval for the keepalive requests.
     * See <code>Application_Driver::configureScripts()</code> for details.
     *
     * @param {Integer} interval
     * @return {KeepAlive}
     */
    SetInterval(interval)
    {
        this.log('Setting interval to [' + interval + '] seconds.');

        this.interval = interval;

        if(this.intervalInstance !== null) {
            clearInterval(this.intervalInstance);
            this.intervalInstance = null;
            this.Start();
        }

        return this;
    }

    /**
     * Sets the target flag for the keepalive request.
     * These are used for testing, and accepted values are:
     *
     * - redirect: Redirects to the login page, to simulate a CAS authentication process.
     *
     * @param {String|null} target
     * @return {KeepAlive}
     */
    SetTarget(target)
    {
        this.log('Setting target to [' + target + '].');

        this.target = target;

        return this;
    }

    HandleRequest()
    {
        if (this.requestRunning || !this.enabled) {
            return;
        }

        this.requestRunning = true;

        const handler = this;

        this.log('Sending server request.');

        application.createAJAX('KeepAlive')
            .SetPayload({
                'keep-alive': {
                    'target': this.target
                }
            })
            .Success(function (data) {
                handler.OnSuccess(data);
            })
            .SetReportFailure(false)
            .Failure(function (errorText)
            {
                handler.OnFailure();
            })
            .Send();
    }

    OnSuccess(data)
    {
        this.requestRunning = false;

        if(typeof data !== 'object' && typeof data.status !== 'string' && data.status !== 'OK') {
            this.OnFailure();
            return;
        }

        this.log('OK | Response received.');

        if(this.failureDialog !== null) {
            this.failureDialog.Hide();
        }
    }

    OnFailure()
    {
        this.requestRunning = false;

        // Avoid showing it again
        if(this.failureDialog !== null) {
            return;
        }

        this.log('ERROR | No response, user is probably logged out.');

        const handler = this;

        this.failureDialog = application.dialogMessage(
            '<p><strong>' +
                t('Your %1$s session seems to have expired.', application.appNameShort) +
            '</strong></p>' +
            '<p>'+
                t('If you do not want to lose your work, open the %1$s in a new tab to log in again.', application.appNameShort)+' '+
                t('Afterwards, you can continue working in this tab.')+
            '</p>'+
            '<p>'+
            '<span class="muted">'+
                t('Note:')+' '+
                t('The %1$s interface may appear without first showing a login screen.', application.appNameShort)+' '+
                t('In that case, your login was refreshed automatically and you are ready to go.')+
            '</span>'+
            '</p>'+
            '<p>'+
                UI.Button(t('Log in (new tab)', application.appNameShort))
                    .MakePrimary()
                    .Link(application.url, true)
                    .SetIcon(UI.Icon().LogIn())+' '+
                UI.Button(t('Try again'))
                    .Click(function () {
                        handler.HandleRequest();
                    })
                    .SetIcon(UI.Icon().Refresh())+
            '</p>',
            t('Session expired'),
            function () {
                handler.OnFailureDialogClosed();
            }
        );
    }

    log(message)
    {
        application.log('KeepAlive', message);
    }

    OnFailureDialogClosed()
    {
        this.failureDialog = null;
    }
}
