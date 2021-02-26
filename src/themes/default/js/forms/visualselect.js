var Forms_VisualSelect =
{
	'elementID':null,
	'items':null,
	'expanded':false,
	'formEl':null,
		
	init:function(elementID)
	{
		this.elementID = elementID+'-visel';
		this.formEl = $('#'+elementID);

		var formValue = this.formEl.val();
		var items = [];
		var selector = this;
		
		this.formEl.change(function() {
			selector.Handle_SelectChanged();
		});
		
		$('#'+this.elementID+' .visel-btn-flat-view').click(function() {
			selector.FlatView();
		});

		$('#'+this.elementID+' .visel-btn-grouped-view').click(function() {
			selector.GroupedView();
		});
		
		$('#'+this.elementID+' .visel-filter-input').keyup(function() {
			selector.Filter();
		});
		
		$('#'+this.elementID+' .visel-btn-clear-filter').click(function() {
			selector.ClearFilters();
		});
		
		$('#'+this.elementID+' .visel-expand').click(function() {
			selector.ToggleExpand();
		});

		var initialItem = null;
		
		$('#'+this.elementID+' LI.visel-item').each(function(idx, el) 
		{
			el = $(el);
			
			var item = {
				'li':el,
				'value':el.attr('data-value'),
				'label':el.find('.visel-item-image').attr('title'),
				'selected':false,
				Deselect:function() 
				{
					this.li.removeClass('selected')
				},
				Select:function()
				{
					this.li.addClass('selected');
				},
				Filter:function(terms)
				{
					if(terms.length < 2) {
						this.Show();
						return;
					}

					var reg = new RegExp(terms, 'i');
					var string = this.value + ' ' + this.label;
					
					if(reg.test(string)) {
						this.Show();
					} else {
						this.Hide();
					}
				},
				Show:function()
				{
					this.li.show();
				},
				Hide:function()
				{
					this.li.hide();
				}
			};

			if(item.value == formValue) 
			{
				initialItem = item;
			}
			
			items.push(item);
			
			el.click(function() {
				selector.Handle_ItemClick(item);
			});
		});
		
		this.items = items;

		if(initialItem != null) {
			this.Handle_ItemClick(initialItem);
		}
	},
	
	ToggleExpand:function()
	{
		var el = $('#'+this.elementID+' .visel-body');
		
		if(el.hasClass('expanded')) 
		{
			el.removeClass('expanded');
			UI.ScrollToElement('#'+this.elementID);
		} 
		else 
		{
			el.addClass('expanded');
		}
	},
	
	Handle_SelectChanged:function()
	{
		var item = null;
		var val = this.formEl.val();
		var selector = this;
		
		$.each(this.items, function(idx, item) {
			if(item.value == val) {
				selector.Handle_ItemClick(item);
			}
		});
	},
	
	Handle_ItemClick:function(clickedItem)
	{
		clickedVal = clickedItem.value;
		
		$.each(this.items, function(idx, item) {
			if(item.value == clickedVal) {
				item.Select();
			} else {
				item.Deselect();
			}
		});
		
		this.formEl.val(clickedVal);
	},
	
	FlatView:function()
	{
		$('#'+this.elementID).removeClass('view-grouped').addClass('view-flat');
		
		$('#'+this.elementID+' .visel-items.grouped').hide();
		$('#'+this.elementID+' .visel-items.flat').show();
	},
	
	GroupedView:function()
	{
		$('#'+this.elementID).addClass('view-grouped').removeClass('view-flat');
		
		$('#'+this.elementID+' .visel-items.grouped').show();
		$('#'+this.elementID+' .visel-items.flat').hide();
	},
	
	Filter:function()
	{
		var terms = $('#'+this.elementID+' .visel-filter-input').val().trim();
		
		$.each(this.items, function(idx, item) 
		{
			item.Filter(terms);
		});
	},
	
	ClearFilters:function()
	{
		$('#'+this.elementID+' .visel-filter-input').val('');
		
		this.Filter();
	}
};

Forms_VisualSelect = Class.extend(Forms_VisualSelect);