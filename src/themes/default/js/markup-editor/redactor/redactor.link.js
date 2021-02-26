if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.mymodal = {

    init: function () {
        this.buttonAdd('link', t('Insert link'), this.showMyModal);
    },

    showMyModal: function () {

        var callback = $.proxy(function () {
            this.selectionSave();
            $('#redactor_modal #mymodal-insert').click($.proxy(this.insertLink, this));
        }, this);

        var shops = '';

        $.each(application.shops, function (key, value) {
            if (value['url'] !== '') {
                shops += '<li data-foreign="' + value['foreign_id'] + '" data-id="' + value['id'] + '">' + value['foreign_id'] + ' - ' + value['url'] + '</li>';
            }
        });

        var markup = '' +
            '<section>' +
            '   <label>' + t('URL') + '</label>' +
            '   <input type="text" name="redactor_link_target" id="redactor_link_target" class="redactor_input" />' +
            '   <div id="redactor_autocomplete_shops" class="hidden">' +
            '       <ul id="redactor_autocomplete_shops_list">' +
            shops +
            '       </ul>' +
            '   </div>' +
            '   <div id="redactor_target_pages_container" class="hidden">' +
            '       <label>' + t('Page') + '</label>' +
            '       <select name="redactor_link_target_pages" id="redactor_link_target_page" class="redactor_input">' +
            '       </select>' +
            '       <span class="small">If the page you requested is not available in the selection you can write these behind the shop id like PU.PW.DE:pagename</span>'+
            '   </div>' +
            '   <label>' + t('Text') + '</label>' +
            '   <input type="text" name="redactor_link_text" id="redactor_link_text" class="redactor_input" />' +
            '   <label>' + t('Title') + '</label>' +
            '   <input type="text" name="redactor_link_title" id="redactor_link_title" class="redactor_input" />' +
            '</section>' +
            '<footer>' +
            '   <button class="redactor_modal_btn redactor_modal_action_btn" id="mymodal-insert">Insert</button>' +
            '   <button class="redactor_modal_btn redactor_btn_modal_close">Close</button>' +
            '</footer>';

        this.modalInit(t('Insert link'), markup, 500, callback);

        $('#redactor_autocomplete_shops_list li').bind('click', function () {
            $('#redactor_link_target').val($(this).attr('data-foreign'));
            $('#redactor_autocomplete_shops').addClass('hidden');
            RedactorPlugins.mymodal.loadPages($(this).attr('data-foreign'));
        });

        $('#redactor_modal').on('click', function () {
            $('#redactor_autocomplete_shops').addClass('hidden');
        });

        $('#redactor_link_target').on('keyup',function () {

            $('#redactor_autocomplete_shops').addClass('hidden');
            $('#redactor_target_pages_container').addClass('hidden');

            var found = false,
                val = $(this).val();

            if (val.length > 0) {

                $('#redactor_autocomplete_shops_list li').each(function () {

                    $(this).addClass('hidden');

                    if ($(this).html().indexOf(val) > -1 || $(this).attr('data-foreign').indexOf(val) > -1) {
                        $(this).removeClass('hidden');
                        found = true;
                    }

                });

                if (found === true) {
                    $('#redactor_autocomplete_shops').removeClass('hidden');
                }

            }

        }).on('change', function () {
            $('#redactor_autocomplete_shops').removeClass('hidden');
        });

    },

    insertLink: function (html) {

        var target = $('#redactor_link_target').val(),
            page = $('#redactor_link_target_page option:selected').val(),
            text = $('#redactor_link_text').val(),
            title = $('#redactor_link_title').val();

        if (target != '' && text != '') {
            this.selectionRestore();
            this.insertHtml('[[' + target + (page !== undefined ? ':' + page : '') + '|' + text + '|' + title + ']]');
            this.modalClose();
        }

    },

    loadPages: function (shop_id) {

        $.ajax({

            'dataType': 'json',
            'url': application.getAjaxURL('Pages'),
            'data': {
                'shop': shop_id
            },

            'success': function (data, textStatus, jqXHR) {

                $('#redactor_autocomplete_shops').addClass('hidden');

                if (data.length > 0) {

                    $('#redactor_link_target_page option').remove();
                    $('#redactor_target_pages_container').removeClass('hidden');

                    $(data).each(function (key, value) {
                        $('#redactor_link_target_page').append('<option>' + value.foreign_id + '</option>');
                    });
                    return;
                }

                $('#redactor_target_pages_container').addClass('hidden');

            }

        });

    }

}