<?php

    /* @var $this UI_Page_Template */

    $title = $this->getVar('title');
    
    if(!empty($title)) {
        echo 
        '<h4 class="section-heading">'.
            $title.
        '</h4>';
    }