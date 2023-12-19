<?php

declare(strict_types=1);

for($i=0; $i < 5; $i++)
{
    echo UI::getInstance()->createSection()
        ->setTitle('Section '.($i+1).' title')
        ->setTagline('Section title tagline')
        ->setAbstract('Abstract text lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ')
        ->setContent('<p>Arbitrary HTML content here</p>')
        ->makeCompact()
        ->collapse();
}
