var UI_Lockscreen =
{

    key: null,
    expires: null,
    timer: null,

    CheckTimeout: function () {

        $.ajax({
            url: application.getAjaxURL('GetLockTimeout'),
            data: {
                key: UI_Lockscreen.key
            },
            success: function (data, textStatus, jqXHR) {

                UI_Lockscreen.expires = parseInt(data);

                window.setTimeout(
                    UI_Lockscreen.CheckTimeout,
                    5000
                );

            },
            error: function (jqXHR, textStatus, errorThrown) {
                UI_Lockscreen.Handle_CheckTimeoutFailure(errorThrown);
            }
        });

    },

    Handle_CheckTimeoutFailure: function (error) {
        console.log(error);
    },

    time2string: function () {

        if (this.expires < 0) {
            return location.reload();
        }

        var d = Math.floor(this.expires / (60 * 60 * 24)) % 24;
        var h = Math.floor(this.expires / (60 * 60)) % 24;
        var m = Math.floor(this.expires / 60) % 60;
        var s = this.expires % 60;

        var out = '';

        if (d > 0) {
            out += d + ' ' + (d == 1 ? t('day') : t('days')) + ' ';
        }

        if (h > 0) {
            out += h + ' ' + (d == 1 ? t('hour') : t('hours')) + ' ';
        }

        if (m > 0) {
            out += m + ' ' + (m == 1 ? t('minute') : t('minutes')) + ' ';
            if (s > 0) {
                out += t('and') + ' ';
            }
        }

        if (s > 0) {
            out += s + ' ' + (s == 1 ? t('second') : t('seconds'));
        }

        $('[data-id=lockscreen-expires]').html(out);

        window.setTimeout(function () {
            UI_Lockscreen.time2string();
            UI_Lockscreen.expires -= 1;
        }, 1000);

    }

}