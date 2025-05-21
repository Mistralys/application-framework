"use strict";

/**
 * Custom exception class for application-related errors, with
 * additional information like developer-specific error details.
 *
 * @package Application
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ApplicationException  
{
  /**
   * @param {String} message
   * @param {String} develinfo
   * @param {Number} code
   */
	constructor(message, develinfo, code)
	{
		this.Message = message;
		this.DeveloperInfo = develinfo;
		this.Code = code;
		this.Trace = '';

		const err = new Error();
		if(typeof err.stack !== 'undefined') {
			this.Trace = err.stack.toString();
		}
	}
	
	GetCode()
	{
		return this.Code;
	}

	/**
	 *
	 * @return {String}
	 * @constructor
	 */
	GetTrace()
	{
		return this.Trace;
	}
	
	GetMessage()
	{
		return this.Message;
	}
	
	GetDeveloperInfo()
	{
		return this.DeveloperInfo;
	}
	
	Display()
	{
		application.log('EXCEPTION [' + this.Code + ']', this.Message + ' | ' + this.DeveloperInfo, 'error');
		
		if(!User.isDeveloper()) {
			alert(t('An error occurred, with the following message:')+'\n'+this.Message);
		} 
	}
	
	ShowDialog()
	{
		application.createDialogErrorMessage(
			this.Message, 
			this.DeveloperInfo, 
			this.Code
		).Show();
	}
	
	toString()
	{
		let text = 'APPLICATION | '+this.Message;
		if(User.isDeveloper()) {
			text += ' | '+this.DeveloperInfo;
		}
		
		return text;
	}
}
