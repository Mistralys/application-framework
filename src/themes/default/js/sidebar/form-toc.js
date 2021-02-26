var Sidebar_FormTOC = 
{
	'registry':null,
	'container':null,
	'dialogAlphaList':null,
		
	init:function(formName, containerID)
	{
		this._super();
		this.formName = formName;
		this.container = $('#'+containerID);
		this.dialogAlphaList = null;
	},
	
	_Render:function()
	{
		this.registry = FormHelper.getRegistry(this.formName);
		var sections = this.registry.GetSections();
		
		var content = ''+
		'<ul class="form-toc-sections">';
			$.each(sections, function(idx, section) {
				content += ''+ 
				'<li>'+
					'<a href="javascript:void(0)" onclick="UI.GetSection(\''+section.GetID()+'\').Expand();UI.ScrollToElement(\'#'+section.GetID()+'\')">'+
						section.GetLabel()+
					'</a>'+
				'</li>';
			});
			content += 
		'</ul>'+
		'<div class="form-toc-autocomplete">'+
			'<input id="'+this.elementID('autocomplete')+'" type="text" class="input-block" placeholder="'+t('Search for fields...')+'"/>'+
		'</div>'+
		'<div class="form-toc-alphalink">'+
			'<a href="javascript:void(0)" id="'+this.elementID('alphalink')+'">'+
				t('Alphabetical list...')+
			'</a>'+
		'</div>';
		
		var toc = this;
		
		return UI.SidebarSection(t('Form navigator'))
		.MakeCollapsible()
		.SetContent(content)
		.Rendered(function() {
			toc.PostRender();
		})
		.Render();
	},
	
	PostRender:function()
	{
		var terms = [];
		$.each(this.registry.GetElements(), function(idx, element) {
			terms.push(element.GetLabel());
		});

		var toc = this;
		this.element('autocomplete').typeahead({
			'source':terms,
			'updater':function(item) {
				toc.AutocompleteSelect(item);
			}
		});
		
		this.element('alphalink').click(function() {
			toc.DialogAlphaList();
		});
	},
	
	DialogAlphaList:function()
	{
		if(this.dialogAlphaList != null) {
			this.dialogAlphaList.Show();
			return;
		}
		
		var toc = this;
		
		var dialog = new Dialog_SelectItems();
		dialog.SetIcon(UI.Icon().Search());
		dialog.MakeSingleSelect();
		dialog.SetNoAutoRemove();
		dialog.SetButtonLabel(t('Find field'));
		dialog.SetItemLabel(t('field'), t('fields'));
		dialog.SetConfirmHandler(function(ids) {
			if(ids.length > 0) {
				toc.Handle_AlphaListSelect(ids[0]);
			}
		});
		
		$.each(this.registry.GetElements(), function(idx, element) {
			dialog.AddItem(element.GetID(), element.GetLabel(), element.GetSectionLabel());
		});
		
		dialog.Show();
		
		this.dialogAlphaList = dialog;
	},
	
	Handle_AlphaListSelect:function(fieldID) 
	{
		var toc = this;
		$.each(this.registry.GetElements(), function(idx, element) {
			if(element.GetID() == fieldID) {
				toc.ScrollToElement(element);
				return false;
			}
		});
	},
	
	AutocompleteSelect:function(item)
	{
		var toc = this;
		$.each(this.registry.GetElements(), function(idx, element) {
			if(element.GetLabel() == item) {
				toc.ScrollToElement(element);
				return false;
			}
		});
	},

	ScrollToElement:function(element)
	{
		var section = element.GetSection();
		if(section != null) {
			section.Expand();
		}
		
		UI.ScrollToElement('#'+element.GetID()+'-anchor');
	},
	
	Start:function()
	{
		this.container.html(this.Render());
	},
	
	_GetTypeName:function()
	{
		return 'Sidebar form TOC';
	}
};

Sidebar_FormTOC = Application_BaseRenderable.extend(Sidebar_FormTOC);