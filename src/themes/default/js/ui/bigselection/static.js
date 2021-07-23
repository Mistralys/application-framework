
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
		var termsString = this.termsEL.val().trim();
		
		if(termsString.length < 2)
		{
			$('#'+this.elementID+' .bigselection-entry').show();
			return;
		}

		var terms = termsString.split(' ');
		
		$('#'+this.elementID+' .bigselection-entry').each(function(idx, el) 
		{
			el = $(el);

			var haystack = el.attr('data-terms');
			var found = 0;

			$.each(terms, function(idx2, term)
			{
				var reg = new RegExp(term, 'i');

				if(reg.test(haystack))
				{
					found++;
				}
			});

			if(found === terms.length)
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
