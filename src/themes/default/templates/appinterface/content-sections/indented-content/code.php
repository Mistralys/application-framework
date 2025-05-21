<?php

declare(strict_types=1);

UI::getInstance()->createSection()
    ->setTitle('Section title')
    ->makeContentIndented()
    ->setContent('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>')
    ->display();
