/**
 * Switch form element that works like a checkbox. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Switch = 
{
	'onValue':null,
	'offValue':null,
	'onLabel':null,
	'offLabel':null,
	'buttonSize':null,
	
	_init:function()
	{
		this.onValue = 'true';
		this.offValue = 'false';
		this.onLabel = t('On');
		this.offLabel = t('Off');
		this.buttonSize = null;
	},
		
	GetElementType:function()
	{
		return 'Switch';
	},
	
	GetDefaultValue:function()
	{
		return this.offValue;
	},
	
	MakeYesNo:function()
	{
		this.onValue = 'yes';
		this.offValue = 'no';
		this.onLabel = t('Yes');
		this.offLabel = t('No');

		var value = this.GetValue();
		if(value=='true') {
			value = 'yes';
		} else {
			value = 'no';
		}
		
		return this.SetValue(value);
	},
	
	IsYesNo:function()
	{
		if(this.onValue=='yes') {
			return true;
		}
		
		return false;
	},
	
	_Render:function()
	{
        var value = null;
        var onClass = '';
        var offClass = '';

        if (this.IsChecked()) {
            onClass = 'btn-success active';
            value = this.onValue;
        } else {
            offClass = 'btn-danger active';
            value = this.offValue;
        }

        if (this.buttonSize != null) {
            onClass += ' btn-' + this.buttonSize;
            offClass += ' btn-' + this.buttonSize;
        }

		var atts = this.GetAttributes();
		atts['type'] = this.type;
		atts['value'] = this.GetValue();

		var html = ''+
		'<div class="btn-group bootstrap-switch" id="'+this.id+'" data-value-on="'+this.onValue+'" data-value-off="'+this.offValue+'">' +
	        '<button id="' +this.id+ '-on" class="btn btn-small ' + onClass + '" type="button" onclick="switchElement.turnOn(\'' + this.id + '\')">' + this.onLabel + '</button>' +
	        '<input id="' + this.id + '-storage" type="hidden" name="' + this.name + '" value="' + value + '"/>' +
	        '<button id="' + this.id + '-off" class="btn btn-small ' + offClass + '" type="button" onclick="switchElement.turnOff(\'' + this.id + '\')">' + this.offLabel + '</button>' +
	    '</div>'+this.append;
		
		return html;
	},
	
	IsChecked:function()
	{
		if(this.GetValue()==this.onValue) {
			return true;
		}
		
		return false;
	},
	
	_GetLiveValue:function()
	{
		return switchElement.getValue(this.id);
	},
	
	SetValue:function(value)
	{
		// allow values to be set as booleans as well
		if(typeof(value) == 'boolean') {
			value = bool2string(value, this.IsYesNo());
		}
		
		this._super(value);
		
		return this;
	},
	
	TurnOn:function()
	{
		return this.SetValue(true);
	},
	
	TurnOff:function()
	{
		return this.SetValue(false);
	},
	
	_SetLiveValue:function(value)
	{
		if(value==this.onValue) {
			switchElement.turnOn(this.id);
		} else {
			switchElement.turnOff(this.id);
		}
	},
	
	_PostRender:function()
	{
		var category = this.GetAttribute('category');
        if (isEmpty(category)) {
            category = '_uncategorized';
        }
        
        var element = this;
        
		switchElement.register(this.id, category);
		switchElement.onChangeHandler(
			this.id, 
			function() {
				element.Handle_Change();
			}
		);
	}
};

FormHelper_Form_Element_Switch = FormHelper_Form_Element.extend(FormHelper_Form_Element_Switch);
