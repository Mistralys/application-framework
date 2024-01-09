<?php

declare(strict_types=1);

$form = UI::getInstance()->createForm('example-elements');

$el = $form->addImageUploader('image');
$el->setLabel('Image');

echo $form;
