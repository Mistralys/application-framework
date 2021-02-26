/**
 * Global campaigns management class: automatically present
 * when in the application's campaigns administration.
 * 
 * @class
 * @memberof Maileditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Campaigns = 
{
	'ActiveID':null, // set serverside
	'ActiveAlias':null, // set serverside
	'ActiveLabel':null, // set serverside
		
	'items':[],
	'elDialogSwitch':null,
		
	Register:function(id, label, alias, owner, switchUrl)
	{
		this.items.push({
			'id':id,
			'label':label,
			'alias':alias,
			'owner':owner,
			'url':switchUrl
		});
	},
	
	DialogSwitch:function()
	{
		if(this.elDialogSwitch != null) {
			this.elDialogSwitch.Show();
			return;
		}
		
		var dialog = new Dialog_SelectItems();
		dialog.SetItemLabel(t('Campaign'), t('Campaigns'));
		dialog.SetTitle(t('Select a campaign'));
		dialog.SetButtonLabel(t('Select'));
		dialog.SetIcon(UI.Icon().SwitchCampaign());
		dialog.MakeSingleSelect();
		dialog.SetConfirmHandler(function(ids) {
			Campaigns.Handle_SwitchCampaign(ids[0]);
		});
		 
		$.each(this.items, function(idx, campaign) {
			dialog.AddItem(
				campaign.id,
				campaign.label,
				campaign.alias + ' | ' + t('Created by %1$s.', campaign.owner)
			);
		});
		 
		this.elDialogSwitch = dialog;
		dialog.Show();
	},
	
	Handle_SwitchCampaign:function(id)
	{
		
		
		$.each(this.items, function(idx, campaign) {
			if(campaign.id == id) {
				application.showLoader(t('Please wait, switching campaign...'));
				application.redirect(campaign.url);
			}
		});
	}
};