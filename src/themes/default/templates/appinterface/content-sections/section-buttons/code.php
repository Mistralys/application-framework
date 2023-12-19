<?php

declare(strict_types=1);

echo UI::getInstance()->createSection()
    ->setTitle('Section with context buttons')
    ->setContent('<p>Some content here.</p>')
    ->addContextButton(
        UI::button('Settings')
           ->setIcon(UI::icon()->settings())
    )
    ->addContextButton(
        UI::button('Options')
            ->setIcon(UI::icon()->options())
    );
