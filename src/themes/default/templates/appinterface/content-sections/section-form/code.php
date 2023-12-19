<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('dummy-section-form');
$form->addAlias('alias');

echo UI::getInstance()->createSection()
    ->setTitle('Section with form')
    ->setIcon(UI::icon()->text())
    ->addForm($form);
