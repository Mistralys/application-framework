<?php

    /* @var $this UI_Page_Template */

    $items = $this->getVar('items');

    echo 
    '<p>'.
        t('Welcome to the developer tools.').
    '</p>'.
    '<ul>';
        foreach($items as $category => $scripts) {
            echo
            '<li>'.
                $category.
                '<ul>';
                    foreach($scripts as $urlName => $label) {
                        $url = $this->buildURL(array(
                            'page' => 'devel',
                            'mode' => $urlName
                        ));
                        
                        echo
                        '<li>'.
                            '<a href="'.$url.'">'.
                                $label.
                            '</a>'.
                        '</li>';
                    }
                    echo
                '</ul>'.
            '</li>';
        }
        echo 
    '</ul>';