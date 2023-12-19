<?php

use AppUtils\Microtime;

$grid = UI::getInstance()->createPropertiesGrid();

$grid->add(t('Custom'), t('Freeform content'));

$grid->add(t('With comment'), t('Freeform content'))
    ->setComment(t('This is a comment'));

$grid->add(t('With help'), t('Freeform content'))
    ->setHelpText(t('This is a help text'));

$grid->addBoolean(t('Boolean'), true)
    ->makeYesNo();

$grid->addDate(t('Date'), Microtime::createNow())
    ->withDiff()
    ->withTime();

$grid->addAmount(t('Amount'), 123.45);

$grid->addByteSize(t('Byte size'), 123456789);

$grid->addHeader(t('Header'));

$grid->addMerged(t('Merged cell content'));

$grid->addMarkdown('**Markdown** formatted text');

$grid->addMessage(t('This is a success message'))
    ->makeSuccess();

echo $grid;
