<?php

declare(strict_types=1);

// URL to the images, in this case loaded from the framework theme folder.
$imgURL = UI::getInstance()->getTheme()->getDefaultImagesURL().'/appinterface/form-elements/visual-select';

$form = UI::getInstance()->createForm('visual-select-example');

$select = $form->addVisualSelect('test-visual-select');
$select->setLabel('Image');

for ($i = 1; $i <= 8; $i++) {
    $select->addImage(
        sprintf('Image %02d', $i),
        sprintf('groupa-image%02d', $i),
        sprintf('%s/set01a-image%02d.png', $imgURL, $i)
    );
}

echo $form;
