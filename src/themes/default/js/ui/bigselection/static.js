
"use strict";

class UI_BigSelection_Static
{
	constructor(elementID)
	{
		this.elementID = elementID;
		this.termsEL = null;
	}
	
	Start()
	{
		this.termsEL = $('#'+this.elementID+'-wrapper .bigselection-search-terms').first();

		var selection = this;

		$('#'+this.elementID+'-btn').click(function() {
			selection.ClearSearch();
		});
		
		this.termsEL.keyup(function() {
			selection.UpdateFilters();
		});
	}
	
	ClearSearch()
	{
		this.termsEL.val('');
		
		document.activeElement.blur();
		
		this.UpdateFilters();
	}
	
	UpdateFilters()
	{
		var terms = this.termsEL.val().trim();
		
		if(terms.length < 2) 
		{
			$('#'+this.elementID+' .bigselection-entry').show();
			return;
		}
		
		$('#'+this.elementID+' .bigselection-entry').each(function(idx, el) 
		{
			el = $(el);
			
			var reg = new RegExp(terms, 'i');
			var haystack = el.attr('data-terms');
			
			if(reg.test(haystack)) 
			{
				el.show();
			}
			else
			{
				el.hide();
			}
		});
	}
}

// Fix for porting the class into the global scope when 
// it is loaded via jquery.
window.UI_BigSelection_Static = UI_BigSelection_Static;
