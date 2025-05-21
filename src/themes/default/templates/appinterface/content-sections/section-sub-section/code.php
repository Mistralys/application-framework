<?php

declare(strict_types=1);

$section = UI::getInstance()->createSection()
    ->setTitle('Main section');

$section->addSubsection()
    ->setTitle('First subsection')
    ->setContent('<p>Some content here.</p>')
    ->collapse();

$section->addSubsection()
    ->setTitle('With context buttons')
    ->setContent('<p>Some content here.</p>')
    ->addContextButton(
        UI::button(t('Edit'))
            ->setIcon(UI::icon()->edit())
    )
    ->addContextButton(
        UI::button(t('Delete'))
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
    )
    ->collapse();

$section->addSubsection()
    ->setTitle('Tagline and buttons')
    ->setTagline('Section title tagline')
    ->setContent('<p>Some content here.</p>')
    ->addContextButton(
        UI::button(t('Edit'))
            ->setIcon(UI::icon()->edit())
    )
    ->addContextButton(
        UI::button(t('Delete'))
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
    )
    ->collapse();

echo $section;