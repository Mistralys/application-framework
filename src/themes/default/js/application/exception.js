/**
 * Custom exception class for application-related errors, with
 * additional information like developer-specific error details.
 *
 * @package Application
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var ApplicationException = 
{
	'Message':null,
	'DeveloperInfo':null,
	'Code':null,

  /**
   * @param message
   * @param develinfo
   * @param code
   */
	init:function(message, develinfo, code)
	{
		this.Message = message;
		this.DeveloperInfo = develinfo;
		this.Code = code;
	},
	
	GetCode:function()
	{
		return this.Code;
	},
	
	GetMessage:function()
	{
		return this.Message;
	},
	
	GetDeveloperInfo:function()
	{
		return this.DeveloperInfo;
	},
	
	Display:function()
	{
		application.log('EXCEPTION [' + this.Code + ']', this.Message + ' | ' + this.DeveloperInfo, 'error');
		
		if(!User.isDeveloper()) {
			alert(t('An error occurred, with the following message:')+'\n'+this.Message);
		} 
	},
	
	ShowDialog:function()
	{
		application.createDialogErrorMessage(
			this.Message, 
			this.DeveloperInfo, 
			this.Code
		).Show();
	},
	
	toString:function()
	{
		var text = 'APPLICATION | '+this.Message;
		if(User.isDeveloper()) {
			text += ' | '+this.DeveloperInfo;
		}
		
		return text;
	}
};

ApplicationException = Class.extend(ApplicationException);