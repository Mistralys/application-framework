<?php

declare(strict_types=1);

echo UI::getInstance()->createSection()
    ->setTitle('Informational message')
    ->makeInfoMessage('This is to inform you about something.');
