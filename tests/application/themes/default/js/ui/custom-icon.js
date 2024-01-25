/**
* UI Icon handling class: offers an easy-to-use API
* to create icons for common application tasks.
*
* @package TestDriver
* @subpackage User Interface
* @author Sebastian Mordziol <s.mordziol@mistralys.eu>
* @class
*
* @template-version 1
*/
var CustomIcon =
{
    // region: Icon methods
    
    Planet:function() { return this.SetType('globe-europe', 'fas'); },
    Revisionable:function() { return this.SetType('rev', 'fab'); },

    // endregion
};

CustomIcon = UI_Icon.extend(CustomIcon);
