<?php

echo sb()
    ->tag('h3', t('Color styles'))
    ->add(UI::button('Default'))
    ->add(UI::button('Primary')->makePrimary())
    ->add(UI::button('Warning')->makeWarning())
    ->add(UI::button('Success')->makeSuccess())
    ->add(UI::button('Info')->makeInfo())
    ->add(UI::button('Inverted')->makeInverse())
    ->add(UI::button('Dangerous')->makeDangerous())
    ->add(UI::button('Developer')->makeDeveloper())

    ->tag('h3', t('Sizes'))
    ->add(UI::button('Large')->makeLarge())
    ->add(UI::button('Normal'))
    ->add(UI::button('Small')->makeSmall())
    ->add(UI::button('Mini')->makeMini())

    ->tag('h3', t('With icons'))
    ->add(UI::button('Large')->makeLarge()->setIcon(UI::icon()->add()))
    ->add(UI::button('Normal')->setIcon(UI::icon()->add()))
    ->add(UI::button('Small')->makeSmall()->setIcon(UI::icon()->add()))
    ->add(UI::button('Mini')->makeMini()->setIcon(UI::icon()->add()));
