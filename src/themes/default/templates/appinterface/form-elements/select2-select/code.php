<?php

declare(strict_types=1);

use UI\CSSClasses;

$form = UI::getInstance()->createForm('select2-select-example');

$el1 = $form->addSelect('test-select2-serverside', t('Serverside'));
$el1->addClass(CSSClasses::INPUT_SELECT_FILTERABLE);
$el1->setComment(t('Filtering capability added by the CSS class: %s', CSSClasses::INPUT_SELECT_FILTERABLE));

$el1->addOption('First item', 'first');
$el1->addOption('Second item', 'second');
$el1->addOption('Third item', 'third');
$el1->addOption('Fourth item', 'fourth');
$el1->addOption('Fifth item', 'fifth');
$el1->addOption('Sixth item', 'sixth');

$form->addHTML('<hr>');

$el2 = $form->addSelect('test-select2-clientside', t('Clientside'));
$el2->setComment(t('Filtering capability added at runtime by clicking the button below.'));

$el2->addOption('First item', 'first');
$el2->addOption('Second item', 'second');
$el2->addOption('Third item', 'third');
$el2->addOption('Fourth item', 'fourth');
$el2->addOption('Fifth item', 'fifth');
$el2->addOption('Sixth item', 'sixth');

$form->addButton('make_filterable')
    ->setLabel(t('Make filterable'))
    ->makePrimary()
    ->click(
        $form->renderJSSelectFilterable('#'.$el2->getId()).
        sprintf(";alert('%s');", t('Filtering enabled!'))
    );

echo $form;
