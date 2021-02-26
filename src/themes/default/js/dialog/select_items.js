/**
 * Utility dialog that shows a list of items, and gives the ability to
 * select relevant items graphically.
 * 
 * Usage:
 * 
 * <ul>
 *    <li>Extend this class</li>
 *    <li>Implement the <code>Handle_ItemsSelected</code> method</li>
 *    <li>Optionally, implement the <code>GetItemLabelPlural</code> method to change the label of the items</li>
 *    <li>Instantiate the dialog when needed</li>
 *    <li>Add all available items using the <code>AddItem</code> method</li>
 *    <li>When needed, show the dialog using the <code>Show</code> method</li>
 * </ul>
 * 
 * Alternatively, it is possible to use the dialog directly by 
 * instantiating the <code>Dialog_SelectItems</code> class, and 
 * using the API to configure it.
 * 
 * Example:
 * 
 * <pre>
 * var dialog = new Dialog_SelectItems();
 * 
 * // set the item type labels
 * dialog.SetItemLabel(t('product'), t('products'));
 * 
 * // add the selectable items 
 * for(var i=0; i<10; i++) {
 *     dialog.AddItem(i, t('Item nr '+(i+1)));
 * }
 * 
 * // set the callback function for the selection confirmation
 * dialog.SetConfirmHandler(function(ids) {
 *     alert(ids);
 * });
 * </pre>
 * 
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 * @extends Dialog_Basic
 */
var Dialog_SelectItems = 
{
	_GetTitle:function()
	{
		if(this.IsMulti()) {
			return t('Add %1$s', this.GetItemLabelPlural(false));
		}
		
		return t('Add a %1$s', this.GetItemLabelSingular(false));
	},
	
	'buttonLabel':null,
	'items':null,
	'itemLabelSingular':null,
	'itemLabelPlural':null,
	'confirmHandler':null,
	'cancelHandler':null,
	'options':null,
	'itemsSelected':null,
	'noItemsMessage':null,
	'showDescription':null,
	
	_init:function()
	{
		this.buttonLabel = null;
		this.items = [];
		this.itemLabelSingular = t('item');
		this.itemLabelPlural = t('items');
		this.confirmHandler = null;
		this.cancelHandler = null;
		this.itemsSelected = false;
		this.noItemsMessage = null;
		this.showDescription = true;
		this.options = {
			'MultiSelect':true,
			'AutoRemoveSelected':true,
			'ShowDescriptionColumn':true,
			'DescriptionLabel':t('Description'),
			'StartExpanded':false,
			'Sort':true,
			'ShowHeaders':true
		};
		
		this.SetIcon(UI.Icon().Add());
	},
	
   /**
    * Sets the icon of the dialog, which is shown in the
    * title bar as well as the confirmation button.
    * 
    * @param {UI_Icon} icon
    * @returns {Dialog_SelectItems}
    */
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},
	
   /**
    * Sets the label of the confirmation button, which
    * defaults to "Add selected".
    * 
    * @param {String} label
    * @returns {Dialog_SelectItems}
    */
	SetButtonLabel:function(label)
	{
		this.buttonLabel = label;
		return this;
	},
	
   /**
    * Adds an item to the list of selectable items.
    * 
    * @param {String} id
    * @param {String} name
    * @param {String} [description=''] Optional description of the item that will be shown if set.
    * @return {Dialog_SelectItems_Item}
    */
	AddItem:function(id, name, description)
	{
		if(typeof(description)=='undefined' || description == null) {
			description = '';
		}
		
		var item = new Dialog_SelectItems_Item(this, id, name, description);
		this.items.push(item);
		
		return item;
	},
	
   /**
    * Retrieves the label of items: how they are designated in
    * the dialog UI. For example, if you have a list of products,
    * this would return "products".
    * 
    * @param {Boolean} [capitalized=true] Whether to return the string with a capitalized first letter
    * @return {String}
    */
	GetItemLabelPlural:function(capitalized)
	{
		if(capitalized==false) {
			return this.itemLabelPlural;
		}
		
		return ucfirst(this.itemLabelPlural);
	},

   /**
    * Retrieves the label of items: how they are designated in
    * the dialog UI. For example, if you have a list of products,
    * this would return "product".
    * 
    * @param {Boolean} [capitalized=true] Whether to return the string with a capitalized first letter
    * @return {String}
    */
	GetItemLabelSingular:function(capitalized)
	{
		if(capitalized==false) {
			return this.itemLabelSingular;
		}
		
		return ucfirst(this.itemLabelSingular);
	},
	
   /**
    * Sets the label to use for items. Note that this should
    * be the lowercase variant of the word.
    * 
    * @param {String} singular
    * @param {String} plural
    * @return {Dialog_SelectItems}
    */
	SetItemLabel:function(singular, plural)
	{
		this.itemLabelSingular = singular;
		this.itemLabelPlural = plural;
		return this;
	},
	
	_RenderBody:function()
	{
		var classes = ['table', 'table-condensed'];
		
		if(!this.GetOption('ShowHeaders')) {
			classes.push('without-headers'); 
		}
		
		var body = ''+
		'<div id="'+this.elementID('list')+'" style="display_none;">'+
			'<table class="' + classes.join(' ') + '">';
				if(this.GetOption('ShowHeaders')) {
					body += ''+
					'<thead>'+
						'<tr>'+
							'<th>'+t('Label')+'</th>';
							if(this.GetOption('ShowDescriptionColumn')==true) {
								body += '' +
								'<th>'+this.GetOption('DescriptionLabel')+'</th>';
							}
							body += '' +
						'</tr>'+
					'<thead>';
				}
				body += ''+
				'<tfoot>'+
					'<tr>'+
						'<td colspan="2">'+
							'<span class="muted" id="'+this.elementID('footer')+'">'+
							'</span>'+
						'</td>'+
					'</tr>'+
				'</tfoot>'+
				'<tbody id="'+this.elementID('items-body')+'">'+
				'</tbody>'+
			'</table>'+
		'</div>'+
		'<div id="'+this.elementID('empty_list')+'" style="display:none;">'+
			application.renderAlertInfo(
				UI.Icon().Information()+' '+
				this.GetNoItemsMessage()
			)+
		'</div>';
		
		return body;
	},
	
	_RenderFooterLeft:function()
	{
		var html = ''+
		'<form onsubmit="return false" class="form-inline pull-right">'+
			'<div class="input-prepend input-append">' +
				'<span class="add-on">' +
					UI.Icon().Search() +
				'</span>' +
				'<input class="span2" id="'+this.elementID('search')+'" type="text" placeholder="' + t('Quicksearch...') + '">' +
				'<span class="add-on clickable" id="' + this.elementID('reset-search') + '">'+
					UI.Icon().Delete()+
				'</span>'+
			'</div>'+
		'</form>';

		return html;
	},
	
	IsMulti:function()
	{
		return this.GetOption('MultiSelect', true);
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		var html = '';
		
		if(this.IsMulti()) {
			this.AddButton(
				UI.Button(t('Select all'))
				.SetID(this.elementID('button_selectall'))
				.SetIcon(UI.Icon().SelectAll())
				.Click(function() {
					dialog.Handle_SelectAll();
					$(this).blur();
				})
			);
				
			this.AddButton(
				UI.Button(t('Deselect all'))
				.SetID(this.elementID('button_deselectall'))
				.SetIcon(UI.Icon().DeselectAll())
				.Click(function() {
					dialog.Handle_DeselectAll();
					$(this).blur();
				})
			); 
		}
		
		var label = this.buttonLabel;
		if(label==null) {
			label = t('Add selected');
			if(!this.IsMulti()) {
				label = t('Add');
			}
		}
		
		this.AddButton(
			UI.Button(label)
			.MakePrimary()
			.SetIcon(this.icon)
			.SetID(this.elementID('button_add'))
			.Click(function() {
				dialog.Handle_ConfirmAdd();
			})
		);
		
		this.AddButton(
			UI.Button(t('Cancel'))
			.Click(function() {
				dialog.Hide();
			})
		);
		
		return '';
	},
	
	Handle_SelectAll:function()
	{
		$('.unused-item-row').addClass('selected');
		this.Refresh();
	},
	
	Handle_DeselectAll:function()
	{
		$('.unused-item-row.selected').removeClass('selected');
		this.Refresh();
	},
	
	Refresh:function()
	{
		this.RefreshSelection();
		this.RefreshButtons();
		this.RefreshFooter();
	},
	
   /**
    * After selecting/deselecting an item, check the selection status
    * of all items: when using nested items, the selection state of
    * parent elements may need to be adjusted.
    */
	RefreshSelection:function()
	{
		
	},
	
   /**
    * Refreshes the status line of the table with the products count.
    */
	RefreshFooter:function()
	{
		var label = this.GetItemLabelPlural(false);
		if(this.items.length==1) {
			label = this.GetItemLabelSingular(false);
		}
		
		this.element('footer').html(
			this.items.length + ' ' + label
		);
	},
	
   /**
    * Resets the states of the dialog buttons according to 
    * the different conditions of the dialog.
    */
	RefreshButtons:function()
	{
		var btn_selectall = this.element('button_selectall');
		var btn_deselectall = this.element('button_deselectall');
		var btn_add = this.element('button_add');
		
		btn_add.removeClass('disabled');
		btn_selectall.removeClass('disabled');
		btn_deselectall.removeClass('disabled');
		
		if(!this.HasItems()) {
			this.element('list').hide();
			this.element('empty_list').show();
			btn_selectall.hide();
			btn_deselectall.hide();
			btn_add.hide();
		} else {
			this.element('list').show();
			this.element('empty_list').hide();
			btn_selectall.show();
			btn_deselectall.show();
			btn_add.show();
		}
		
		if(!this.HasSelectedItems()) {
			btn_add.addClass('disabled');
			btn_deselectall.addClass('disabled');
		}
		
		if(this.AllItemsSelected()) {
			btn_selectall.addClass('disabled');
		}
	},

	AllItemsSelected:function()
	{
		if(this.items.length == $('.unused-item-row.selected').length) {
			return true;
		}
		
		return false;
	},
	
	HasItems:function()
	{
		if(this.items.length > 0) {
			return true;
		}
		
		return false;
	},
	
	HasSelectedItems:function()
	{
		if($('.unused-item-row.selected').length > 0) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Checks whether any items have subitems.
    * @returns {Boolean}
    */
	HasSubitems:function()
	{
		for(var i=0; i < this.items.length; i++) {
			if(this.items[i].HasSubitems()) {
				return true;
			}
		}
		
		return false;
	},

   /**
    * Refreshes the list of unused products. Automatically adds new ones
    * (when they have been deleted in the main list).
    */
	RefreshList:function()
	{
		this.log('Refreshing the list.', 'ui');
		
		if(this.options.Sort) {
			this.items.sort(function(a, b) {
				return strnatcasecmp(a.name, b.name);
			});
		}
		
		var body = this.element('items-body');
		for(var i=0; i<this.items.length; i++) {
			item = this.items[i];
			item.Render(body);
		}
		
		var dialog = this;
		UI.RefreshTimeout(function() {
			$('.unused-item-row').each(function(idx, element) {
				var el = $(element);
				var item_id = el.attr('rel');
				el.off();
				el.click(function() {
					dialog.Handle_ItemClicked(item_id);
				});
				el.dblclick(function() {
					dialog.Handle_ItemDoubleClicked(item_id);
				});
			});
		});
	},
	
   /**
    * Retrieves an item instance by its ID. Recurses into
    * subitems as well.
    * 
    * @param {String} item_id
    * @returns {Dialog_SelectItems_Item|NULL}
    */
	GetItem:function(item_id)
	{
		for(var i=0; i < this.items.length; i++) {
			if(this.items[i].id == item_id) {
				return this.items[i];
			} else if(this.items[i].HasItem(item_id)) {
				return this.items[i].GetItem(item_id);
			}
		}
		
		return null;
	},
	
	Handle_ItemClicked:function(item_id)
	{
		if(!this.IsMulti()) {
			$('.unused-item-row').removeClass('selected');
		}
		
		var item = this.GetItem(item_id);
		if(item) {
			item.Handle_Clicked();
		}
		
		this.Refresh();
	},
	
	Handle_ItemDoubleClicked:function(item_id)
	{
		// activate the row. This is already done by the click handler,
		// but just in case the browser does not handle the event the
		// expected way, we activate it again.
		var item = this.GetItem(item_id);
		if(item) {
			item.Select();
		}
		
		this.Handle_ConfirmAdd();
	},
	
	Handle_ConfirmAdd:function()
	{
		if(!this.HasSelectedItems()) {
			return;
		}
		
		var dialog = this;
		var ids = [];
		$('.unused-item-row.selected').each(function(idx, row) {
			var el = $(row);
			var item_id = el.attr('rel');
			ids.push(item_id);
			
			// remove the row of the item in the table
			if(dialog.GetOption('AutoRemoveSelected')) {
				el.remove();
			}
		});
		
		this.Hide();
		this.Handle_ItemsSelected(ids);
	},
	
   /**
    * Disables the automatic removal of the selected entries after
    * the user confirmed his selection.
    * 
    * @return {Dialog_SelectItems}
    */
	SetNoAutoRemove:function()
	{
		return this.SetOption('AutoRemoveSelected', false);
	},
	
   /**
    * Called when the user has confirmed his choice of items: provides
    * an indexed array with item IDs. Implement this method in your
    * class to process the selection as you see fit.
    * 
    * @param {Array} ids
    */
	Handle_ItemsSelected:function(ids)
	{
		this.log('Items selection confirmed. Selected IDs: [' + ids.join(', ') + '].', 'event');

		this.itemsSelected = true;
		
		// in addition to the IDs, collect the matching item instances as well.
		var items = [];
		var objects = this.items;
		$.each(ids, function(idx, id) {
			$.each(objects, function(idx2, object) {
				if(object.GetID() == id) {
					items.push(object);
				}
			});
		});
		
		if(this.confirmHandler != null) {
			this.log('A confirm handler is present, calling it.', 'event');
			this.confirmHandler.call(undefined, ids, items);
			return;
		}
	},
	
   /**
    * Sets the handler function to call when the user confirms his/her choice.
    * The function gets two parameters:
    * 
    * - An indexed array selected IDs (even when in SingleSelect mode)
    * - An indexed array with the matching item objects
    * 
    * @param {Function} handler
    * @returns {Dialog_SelectItems}
    */
	SetConfirmHandler:function(handler)
	{
		this.confirmHandler = handler;
		return this;
	},
	
   /**
    * Sets the handler function to call if the user aborts the selection or 
    * closes the selection dialog without making a choice.
    * 
    * @param {Function} handler
    * @returns {Dialog_SelectItems}
    */
	SetCancelHandler:function(handler)
	{
		if(!isEmpty(handler)) {
			this.cancelHandler = handler;
		}
		
		return this;
	},
	
	_PostRender:function()
	{
		var dialog = this;
		this.element('search').keyup(function() {
			dialog.Handle_Search();
		});
		
		this.element('reset-search').click(function() {
			dialog.Handle_ResetSearch();
		});

		this.RefreshList();
		this.Refresh();
	},
	
	_Start:function()
	{
		var abstractText = this.abstractText;
		
		if(this.GetOption('MultiSelect', true)) {
			abstractText += ' ' + t(
				'Click on the %1$s to add, then confirm your selection with the button below to add them.', 
				this.GetItemLabelPlural(false)
			);
		} else {
			abstractText += ' ' + t(
				'Double-click on the %1$s to add, or select it and then confirm your selection with the button below.', 
				this.GetItemLabelSingular(false)
			);
		}
		
		this.SetAbstract(abstractText);
	},
	
	Handle_Search:function()
	{
		var term = trim(this.element('search').val());
		if(isEmpty(term)) {
			term = null;
		} else {
			term = term.toLowerCase();
			term = term.replace('-', '');
		}
		
		for(var i=0; i<this.items.length; i++) {
			item = this.items[i];
			el = $('tr.unused-item-row[rel='+item.id+']');
			if(term==null) {
				el.show();
				continue;
			}
			
			var str = (item.name + ' ' + item.descr).toLowerCase();
			str = str.replace('-', '');
			if(str.indexOf(term) >= 0) {
				el.show();
			} else {
				el.hide();
			}
		}
	},
	
	Handle_ResetSearch:function()
	{
		this.element('search').val('');
		this.Handle_Search();
		this.element('search').focus();
	},
	
	_Handle_Shown:function()
	{
		this.element('search').focus();
	},
	
   /**
    * Sets an option.
    * 
    * @param {String} name
    * @param {String|Number} value
    * @return {Dialog_SelectItems}
    */
	SetOption:function(name, value)
	{
		this.options[name] = value;
		return this;
	},
	
	GetOption:function(name, defaultValue)
	{
		if(typeof(this.options[name]) != 'undefined') {
			return this.options[name];
		}
		
		if(isEmpty(defaultValue)) {
			defaultValue = null;
		}
		
		return defaultValue;
	},
	
   /**
    * Sets that only a single item may be selected in the list.
    * 
    * @returns {Dialog_SelectItems}
    */
	MakeSingleSelect:function()
	{
		return this.SetOption('MultiSelect', false);
	},
	
	_Handle_Closed:function()
	{
		if(!this.itemsSelected && this.cancelHandler != null) {
			this.log('A cancel handler is defined, calling it.');
			this.cancelHandler.call(undefined);
		}
	},
	
	GetNoItemsMessage:function()
	{
		if(this.noItemsMessage != null) {
			return this.noItemsMessage;
		}
		
		return t('All %1$s are already in use, or none are available.', this.GetItemLabelPlural(false)); 
	},
	
	SetNoItemsMessage:function(message)
	{
		this.noItemsMessage = message;
		return this;
	},
	
	SetNoDescription:function()
	{
		return this.SetOption('ShowDescriptionColumn', false);
	},
	
	SetNoSorting:function()
	{
		return this.SetOption('Sort', false);
	},
	
	SetNoHeaders:function()
	{
		return this.SetOption('ShowHeaders', false);
	},
	
	SetDescriptionHeaderLabel:function(label)
	{
		return this.SetOption('DescriptionLabel', label);
	}
};

Dialog_SelectItems = Dialog_Basic.extend(Dialog_SelectItems);

var Dialog_SelectItems_Item = 
{
	'dialog':null,
	'id':null,
	'name':null,
	'descr':null,
	'rowID':null,
	'classes':null,
	'indent':null,
	'parentItem':null,
	'items':null,
	'expanded':null,
	'properties':null,
	
	init:function(listDialog, id, name, description, parentItem)
	{
		this.dialog = listDialog;
		this.parentItem = parentItem;
		this.id = id;
		this.name = name;
		this.descr = description;
		this.rowID = this.id+'r';
		this.classes = [];
		this.indent = 0;
		this.items = [];
		this.expanded = false;
		this.properties = {};
		
		if(!isEmpty(parentItem)) {
			this.indent = parentItem.indent + 1;
		}
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	AddClass:function(name)
	{
		if(!in_array(name, this.classes)) {
			this.classes.push(name);
		}
		
		return this;
	},
	
	Indent:function(amount)
	{
		this.indent = amount;
		return this;
	},
	
	Render:function(containerElement)
	{
		if(this.dialog.element(this.rowID).length > 0) {
			return;
		}
		
		this.dialog.log('Item ['+this.id+'] is not in the list yet, adding it.', 'ui');
		
		var classes = this.classes;
		classes.push('unused-item-row');
		
		if(this.indent > 0) {
			classes.push('item-row-indent'+this.indent);
		}
		
		var atts = {
			'id':this.dialog.elementID(this.rowID),
			'rel':this.id
		};
		
		if(this.parentItem) {
			atts['data-parent-id'] = this.parentItem.id;
			classes.push('item-row-subitem');
			if(!this.dialog.options['StartExpanded']) {
				this.expanded = false;
				atts['style'] = 'display:none';
			} else {
				this.expanded = true;
			}
		}
		
		atts['class'] = classes.join(' ');
		
		var item = this;
		var showDescr = this.dialog.GetOption('ShowDescriptionColumn');
		var colspan = '';
		if(showDescr==true && isEmpty(this.descr)) {
			colspan = ' colspan="2"';
		}
		
		var html = ''+
		'<tr '+UI.CompileAttributes(atts)+'>'+
			'<td class="item-cell-label nowrap"'+colspan+'>'+
				this.RenderCollapseControls()+
				this.name +
			'</td>';
			if(showDescr==true) {
				html += '' +
				'<td class="item-cell-descr"><span class="muted">'+this.descr+'</span></td>';
			}
			html += '' +
		'</tr>';
			
		containerElement.append(html);
		
		$.each(this.items, function(idx, subitem) {
			subitem.Render(containerElement);
		});
		
		UI.RefreshTimeout(function(){
			item.PostRender();
		});
	},
	
   /**
    * Called once rendering has completed and the DOM is ready.
    */
	PostRender:function()
	{
		var icd = this.dialog.element(this.rowID+'-icd');
		var icm = this.dialog.element(this.rowID+'-icm');
		var icp = this.dialog.element(this.rowID+'-icp');
		
		var item = this;
		
		icp.click(function(e) {
			e.stopPropagation();
			item.Handle_ClickExpand();
		});
		
		icm.click(function(e){
			e.stopPropagation();
			item.Handle_ClickCollapse();
		});
		
		this.RefreshCollapseControls();
	},
	
   /**
    * Updates the collapse/expand icons.
    */
	RefreshCollapseControls:function()
	{
		if(!this.dialog.HasSubitems()) {
			return;
		}
		
		var icd = this.dialog.element(this.rowID+'-icd');
		var icm = this.dialog.element(this.rowID+'-icm');
		var icp = this.dialog.element(this.rowID+'-icp');
		
		icd.hide();
		icm.hide();
		icp.hide();
		
		if(!this.HasSubitems()) {
			icd.show();
		} else if(!this.expanded) {
			icp.show();
		} else {
			icm.show();
		}
	},

   /**
    * If the dialog has items with subitems, we enable
    * the collapse/expand icons for all entries to show
    * the subitems.
    */
	RenderCollapseControls:function()
	{
		if(!this.dialog.HasSubitems() || this.parentItem != null) {
			return '';
		}
		
		var item = this;
		
		var html = ''+
		'<span id="'+this.dialog.elementID(this.rowID+'-icd')+'" style="display:none" class="item-row-collapser collapser-disabled">'+
			UI.Icon().Plus().MakeMuted()+
		'</span>'+
		'<span id="'+this.dialog.elementID(this.rowID+'-icp')+'" style="display:none" class="item-row-collapser collapser-expand">'+
			UI.Icon().Plus()+
		'</span>'+
		'<span id="'+this.dialog.elementID(this.rowID+'-icm')+'" style="display:none" class="item-row-collapser collapser-collapse">'+
			UI.Icon().Minus()+
		'</span>';

		return html;
	},
	
	Handle_ClickExpand:function()
	{
		this.expanded = true;

		for(var i=0; i < this.items.length; i++) {
			this.items[i].Expand();
		}
		
		this.RefreshCollapseControls();
	},
	
	Handle_ClickCollapse:function()
	{
		this.expanded = false;
		
		for(var i=0; i < this.items.length; i++) {
			this.items[i].Collapse();
		}
		
		this.RefreshCollapseControls();
	},
	
	Expand:function()
	{
		this.dialog.element(this.rowID).show();
	},
	
	Collapse:function()
	{
		this.dialog.element(this.rowID).hide();
	},
	
   /**
    * Adds a new item to select.
    * 
    * @param {String} id
    * @param {String} name
    * @param {String} description
    * @returns {Dialog_SelectItems_Item}
    */
	AddItem:function(id, name, description)
	{
		var item = new Dialog_SelectItems_Item(this.dialog, id, name, description, this);
		this.items.push(item);
		return item;
	},
	
	HasItem:function(item_id)
	{
		var item = this.GetItem(item_id);
		if(item != null) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Retrieves an item instance by its ID. Recurses into
    * subitems as well.
    * 
    * @param {String} item_id
    * @returns {Dialog_SelectItems_Item|NULL}
    */
	GetItem:function(item_id)
	{
		for(var i=0; i < this.items.length; i++) {
			if(this.items[i].id == item_id) {
				return this.items[i];
			} else if(this.items[i].HasItem(item_id)) {
				return this.items[i].GetItem(item_id);
			}
		}
		
		return null;
	},
	
   /**
    * Handles the item being clicked: toggles the 
    * selection state, and handles the state of any
    * subitems as well: if selected/deselected, this
    * is propagated to all subitems.
    */
	Handle_Clicked:function()
	{
		this.Toggle();

		if(this.IsSelected()) {
			if(this.items.length > 0) {
				for(var i=0; i < this.items.length; i++) {
					this.items[i].Select();
				}
			}

			if(this.parentItem != null) {
				this.parentItem.Handle_SubitemSelected();
			}
		} else {
			if(this.items.length > 0) {
				for(var i=0; i < this.items.length; i++) {
					this.items[i].Deselect();
				}
			}

			if(this.parentItem != null) {
				this.parentItem.Handle_SubitemDeselected();
			}
		}
	},
	
   /**
    * Toggles the item's selection state: if selected,
    * it is deselected and vice versa.
    */
	Toggle:function()
	{
		if(this.IsSelected()) {
			this.Deselect();
		} else {
			this.Select();
		}
	},
	
	IsSelected:function()
	{
		var row = this.dialog.element(this.rowID);
		return row.hasClass('selected');	
	},
	
	Select:function()
	{
		var row = this.dialog.element(this.rowID);
		row.addClass('selected');
	},
	
	Deselect:function()
	{
		var row = this.dialog.element(this.rowID);
		row.removeClass('selected');
	},
	
   /**
    * Is a subitem has been selected, check if all
    * available subitems are now selected: in that
    * case, select the parent item as well.
    */
	Handle_SubitemSelected:function()
	{
		var selectedCount = 0;
		for(var i=0; i < this.items.length; i++) {
			if(this.items[i].IsSelected()) {
				selectedCount++;
			}
		}
		
		if(selectedCount == this.items.length) {
			this.Select();
		}
	},
	
   /**
    * If any of the item's subitems has been deselected,
    * make sure the item is not selected anymore.
    */
	Handle_SubitemDeselected:function()
	{
		this.Deselect();
	},
	
	HasSubitems:function()
	{
		if(this.items.length > 0) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Sets a property of the item, which has no functionality
    * beyond storing data in the item to retrieve later as needed.
    * 
    * @param {String} name
    * @param {Mixed} value
    * @see GetProperty()
    */
	SetProperty:function(name, value)
	{
		this.properties[name] = value;
		return this;
	},
	
	GetProperty:function(name)
	{
		if(typeof(this.properties[name]) != 'undefined') {
			return this.properties[name];
		}
		
		return null;
	}
};

Dialog_SelectItems_Item = Class.extend(Dialog_SelectItems_Item);