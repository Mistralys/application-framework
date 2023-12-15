<?php

use Application\MarkdownRenderer;
use AppUtils\FileHelper;
use AppUtils\Highlighter;

/* @var $this UI_Page_Template */

    $examples = $this->getVar('examples');
    $activeExampleID = $this->getVar('active');
    
    $this->ui->addStylesheet('ui-appinterface.css');
    
    if(empty($activeExampleID)) {
       $this->createSection()
       ->setTitle(t('User interface examples'))
       ->setContent('<p>'.t('The following is a collection of examples of user interface elements, intended as both a visual reference as well as for the related source code.').'</p>')
       ->display();
       return; 
    }
    
    $folder = $this->theme->getDefaultTemplatesPath().'/appinterface';
    
    $tokens = explode('.', $activeExampleID);
    $categoryID = $tokens[0];
    $exampleID = $tokens[1];
    
    $title = $examples[$categoryID]['label'] . ' - ' . $examples[$categoryID]['examples'][$exampleID];

    $path = $folder.'/'.$categoryID.'/'.$exampleID.'.php';
    $infoPath = $folder.'/'.$categoryID.'/'.$exampleID.'.md';
    
    $mainSection = $this->createSection();
    $mainSection->setTitle($title);

    if(file_exists($infoPath)) {
        $mainSection->addSubsection()
            ->setTitle(t('Description'))
            ->setContent(MarkdownRenderer::create()->render(FileHelper::readContents($infoPath)));
    }

    $sub1 = $mainSection->addSubsection();
    $sub1->setCollapsed(false);
    $sub1->setTitle(t('Output'));
    $sub1->startCapture();
        echo '<div class="example-output">';
        include $path;
        echo '</div>';
    $sub1->endCapture();

    $mainSection->addSubsection()
    ->setTitle(t('Source code'))
    ->setCollapsed()
    ->setContent(Highlighter::php(FileHelper::readContents($path)));
    
    $mainSection->display();
