<?php

declare(strict_types=1);

use Mistralys\Examples\HerbsCollection;

$section = UI::getInstance()->createSection()
    ->setTitle('Section title')
    ->setContent('<p>Some content here</p>');

$selector = $section->addQuickSelector();

// Add some example entries to the selector
$herbs = HerbsCollection::getInstance()->getAll();
foreach($herbs as $herb) {
    $selector->addItem((string)$herb->getID(), $herb->getName(), '');
}

echo $section;
