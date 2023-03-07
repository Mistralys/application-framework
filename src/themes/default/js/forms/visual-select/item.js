class VisualSelectItem
{
    /**
     * @param {VisualSelectElement} selector
     * @param {jQuery} el
     * @constructor
     */
    constructor(selector, el)
    {
        this.selector = selector;
        this.li = el;
        this.value = el.attr('data-value');
        this.label = el.find('.visel-item-image').attr('title');
        this.setID = el.attr('data-image-set');
        this.selected = false;
    }

    Deselect()
    {
        this.li.removeClass('selected');
    }

    Select()
    {
        this.li.addClass('selected');
    }

    Filter(terms)
    {
        // Ignore images not from the selected set
        if(this.selector.HasSet() && this.setID !== this.selector.GetSetID()) {
            this.Hide();
            return;
        }

        if(terms.length < 2) {
            this.Show();
            return;
        }

        let reg = new RegExp(terms, 'i');
        let string = this.value + ' ' + this.label;

        if(reg.test(string)) {
            this.Show();
        } else {
            this.Hide();
        }
    }

    Show()
    {
        this.li.show();
    }

    Hide()
    {
        this.li.hide();
    }
}
