<?php

	/* @var $this UI_Page_Template */
	/* @var $button UI_Page_Sidebar_Item_Button */
    /* @var $menu UI_Bootstrap_DropdownMenu */

	$button = $this->getVar('button');
	$confirmMessage = $this->getVar('confirmMessage');
	$menu = null;
	
	if($button instanceof UI_Page_Sidebar_Item_DropdownButton) {
	    $menu = $button->getMenu();
	}

	$tagname = 'button';
	$attributes = array(
		'id' => $button->getID(),
		'name' => $this->getVar('name'),
		'type' => 'button',
		'class' => 'btn btn-block',
	);

	$design = $this->getVar('design');
	if(empty($design)) {
	    $design = 'default';
	}
	
	$attributes['class'] .= ' btn-'.$design;
	
	$mode = $this->getVar('mode');
	
	if(!$menu) 
	{
	    $attributes['onclick'] = $this->getVar('onclick');

	    if($this->hasVar('loadingText')) {
	        $attributes['data-loading-text'] = $this->getVar('loadingText');
	    }
    	
	    if($confirmMessage instanceof UI_Page_Sidebar_Item_Button_ConfirmMessage) 
    	{
    	    $mode = 'clickable';
    	    $this->setVar('javascript', $confirmMessage->getJavaScript());
    	}
	}
	
	if($button->isLocked()) {
	    $button->disable();
	    $mode = 'clickable';
	    $attributes['class'] .= ' btn-locked';
	    $attributes['title'] = $button->getLockReason();
	    $this->setVar(
	        'javascript', 
	        'LockManager.DialogActionDisabled()'
        );
	}
	
	if($button->isDisabled()) 
	{
	    $attributes['class'] .= ' disabled';
	    $mode = 'none';
	}
	
	switch($mode)
	{
		case 'linked':
			$tagname = 'a';
			$attributes['href'] = $this->getVar('url');
			$target = $this->getVar('urlTarget');
			if(!empty($target)) {
			    $attributes['target'] = $target;
			}
			break;

		case 'submit':
			$attributes['type'] = 'submit';
			$attributes['value'] = $attributes['name'];
			break;

		case 'clickable':
			$attributes['onclick'] = $this->getVar('javascript');
			break;
			
		case 'dropmenu':
		    $this->ui->addJavascriptOnload(sprintf("$('#%s').dropdown()", $button->getID()));
		    $attributes['data-toggle'] = 'dropdown';
		    //$attributes['onclick'] = '';
		    break;
	}

	$icon = '';
	$iconObject = $this->getVar('icon');
	if($iconObject instanceof UI_Icon) {
		$icon = $iconObject->render().' ';
	}
	
	if($button->hasTooltip()) 
	{
	    $tIcon = UI::icon()
	    ->setTooltip($button->getTooltip());
	    
	    // use a different icon if the button is disabled
	    if($button->isDisabled()) {
	        $tIcon->warning();
	    } else {
	        $tIcon->help();
	    }
	    
	    $icon = 
	    '<span class="button-tooltip-icon">'.
	       $tIcon.
	    '</span>'.
        $icon;
	}
	
	$label = $button->getLabel();
	if($design=='developer') {
		$label = '<b>'.t('DEV:').'</b> '.$label;
	}
	
	if($menu && $button->hasCaret()) {
	    $label .= ' <span class="caret"></span>';
	}

	if($tagname=='a' && isset($attributes['type'])) {
		unset($attributes['type']);
	}

	$html =
	'<'.$tagname.' '.compileAttributes($attributes).'>'.
	   $icon.$label.
	'</'.$tagname.'>';
	
    if($menu) {
        $html = 
        '<span style="position:relative">'.
	        $html.
            $menu->render().
        '</span>';
	}

	echo $html;
	