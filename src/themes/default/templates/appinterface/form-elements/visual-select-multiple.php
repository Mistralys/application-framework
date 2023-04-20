<?php
/* @var $this UI_Page_Template */

// URL to the images, in this case loaded from the framework theme folder.
$imgURL = $this->getTheme()->getDefaultImagesURL().'/appinterface/form-elements/visual-select';

$form = $this->ui->createForm('visual-select-example');

$select = $form->addVisualSelect('test-visual-select-1');

for ($i = 1; $i <= 8; $i++) {
    $select->addImage(
        sprintf('Image %02d', $i),
        sprintf('groupa-image%02d', $i),
        sprintf('%s/set01a-image%02d.png', $imgURL, $i)
    );
}

$select = $form->addVisualSelect('test-visual-select-2');

$amountSets = 2;
$groups = array('a', 'b');

for($setNumber=1; $setNumber <= $amountSets; $setNumber++)
{
    $set = $select->addImageSet(sprintf('set%02d', $setNumber), t('Set %02d', $setNumber));

    foreach($groups as $groupLetter)
    {
        $group = $set->addGroup(t('Group %1$s', strtoupper($groupLetter)));

        for ($i = 1; $i <= 8; $i++) {
            $group->addImage(
                sprintf('Image %02d', $i),
                sprintf('set%02d-group%s-image%02d', $setNumber, $groupLetter, $i),
                sprintf('%s/set%02d%s-image%02d.png', $imgURL, $setNumber, $groupLetter, $i)
            );
        }
    }
}

$form->display();
?>
