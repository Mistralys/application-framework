<?php

declare(strict_types=1);

$section = UI::getInstance()->createSection()
    ->setTitle('Section title');

$section->startCapture();
    ?>
        <ul>
            <li>Some regular</li>
            <li>HTML content</li>
        </ul>
    <?php
$section->endCapture(); // Replaces existing content

$section->startCapture();
    ?>
        <p>This text is appended to the existing content.</p>
    <?php
$section->endCaptureAppend(); // Appends to existing content

$section->display();
