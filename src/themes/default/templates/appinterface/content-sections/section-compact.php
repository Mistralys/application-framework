<?php
/* @var $this UI_Page_Template */

for($i=0; $i < 9; $i++) {
    $this->createSection()
        ->setTitle('Section '.$i.' title')
        ->setTagline('Section title tagline')
        ->setAbstract('Abstract text lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ')
        ->setContent('<p>Arbitrary HTML content here</p>')
        ->makeCompact()
        ->collapse()
        ->display();
}

?>