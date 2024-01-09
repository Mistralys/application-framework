<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('visual-select-example');

$el = $form->addMultiselect('multi-1', t('Multi-Select'));
$el->makeMultiple();
$el->enableFiltering();

$el->addOption('First item', 'first');
$el->addOption('Second item', 'second');
$el->addOption('Third item', 'third');
$el->addOption('Fourth item', 'fourth');
$el->addOption('Fifth item', 'fifth');
$el->addOption('Sixth item', 'sixth');

echo $form;
