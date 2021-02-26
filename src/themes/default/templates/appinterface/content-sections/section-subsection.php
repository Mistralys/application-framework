<?php
/* @var $this UI_Page_Template */

$this->page->createSubsection()
->setTitle('Subsection title, not collapsible')
->setContent('<p>Some content here.</p>')
->display();

$this->page->createSubsection()
->setTitle('Subsection title, collapsible')
->setCollapsed(false)
->setContent('<p>Some content here.</p>')
->display();