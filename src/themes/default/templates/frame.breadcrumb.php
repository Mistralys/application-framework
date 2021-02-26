<?php

	/* @var $this UI_Page_Template */
	/* @var $breadcrumb UI_Page_Breadcrumb */
	/* @var $item UI_Page_Breadcrumb_Item */
	
	$breadcrumb = $this->getVar('breadcrumb');
	
	$items = $breadcrumb->getItems();
	
	$html =
	'<ul class="breadcrumb">';
		foreach($items as $item) {
			$class = 'regular';
			
			if($item->isFirst()) {
				$class = 'first';
			}
			
			$divider = ' <span class="divider">/</span>';
			if($item->isLast()) {
				$class = 'active last';
				$divider = '';
			}
			
			// bootstrap >2 does not need dividers anymore
			if(UI::getBoostrapVersion() > 2) {
			    $divider = '';
			}
			
			$html .=
			'<li class="breadcrumb-item '.$class.'">'.
				$item->render().
				$divider.
			'</li>';
		}
		$html .=
	'</ul>';
		
	echo $html;