<?php

declare(strict_types=1);

use Mistralys\Examples\UserInterface\ExampleFile;

echo sb()
    ->tag('h3', t('Without a reason'))
    ->add(UI::button(t('No action'))->disable())
    ->add(UI::button(t('With a link'))->link(ExampleFile::buildURL())->disable())
    ->add(UI::button(t('With a click handler'))->click("alert('clicked')")->disable())

    ->tag('h3', t('With a reason (hover to see tooltip)'))
    ->add(UI::button(t('No action'))->disable(t('This action is currently unavailable.')))
    ->add(UI::button(t('With a link'))->link(ExampleFile::buildURL())->disable(t('Navigation is currently unavailable.')))
    ->add(UI::button(t('With a click handler'))->click("alert('clicked')")->disable(t('This action is currently unavailable.')));
