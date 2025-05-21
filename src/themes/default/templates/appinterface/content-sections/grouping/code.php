<?php

declare(strict_types=1);

$ui = UI::getInstance();

for($g=1; $g <= 2; $g++)
{
    // Create a group name
    $group = 'Group ' . $g;

    // Display the collapse controls for the group
    echo UI_Page_Section::createGroupControls($ui, $group)
        ->makeMini()
        ->setDisplayThreshold(3)
        ->setStyle('margin-bottom', '0.5rem')
        ->setTooltips(
            'Expand all sections in group '.$g,
            'Collapse all sections in group '.$g
        );

    // Add some sections to the group
    for($s=1; $s <= 3; $s++)
    {
        echo $ui->createSection()
            ->setGroup($group)
            ->setTitle('Section title')
            ->setTagline($group)
            ->setContent('<p>Arbitrary HTML content here</p>')
            ->collapse();
    }

    echo '<hr>';
}
