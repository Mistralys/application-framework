<?php

    use AppUtils\FileHelper;

    /* @var $this UI_Page_Template */

    $examples = $this->getVar('examples');
    $activeID = $this->getVar('active');
    
    $this->ui->addStylesheet('ui-appinterface.css');
    
    if(empty($activeID)) {
       $this->createSection()
       ->setTitle(t('User interface examples'))
       ->setContent('<p>'.t('The following is a collection of examples of user interface elements, intended as both a visual reference as well as for the related source code.').'</p>')
       ->display();
       return; 
    }
    
    $folder = $this->theme->getDefaultTemplatesPath().'/appinterface';
    
    $tokens = explode('.', $activeID);
    $categoryID = $tokens[0];
    $exampleID = $tokens[1];
    
    $title = $examples[$categoryID]['label'] . ' - ' . $examples[$categoryID]['examples'][$exampleID];

    $path = $folder.'/'.$categoryID.'/'.$exampleID.'.php';
    
    $mainSection = $this->createSection();
    $mainSection->setTitle($title);
    
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
    ->setContent(AppUtils\Highlighter::php(FileHelper::readContents($path)));
    
    $mainSection->display();
