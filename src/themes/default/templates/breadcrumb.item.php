<?php

	/* @var $this UI_Page_Template */
	/* @var $item UI_Page_Breadcrumb_Item */
	
	$item = $this->getVar('item');

	// the last item in the breadcrumb is never linked.
	if($item->isLast()) {
		echo $item->getLabel();
		return;
	}

	switch($item->getMode())
	{
		case 'linked':
			echo 
			'<a id="breadcrumb-' . nextJSID() . '" href="'.$item->getURL().'">'.
				$item->getLabel().
			'</a>';
			break;		
			
		case 'clickable':
			'<a id="breadcrumb-' . nextJSID() . '" href="javascript:void(0)" onclick="'.$item->getJavascript().'">'.
				$item->getLabel().
			'</a>';
			break;
	}