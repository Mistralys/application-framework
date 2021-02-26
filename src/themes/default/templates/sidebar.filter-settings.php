<?php

	/* @var $this UI_Page_Template */
	/* @var $settings Application_FilterSettings */

	$settings = $this->getVar('settings');
	
	$title = $this->getVar('title');
	if(empty($title)) {
	    $title = t('Filter settings');
	}
	
	$jsName = $settings->getJSName();
	
	$this->page->createSidebarSection()
	   ->setTitle($title)
	   ->setContent($this->getVar('html'))
	   ->addContextButton(
	       UI::button()
           ->setIcon(UI::icon()->add())
           ->click(sprintf('%s.DialogSave()', $jsName))
           ->makeMini()
           ->setTooltipText(t('Save current filter...'))
       )
       ->addContextButton(
           UI::button()
           ->setIcon(UI::icon()->load())
           ->click(sprintf('%s.DialogLoad()', $jsName))
           ->makeMini()
           ->setTooltipText(t('Load filter...'))
       )
	   ->display();
