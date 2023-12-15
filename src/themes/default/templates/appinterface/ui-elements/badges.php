<?php

echo sb()

    ->tag('h3', t('Badges'))
    ->add(UI::badge(t('Default')))
    ->add(UI::badge(t('Success'))->makeSuccess())
    ->add(UI::badge(t('Dangerous'))->makeDangerous())
    ->add(UI::badge(t('Warning'))->makeWarning())
    ->add(UI::badge(t('Info'))->makeInfo())
    ->add(UI::badge(t('Inactive'))->makeInactive())
    ->add(UI::badge(t('Inverse'))->makeInverse())

    ->tag('h3', t('Labels'))
    ->add(UI::label(t('Default')))
    ->add(UI::label(t('Success'))->makeSuccess())
    ->add(UI::label(t('Dangerous'))->makeDangerous())
    ->add(UI::label(t('Warning'))->makeWarning())
    ->add(UI::label(t('Info'))->makeInfo())
    ->add(UI::label(t('Inactive'))->makeInactive())
    ->add(UI::label(t('Inverse'))->makeInverse())

    ->tag('h3', t('Sizes'))
    ->add(UI::badge(t('Large'))->makeLarge())
    ->add(UI::badge(t('Small'))->makeSmall())

    ->tag('h3', t('With icons'))
    ->add(UI::badge(t('Large'))->makeLarge()->setIcon(UI::icon()->add()))
    ->add(UI::badge(t('Normal'))->setIcon(UI::icon()->add()))
    ->add(UI::badge(t('Small'))->makeSmall()->setIcon(UI::icon()->add()));
