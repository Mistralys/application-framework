<?php
/* @var $this UI_Page_Template */

$section = $this->createSection();

// add content by capturing output
$section->startCapture();
    ?>
        <ul>
            <li>Some regular</li>
            <li>HTML content</li>
        </ul>
    <?php
$section->endCapture(); 

// add content by appending it to existing content
$section->appendContent('<p>Appended paragraph of text.</p>');

$section->display();

?>