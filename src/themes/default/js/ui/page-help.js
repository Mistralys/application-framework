"use strict";

class UI_PageHelp
{
	constructor()
	{
		this.open = false;
		this.elHelp = null;
	}

	Start()
	{
		var help = this;
		this.elHelp = $('#page-help .help-contents');

		$('#page-help .help-opener')
			.click(function() {
				help.Toggle();
			});
	}

	Toggle()
	{
		if(this.open)
		{
			this.Close();
		}
		else
		{
			this.Open();
		}
	}

	Close()
	{
		application.allowAutoRefresh('page-help');
		this.open = false;
		this.elHelp.hide();

		UI.ScrollJumpToElement('#page-help');
	}

	Open()
	{
		application.disallowAutoRefresh('page-help');
		this.open = true;
		this.elHelp.show();
	}
}

window.UI_PageHelp = UI_PageHelp;
