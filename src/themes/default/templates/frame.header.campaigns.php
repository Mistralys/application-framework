<?php 

	/* @var $this UI_Page_Template */

	$campaigns = call_user_func(array(APP_CLASS_NAME.'_Campaigns', 'getInstance'));
	$campaign = $campaigns->getSelectedCampaign();
	$isDefault = $campaign->isDefault();
	
	$editingCampaigns = false;
	$area = $this->driver->getActiveArea();
	if($area->getID()=='Campaigns') {
		$editingCampaigns = true;
	}
	
	$this->ui->addJavascript('campaigns.js');
	$items = $campaigns->getAll();
	foreach($items as $item) {
	    if($item->isDefault()) {
	        continue;
	    }
	    
	    $this->ui->addJavascriptHeadStatement(
	        'Campaigns.Register',
	        $item->getID(),
	        $item->getLabel(),
	        $item->getAlias(),
	        $item->getOwner()->getName(),
	        $item->getSwitchURL()
        );
	}
	
	$this->ui->addJavascriptHeadVariable('Campaigns.ActiveID', $campaign->getID());
	$this->ui->addJavascriptHeadVariable('Campaigns.ActiveAlias', $campaign->getAlias());
	$this->ui->addJavascriptHeadVariable('Campaigns.ActiveLabel', $campaign->getLabel());
	$this->ui->addJavascriptHeadVariable('Campaigns.IsDefault', $campaign->isDefault());
	
	$campaignClass = '';
	
	if($editingCampaigns)
	{
	    $campaignClass = 'campaign-disabled';
	    
	    $content = 
   	    '<span id="navcampaign">'.
	        UI::icon()->information(). ' ' . 
   	        t('The campaign selection is not available while editing campaigns.').
	    '</span>';
	}
	else if($isDefault)
	{
	    $campaignClass = 'campaign-default';
	    
	    $title = t('Click to select a campaign to switch to.');
	    $label = UI::icon()->switchCampaign() . ' ' . t('Select a campaign...');
	    
	    $content = 
	    '<span id="navcampaign" class="clickable" onclick="Campaigns.DialogSwitch()">'.UI::icon()->information() . ' ' . t('No campaign selected.').'</span>'.
	    '<div class="campaign-context">'.
	       '<a href="javascript:void(0)" onclick="Campaigns.DialogSwitch()">'.
	           $label.
           '</a>'.
	    '</div>';
	}
	else
	{
	    $campaignClass = 'campaign-active';

    	$menu = $this->ui->createDropdownMenu();
    	$menu->setAttribute('aria-labelledby', 'navcampaign');
    	$menu->setAttribute('role', 'menu');
    	
	    $menu->addLink(t('Back to current'), $campaigns->getResetURL())->setIcon(UI::icon()->backToCurrent());
	    $menu->addSeparator();
	    $menu->addClickable(t('Switch campaign...'), 'Campaigns.DialogSwitch()')->setIcon(UI::icon()->switchCampaign());
	    $menu->addSeparator();
	    $menu->addLink(t('Manage this campaign'), $campaign->getAdminEditStatusURL())->setIcon(UI::icon()->campaigns());
	    $menu->addLink(t('Campaign management'), $campaigns->getAdminURL())->setIcon(UI::icon()->campaigns());
	
	    $name = $campaign->getLabel();
	
	    $title = t('You are editing campaign-specific contents.');
	    $label = UI::icon()->campaigns() . ' ' . t('Active campaign:') . ' ' . $name . ' ' . UI::icon()->dropdown();
	    
	    $content = 
   	    '<div class="campaign-context">'.
            '<a href="'.$campaigns->getResetURL().'">'.
                UI::icon()->backToCurrent() . ' ' .
                t('Back to current').
            '</a>'.
        '</div>'.
        '<ul class="campaign-selector-menu">'.
            sprintf(
                '<li class="dropdown">'.
                    '<a id="navcampaign" class="nav-group dropdown-toggle" data-toggle="dropdown" href="#" title="%s">%s</a>'.
                    '%s'.
                '</li>',
                $title,
                $label,
                $menu->render()
            ).
        '</ul>';
	}
	
    $html =
    '<div class="campaign-selector '.$campaignClass.'">'.
        '<div class="container">'.
            $content.
        '</div>'.
    '</div>';
        
    echo $html;
