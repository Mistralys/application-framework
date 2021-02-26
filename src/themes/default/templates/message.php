<?php

	/* @var $this UI_Page_Template */

    $type = $this->getVar('type');

	$dismiss = '';
	if($this->getVar('dismissable') === true) 
	{
		$dismiss = '<button type="button" class="close" data-dismiss="alert">&times;</button>';
	}

	$icons = array(
	    UI::MESSAGE_TYPE_SUCCESS => 'OK',
	    UI::MESSAGE_TYPE_WARNING => 'WARNING',
	    UI::MESSAGE_TYPE_ERROR => 'WARNING',
	    UI::MESSAGE_TYPE_INFO => 'INFORMATION'
	);
	
	$iconType = $this->getVar('icon');
	
	$icon = '';
	if($iconType === true && isset($icons[$type])) 
	{
	    $icon = UI::icon()->setType($icons[$type]);
	} 
	else if($iconType instanceof UI_Icon) 
	{
	    $icon = $iconType->render();
	}
	
	$classes = array_merge(
	    $this->getVar('classes'),
    	array(
    	    'alert',
    	    'alert-'.$type,
    	    'alert-layout-'.$this->getVar('layout')
    	)
    );
	
	echo
	'<div class="'.implode(' ', $classes).'">'.
		$dismiss.
		$icon.' '.$this->getVar('message').
	'</div>';