/**
 * Represents the current user. This is available everywhere and is
 * populated automatically by the application.
 * 
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @static
 * @class User
 */
var User =
{
	/**
	 * The user's ID.
	 * 
	 * @property {Number} id
	 */
	'id':null,
	
   /**
    * The user's full name, i.e. firstname and lastname.
    * 
    * @property {String} name
    */
	'name':null,
	
   /**
    * The user's first name.
    * 
    * @property {String} firstname
    */
	'firstname':null,
	
   /**
    * The user's last name.
    * 
    * @property {String} lastname
    */
	'lastname':null,
	
	'rights':{},

	addRights:function(rights)
	{
		for(var i=0; i<rights.length; i++) {
			this.rights[rights[i]] = true;
		}
	},

	canEditProductType:function()
	{
		return this.hasRight('EditProductType');
	},

	canEditSortingList:function()
	{
		return this.hasRight('EditSorting');
	},

	canSortProducts:function()
	{
		return this.hasRight('SortSorting');
	},

   /**
    * Checks whether the user is a developer.
    *
    * @returns {Boolean}
    */
	isDeveloper:function()
	{
		if(application.demoMode) {
			return false;
		}
		
		return this.hasRight('Developer');
	},

   /**
    * Checks whether the user has the specified right, e.g. "EditItem"
    *
    * @returns {Boolean}
    */
	hasRight:function(name)
	{
		if(typeof(this.rights[name])!='undefined') {
			return true;
		}

		return false;
	}
};