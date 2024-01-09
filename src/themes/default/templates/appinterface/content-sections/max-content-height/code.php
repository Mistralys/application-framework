<?php

declare(strict_types=1);

$para = (string)sb()->para('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.');

echo UI::getInstance()->createSection()
    ->setTitle('Height-limited section')
    ->setIcon(UI::icon()->text())
    ->setMaxBodyHeight('200px')
    ->setContent(str_repeat($para, 30))
    ->expand();
