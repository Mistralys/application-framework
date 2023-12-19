<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('example-elements');

$form->addText('text', t('Text input'));

$form->addPrimarySubmit(t('Primary button'), 'primary')
    ->setIcon(UI::icon()->ok());

$form->addSubmit(t('Secondary button'), 'secondary')
    ->setIcon(UI::icon()->check());

$form->addSubmit(t('Cautionary button'), 'warning')
    ->setIcon(UI::icon()->warning())
    ->makeWarning();

echo $form;
