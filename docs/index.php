<?php

    use Mistralys\MarkdownViewer\DocsManager;
    use Mistralys\MarkdownViewer\DocsViewer;

    if(!file_exists('../vendor/autoload.php')) {
        die('Please run composer install first.');
    }

    require_once '../vendor/autoload.php';

    $manager = (new DocsManager())
        ->addFile('Main docs', 'Documentation.md');

    (new DocsViewer($manager, '../vendor'))
        ->setTitle('Application Framework')
        ->display();

