<?php
/**
 * File containing the template class {@see template_default_frame}.
 * 
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame
 */

declare(strict_types=1);
use UI\Page\Navigation\QuickNavigation;

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
    public const BODY_CLASS_WITH_QUICKNAV = 'with-quicknav';
    public const BODY_CLASS_LOCKING_LOCKABLE = 'locking-lockable';
    public const BODY_CLASS_LOCKING_LOCKED = 'locking-locked';
    public const BODY_CLASS_LOCKING_UNLOCKED = 'locking-unlocked';

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
    <div id="footer_frame" class="container">{FOOTER}</div>
</footer>

</body>
</html>
<?php

    }
    
   /**
    * @var array<string,string>
    */
    private array $variables;
    
    private Application_Ratings $ratings;
    private Application_Admin_ScreenInterface $screen;
    private ?Application_LockManager $lockManager;
    
    protected function preRender() : void
    {
        $this->screen = $this->page->getActiveScreen();
        $this->lockManager = $this->page->getLockManager();
        
        Application_LockManager::injectJS($this->ui, $this->lockManager);
        
        $this->ratings = Application::createRatings();
        $this->ratings->injectJS($this->ui);
        
        $this->resolveVariables();
    }
    
    private function resolveVariables() : void
    {
        $contentHTML = $this->getVar('html.content');
        
        if(strpos($contentHTML, '{SIDEBAR}') !== false)
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

        if($this->hasQuickNav()) {
            $bodyClasses[] = self::BODY_CLASS_WITH_QUICKNAV;
        }

        if($this->screen instanceof Application_Interfaces_Admin_LockableScreen && $this->screen->isLockable()) {
            $bodyClasses[] = self::BODY_CLASS_LOCKING_LOCKABLE;
        }
        
        if(isset($this->lockManager) && $this->lockManager->isLocked()) 
        {
            $bodyClasses[] = self::BODY_CLASS_LOCKING_LOCKED;
        } 
        else if(isset($this->lockManager)) 
        {
            $bodyClasses[] = self::BODY_CLASS_LOCKING_UNLOCKED;
        }
        
        return $bodyClasses;
    }

    public function hasQuickNav() : bool
    {
        $page = $this->getPage();
        $navID = QuickNavigation::NAV_AREA_QUICK_NAVIGATION;

        return
            $page->hasNavigation($navID)
            &&
            $page->getNavigation($navID)->hasValidItems();
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
