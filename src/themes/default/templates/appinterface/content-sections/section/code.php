<?php

declare(strict_types=1);

UI::getInstance()->createSection()
    ->setTitle('Section title')
    ->setTagline('Section title tagline')
    ->setAbstract('Abstract text lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ')
    ->setContent('<p>Arbitrary HTML content here</p>')
    ->display();
