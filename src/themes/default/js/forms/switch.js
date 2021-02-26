/**
 * Handles the bootstrap switch elements: provides an API
 * to turn them on or off, as well as utility methods to
 * toggle whole groups of buttons.
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class switchElement
 * @static
 */
var switchElement =
{
	'elements':{},

   /**
    * Registers an element in the page. This is done automatically by
    * the serverside script whenever a switch element is present in a form.
    *
    * @param {String} elID
    * @param {String} category A category name to group elements together. Default is "_uncategorized".
    */
	register:function(elID, category)
	{
		if(typeof(this.elements[category]) == 'undefined') {
			this.elements[category] = [];
		}
		
		this.elements[category].push(elID);
	},
	
	getOnValue:function(elID)
	{
		return $('#'+elID).attr('data-value-on');
	},
	
	getOffValue:function(elID)
	{
		return $('#'+elID).attr('data-value-off');
	},

   /**
    * Switches the target element to the "On/Yes" position.
    *
    * @param {String} elID
    */
	turnOn:function(elID)
	{
		var on = $('#'+elID+'-on');
		var off = $('#'+elID+'-off'); 
		var onValue = this.getOnValue(elID);
		
		on.addClass('active btn-success').removeClass('btn-default');
		off.removeClass('active btn-danger').addClass('btn-default');
		
		// remove the active button from the tabindex so the user does not
		// have to tab over both, and restore the tabinxed of the other button
		// in case it had been deactivated before.
		on.attr('tabindex', '-1');
		off.removeAttr('tabindex');
		
		$('#'+elID+'-storage').val(onValue);

		this.executeHandler(elID, true);
	},

   /**
    * Switches the target element to the "Off/No" position.
    *
    * @param {String} elID
    */
	turnOff:function(elID)
	{
		var on = $('#'+elID+'-on');
		var off = $('#'+elID+'-off'); 
		var offValue = this.getOffValue(elID);
		
		on.removeClass('active btn-success').addClass('btn-default');
		off.addClass('active btn-danger').removeClass('btn-default');
		
		on.removeAttr('tabindex');
		off.attr('tabindex', '-1');
		
		$('#'+elID+'-storage').val(offValue);

		this.executeHandler(elID, false);
	},

   /**
    * Switches all elements in the target category to "On/Yes".
    *
    * @param {String} category The category name the elements were registered for.
    */
	multiOn:function(category)
	{
		if(typeof(this.elements[category]) == 'undefined') {
			return;
		}
		
		$.each(this.elements[category], function(idx, elID) {
			switchElement.turnOn(elID);
		});
	},

   /**
    * Switches all elements in the target category to "Off/No".
    *
    * @param {String} category The category name the elements were registered for.
    */
	multiOff:function(category)
	{
		if(typeof(this.elements[category]) == 'undefined') {
			return;
		}
		
		$.each(this.elements[category], function(idx, elID) {
			switchElement.turnOff(elID);
		});
	}, 
	
	executeHandler:function(elID, active)
	{
		if(typeof(this.onChangeHandlers[elID]) == 'undefined') {
			return;
		}

		var def = this.onChangeHandlers[elID];
		
		def['handler'](
			elID,
			active,
			def['data']
		);
	},

	'onChangeHandlers':{},

	onChangeHandler:function(elID, handler, data)
	{
		if(typeof(data)=='undefined') {
			data = null;
		}

		this.onChangeHandlers[elID] = {
			'handler':handler,
			'data':data
		};
	},
	
	getValue:function(elID)
	{
		return $('#'+elID+'-storage').val();
	}
};