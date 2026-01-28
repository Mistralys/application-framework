<?php
/**
 * @package UserInterface
 * @subpackage Templates
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\EventHandler\EventManager;use Application\Interfaces\Admin\AdminScreenInterface;
use Application\User\LayoutWidths;
use Application\Users\Admin\Screens\UserSettingsArea;
use AppUtils\ClassHelper;
use AppUtils\OutputBuffering;
use UI\Event\PageRendered;
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
    public const string BODY_CLASS_WITH_QUICKNAV = 'with-quicknav';
    public const string BODY_CLASS_LOCKING_LOCKABLE = 'locking-lockable';
    public const string BODY_CLASS_LOCKING_LOCKED = 'locking-locked';
    public const string BODY_CLASS_LOCKING_UNLOCKED = 'locking-unlocked';

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
    <div class="container">
        <div id="footer_frame">{FOOTER}</div>
    </div>
</footer>

{QUERY_SUMMARY}

</body>
</html>
<?php

    }
    
   /**
    * @var array<string,string>
    */
    private array $variables;
    
    private Application_Ratings $ratings;
    private ?AdminScreenInterface $screen = null;
    private ?Application_LockManager $lockManager;
    
    protected function preRender() : void
    {
        $this->screen = $this->page->getActiveScreen();
        $this->lockManager = $this->page->getLockManager();
        
        Application_LockManager::injectJS($this->ui, $this->lockManager);
        
        $this->ratings = AppFactory::createRatings();
        $this->ratings->injectJS($this->ui);
        
        $this->resolveVariables();
    }
    
    private function resolveVariables() : void
    {
        $contentHTML = $this->getVar('html.content');
        
        if(str_contains($contentHTML, '{SIDEBAR}'))
        {
            $contentHTML = str_replace('{SIDEBAR}', $this->sidebar->render(), $contentHTML);
        }
        
        $this->variables = array(
            '{FOOTER}' => $this->footer->render(),
            '{RATINGS}' => $this->ratings->renderWidget(),
            '{MAINTENANCE}' => $this->page->renderMaintenance(),
            '{HELP}' => '',
            '{CONSOLE}' => $this->page->renderConsole(),
            '{CONTENT}' => $contentHTML,
            '{QUERY_SUMMARY}' => $this->renderQuerySummary(),
        );

        if(isset($this->screen)) {
            $this->variables['{HELP}'] = $this->screen->renderHelp();
        }

        // must always be the last items for all the other elements to
        // have the possibility to queue scripts, styles and messages.
        $this->variables['{HEADER}'] = $this->header->render();
        $this->variables['{MESSAGES}'] = $this->page->renderMessages();
        $this->variables['{HEADER_INCLUDES}'] = $this->ui->renderHeadIncludes();
    }

    private function renderQuerySummary(): string
    {
        if(!DBHelper::isQueryTrackingEnabled()) {
            return '';
        }

        $queries = DBHelper::getQueries();

        $duplicates = array();
        $same = array();

        foreach($queries as $query) {
            $sql = (string)$query->getStatement();
            $sqlHash = md5($sql);
            $fullHash = md5($sql.serialize($query->getVariables()));

            if(isset($same[$fullHash])) {
                $same[$fullHash]['count']++;
                $same[$fullHash]['origins'][] = $query->trace2string();
            } else {
                $same[$fullHash] = array(
                    'count' => 1,
                    'sql' => $query->getSQLFormatted(),
                    'origins' => array($query->trace2string()),
                );
            }

            if(isset($duplicates[$sqlHash])) {
                $duplicates[$sqlHash]['count']++;
                $duplicates[$sqlHash]['origins'][] = $query->trace2string();
            } else {
                $duplicates[$sqlHash] = array(
                    'count' => 1,
                    'sql' => (string)$query->getStatement(),
                    'origins' => array($query->trace2string())
                );
            }
        }

        foreach($same as $hash => $data) {
            if($data['count'] === 1) {
                unset($same[$hash]);
            }
        }

        foreach($duplicates as $sqlHash => $data) {
            if($data['count'] === 1) {
                unset($duplicates[$sqlHash]);
            }
        }

        usort($same, static function($a, $b) : int {
            return $b['count'] - $a['count'];
        });

        usort($duplicates, static function($a, $b) : int {
            return $b['count'] - $a['count'];
        });

        OutputBuffering::start();
        ?>
<pre>
Total queries: <?php echo count($queries) ?>


EXACT DUPLICATES (<?php echo count($same) ?>):

<?php echo print_r($same, true) ?>

SAME SQL STATEMENT: (<?php echo count($duplicates) ?>)

<?php echo print_r($duplicates, true) ?>

</pre>
        <?php

        return OutputBuffering::get();
    }
    
    private function getBodyClasses() : array
    {
        $bodyClasses = array();
        $bodyClasses[] = 'layout-'.$this->user->getSetting(UserSettingsArea::SETTING_LAYOUT_WIDTH, LayoutWidths::DEFAULT_WIDTH);
        $bodyClasses[] = 'fontsize-'.$this->user->getSetting('layout_fontsize', 'standard');

        if($this->user->isDeveloper()) {
            $bodyClasses[] = 'dev-user';
        }

        if(isDevelMode()) {
            $bodyClasses[] = 'devel-mode';
        }

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
        $html = str_replace(
            array_keys($this->variables), 
            array_values($this->variables), 
            $output
        );

        if(!EventManager::hasListener(UI::EVENT_PAGE_RENDERED)) {
            return $html;
        }

        $event = EventManager::trigger(
            UI::EVENT_PAGE_RENDERED,
            array(
                $this->page,
                $html
            ),
            PageRendered::class
        );

        return ClassHelper::requireObjectInstanceOf(
            PageRendered::class,
            $event
        )
            ->getHTML();
    }
}
