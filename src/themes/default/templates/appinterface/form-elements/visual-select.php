<?php
/* @var $this UI_Page_Template */

// URL to the images, in this case loaded from the framework theme folder.
$imgURL = $this->getTheme()->getDefaultImagesURL().'/appinterface/form-elements/visual-select';

$form = $this->ui->createForm('visual-select-example');

$select = $form->addVisualSelect('test-visual-select');

for ($i = 1; $i <= 8; $i++) {
    $select->addImage(
        sprintf('Image %02d', $i),
        sprintf('groupa-image%02d', $i),
        sprintf('%s/set01a-image%02d.png', $imgURL, $i)
    );
}

$form->display();
?>
