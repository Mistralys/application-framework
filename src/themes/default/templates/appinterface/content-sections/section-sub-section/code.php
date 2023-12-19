<?php

declare(strict_types=1);

$section = UI::getInstance()->createSection()
    ->setTitle('Main section');

$section->addSubsection()
    ->setTitle('First subsection')
    ->setContent('<p>Some content here.</p>')
    ->collapse();

$section->addSubsection()
    ->setTitle('Second subsection')
    ->setContent('<p>Some content here.</p>')
    ->collapse();

echo $section;