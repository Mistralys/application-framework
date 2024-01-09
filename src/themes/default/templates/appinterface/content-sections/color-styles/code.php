<?php

declare(strict_types=1);

UI::getInstance()->createSection()
    ->setTitle('Dangerous section')
    ->setTagline('Section title tagline')
    ->setContent('<p>This entire section is styled to display information on dangerous tasks.</p>')
    ->makeDangerous()
    ->display();
