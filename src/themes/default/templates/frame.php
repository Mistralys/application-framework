<?php
/**
 * File containing the template class {@see template_default_frame}.
 * 
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame
 */

declare(strict_types=1);

/**
 * Main template for the frame skeleton of all pages.
 * 
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_frame_sidebar
 * @see template_default_frame_header_user_menu
 */
class template_default_frame extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $this->page->resolveTitle() ?></title>
    <link rel="shortcut icon" href="favicon.ico"/>
    <?php echo '{HEADER_INCLUDES}'; ?>    
</head>
<body class="<?php echo implode(' ', $this->getBodyClasses()) ?>">

{MAINTENANCE}

<header id="header">{HEADER}</header>

{RATINGS}

<div id="content_area">
    <div class="container">
        <div id="content_frame">
        	{HELP}
        	{MESSAGES}
            {CONSOLE}
            {CONTENT}
        </div>
    </div>
</div>

<footer id="footer">
    <div id="footer_frame">{FOOTER}</div>
</footer>

</body>
</html>
<?php

    }
    
   /**
    * @var array<string,string>
    */
    private $variables;
    
   /**
    * @var Application_Ratings
    */
    private $ratings;
    
   /**
    * @var Application_Admin_ScreenInterface
    */
    private $screen;
    
   /**
    * @var Application_LockManager|NULL
    */
    private $lockManager;
    
    protected function preRender() : void
    {
        $this->screen = $this->page->getActiveScreen();
        $this->lockManager = $this->page->getLockManager();
        
        Application_LockManager::injectJS($this->ui, $this->lockManager);
        
        $this->ratings = Application::createRatings();
        $this->ratings->injectJS($this->ui);
        
        $this->resolveVariables();
    }
    
    private function resolveVariables()
    {
        $contentHTML = $this->getVar('html.content');
        
        if(strstr($contentHTML, '{SIDEBAR}'))
        {
            $contentHTML = str_replace('{SIDEBAR}', $this->sidebar->render(), $contentHTML);
        }
        
        $this->variables = array(
            '{FOOTER}' => $this->footer->render(),
            '{RATINGS}' => $this->ratings->renderWidget(),
            '{MAINTENANCE}' => $this->page->renderMaintenance(),
            '{HELP}' => $this->screen->renderHelp(),
            '{CONSOLE}' => $this->page->renderConsole(),
            '{CONTENT}' => $contentHTML,
            
            // must always be the last items
            '{HEADER}' => $this->header->render(),
            '{MESSAGES}' => $this->page->renderMessages(),
            '{HEADER_INCLUDES}' => $this->ui->renderHeadIncludes(),
        );
    }
    
    private function getBodyClasses() : array
    {
        $bodyClasses = array();
        $bodyClasses[] = 'layout-'.$this->user->getSetting('layout_width', 'standard');
        $bodyClasses[] = 'fontsize-'.$this->user->getSetting('layout_fontsize', 'standard');
        
        if($this->screen instanceof Application_Interfaces_Admin_LockableScreen && $this->screen->isLockable()) {
            $bodyClasses[] = 'locking-lockable';
        }
        
        if(isset($this->lockManager) && $this->lockManager->isLocked()) 
        {
            $bodyClasses[] = 'locking-locked';
        } 
        else if(isset($this->lockManager)) 
        {
            $bodyClasses[] = 'locking-unlocked';
        }
        
        return $bodyClasses;
    }
    
    protected function filterOutput(string $output) : string
    {
        return str_replace(
            array_keys($this->variables), 
            array_values($this->variables), 
            $output
        );
    }
}
