<?php

declare(strict_types=1);

DBHelper::startTransaction();

// No countries by default, so we add one. German is available
// as translation for the PasteAsPlainText plugin.
$countries = Application_Countries::getInstance();
if($countries->isoExists('de')) {
    $country = $countries->getByISO('de');
} else {
    $country = $countries->createNewCountry('de', 'German');
}

$form = new Application_Formable_Generic();
$form->createFormableForm('markup-editor-example');

// ---------------------------------------------------------------
// MARKUP EDITOR 1
// ---------------------------------------------------------------

$el = $form->addElementTextarea('markup', t('Markup Editor'));
$el->setComment(t('With the align buttons as a dropdown menu.'));

// Attach the markup editor to the element.
$editor = $form->getFormInstance()->makeCKEditor($el, $country);

// Activate the alignment plugin: This is not enabled by default
// in the editor (but it is bundled in the build).
$editor->addPlugin('Alignment');
$editor->insertButtonAfter(UI_MarkupEditor_CKEditor::BUTTON_ALIGN, UI_MarkupEditor_CKEditor::BUTTON_SUPERSCRIPT);

// ---------------------------------------------------------------
// MARKUP EDITOR 2
// ---------------------------------------------------------------

$el = $form->addElementTextarea('markup-two', t('Markup Editor'));
$el->setComment(t('With the alignment choices as separate buttons.'));

$editor = $form->getFormInstance()->makeCKEditor($el, $country);

$editor->addPlugin('Alignment');
$editor->insertButtonAfter(UI_MarkupEditor_CKEditor::BUTTON_ALIGN_RIGHT, UI_MarkupEditor_CKEditor::BUTTON_SUPERSCRIPT);
$editor->insertButtonAfter(UI_MarkupEditor_CKEditor::BUTTON_ALIGN_CENTER, UI_MarkupEditor_CKEditor::BUTTON_SUPERSCRIPT);
$editor->insertButtonAfter(UI_MarkupEditor_CKEditor::BUTTON_ALIGN_LEFT, UI_MarkupEditor_CKEditor::BUTTON_SUPERSCRIPT);

$form->display();

DBHelper::rollbackTransaction();
