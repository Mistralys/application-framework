<?php

	/* @var $this UI_Page_Template */
	/* @var $form UI_Form */

	$form = $this->getVar('form-object');

	echo $this->renderSection(
	    $form->renderHorizontal(), 
	    $form->getTitle(), 
	    $form->getAbstract()
    );