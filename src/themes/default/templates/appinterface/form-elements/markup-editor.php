<?php

declare(strict_types=1);

DBHelper::startTransaction();

// No countries by default, so we add one. German is available
// as translation for the PasteAsPlainText plugin.
$country = Application_Countries::getInstance()->createNewCountry('de', 'German');

$form = new Application_Formable_Generic();
$form->createFormableForm('markup-editor-example');

$el = $form->addElementTextarea('markup', t('Markup Editor'));

// Attach the markup editor to the element.
$editor = $form->getFormInstance()->makeCKEditor($el, $country);

// Add a custom plugin: This is not enabled by default
// in the editor.
$editor->addPlugin('Alignment');
$editor->insertButtonAfter('alignment', UI_MarkupEditor_CKEditor::BUTTON_SUPERSCRIPT);

$form->display();

DBHelper::rollbackTransaction();
