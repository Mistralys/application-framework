<?php

	/* @var $this UI_Page_Template */

	$this->page->createSidebarSection()
	   ->setTitle(t('Filter the list'))
	   ->setContent($this->getVar('form-html'))
	   ->display();
