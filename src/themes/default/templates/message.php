<?php

	/* @var $this UI_Page_Template */

    $type = $this->getVar('type');

	$dismiss = '';
	if($this->getVar('dismissable') === true) 
	{
		$dismiss = '<button type="button" class="close" data-dismiss="alert">&times;</button>';
	}

	$icons = array(
	    UI::MESSAGE_TYPE_SUCCESS => UI::icon()->ok(),
	    UI::MESSAGE_TYPE_WARNING => UI::icon()->warning(),
	    UI::MESSAGE_TYPE_ERROR => UI::icon()->warning(),
	    UI::MESSAGE_TYPE_INFO => UI::icon()->information()
	);
	
	$iconType = $this->getVar('icon');
	
	$icon = '';
	if($iconType === true && isset($icons[$type])) 
	{
	    $icon = $icons[$type];
	} 
	else if($iconType instanceof UI_Icon) 
	{
	    $icon = $iconType;
	}
	
	$classes = array_merge(
	    $this->getVar('classes'),
    	array(
    	    'alert',
    	    'alert-'.$type,
    	    'alert-layout-'.$this->getVar('layout')
    	)
    );

    $tag = 'div';
    if($this->getVar('inline') === true)
    {
        $tag = 'span';
        $classes[] = 'alert-inline';
    }
	
	echo
	'<'.$tag.' class="'.implode(' ', $classes).'">'.
		$dismiss.
		$icon.' '.$this->getVar('message').
	'</'.$tag.'>';
