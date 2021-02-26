<?php

	/* @var $this UI_Page_Template */
	/* @var $sidebar UI_Page_Sidebar */

	$sidebar = $this->getVar('sidebar');

	echo 
   	'<'.$sidebar->getTagName().' class="'.implode(' ', $sidebar->getClasses()).'" id="sidebar">'.
	   '<div class="sidebar-wrap">';
    		$items = $sidebar->getItems();
    		foreach($items as $item) {
    			$item->display();
    		}
    		echo 
		'</div>'.
	'</'.$sidebar->getTagName().'>';