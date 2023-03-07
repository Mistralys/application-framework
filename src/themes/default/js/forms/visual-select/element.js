/**
 * @var {jQuery} $
 */

class VisualSelectElement
{
	/**
	 * @param {String} elementID
	 * @constructor
	 */
	constructor(elementID)
	{
		this.elementID = elementID+'-visel';
		this.formEl = $('#'+elementID);
		this.expanded = false;

		let formValue = this.formEl.val();
		let items = [];
		let selector = this;
		this.selectedSet = '';

		this.formEl.change(function() {
			selector.Handle_SelectChanged();
		});
		
		$('#'+this.elementID+' .visel-btn-flat-view').click(function() {
			selector.FlatView();
		});

		$('#'+this.elementID+' .visel-btn-grouped-view').click(function() {
			selector.GroupedView();
		});
		
		$('#'+this.elementID+' .visel-filter-input')
			// Prevent the enter key from submitting the whole form in this field
			.keydown(function(event) {
				if(event.keyCode === 13) {
					event.preventDefault();
					return false;
				}
			})
			.keyup(function() {
				selector.Filter();
			});

		$('#'+this.elementID+' .visel-btn-clear-filter').click(function() {
			selector.ClearFilters();
		});
		
		$('#'+this.elementID+' .visel-expand').click(function() {
			selector.ToggleExpand();
		});

		$('#'+this.elementID+' .visel-btn-switch-set').click(function(event) {
			selector.SwitchSet($(event.target).attr('data-image-set'));
		});

		let initialItem = null;
		
		$('#'+this.elementID+' LI.visel-item').each(function(idx, el) 
		{
			el = $(el);
			
			let item = new VisualSelectItem(selector, el);

			if(item.value === formValue)
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
	}

	HasSet()
	{
		return this.selectedSet !== '';
	}

	GetSetID()
	{
		return this.selectedSet;
	}
	
	ToggleExpand()
	{
		let el = $('#'+this.elementID+' .visel-body');
		
		if(el.hasClass('expanded')) 
		{
			el.removeClass('expanded');
			UI.ScrollToElement('#'+this.elementID);
		} 
		else 
		{
			el.addClass('expanded');
		}
	}
	
	Handle_SelectChanged()
	{
		let val = this.formEl.val();
		let selector = this;
		
		$.each(this.items, function(idx, item) {
			if(item.value === val) {
				selector.Handle_ItemClick(item);
			}
		});
	}
	
	Handle_ItemClick(clickedItem)
	{
		let clickedVal = clickedItem.value;
		
		$.each(this.items, function(idx, item) {
			if(item.value === clickedVal) {
				item.Select();
			} else {
				item.Deselect();
			}
		});
		
		this.formEl.val(clickedVal);
	}
	
	FlatView()
	{
		$('#'+this.elementID).removeClass('view-grouped').addClass('view-flat');
		
		$('#'+this.elementID+' .visel-items.grouped').hide();
		$('#'+this.elementID+' .visel-items.flat').show();
	}
	
	GroupedView()
	{
		$('#'+this.elementID).addClass('view-grouped').removeClass('view-flat');
		
		$('#'+this.elementID+' .visel-items.grouped').show();
		$('#'+this.elementID+' .visel-items.flat').hide();
	}
	
	Filter()
	{
		let terms = $('#'+this.elementID+' .visel-filter-input').val().trim();
		
		$.each(this.items, function(idx, item) 
		{
			item.Filter(terms);
		});
	}
	
	ClearFilters()
	{
		$('#'+this.elementID+' .visel-filter-input').val('');
		
		this.Filter();
	}

	/**
	 * @param {String} setID
	 */
	SwitchSet(setID)
	{
		this.selectedSet = setID;

		// Hide/show select element options
		this.formEl.find('OPTION').prop('hidden', true);
		this.formEl.find('OPTGROUP').prop('hidden', true);
		this.formEl.find('OPTGROUP[data-image-set="'+setID+'"]').prop('hidden', false);
		this.formEl.find('OPTION[data-image-set="'+setID+'"]').prop('hidden', false);

		// Mark the set button as active
		$('.visel-btn-switch-set').removeClass('active');
		$('.visel-btn-switch-set[data-image-set="'+setID+'"]').addClass('active');

		// Filter the images by set
		$('.visel-item').hide();
		$('.visel-item[data-image-set="'+setID+'"]').show();

		// Filter the groups by set
		$('.visel-group').hide();
		$('.visel-group[data-image-set="'+setID+'"]').show();

		// Filter the groups menu by set
		$('.visel-groups-menu LI').hide();
		$('.visel-groups-menu LI[data-image-set="'+setID+'"]').show();

		this.Filter();
	}
}
