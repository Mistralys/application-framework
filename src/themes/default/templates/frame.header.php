<?php
/**
 * @package UserInterface
 * @subpackage Templates
 * @see  template_default_frame_header
 */

declare(strict_types=1);

use UI\Page\Navigation\MetaNavigation;
use UI\Page\Navigation\QuickNavigation;

/**
 * Header including the main navigation.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_frame_header extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        ?>
<nav class="navbar navbar-default navbar-fixed-top" id="app-mainnav">
	<div class="container" id="nav-container">
        <?php echo $this->renderTemplate('frame.header.appswitcher'); ?>

        <?php echo $this->header->renderNavigation('main'); ?>

        <?php
            echo $this->metaNav->render();
        ?>
    </div>
    <?php
        echo $this->header->renderNavigation(QuickNavigation::NAV_AREA_QUICK_NAVIGATION);

        $lockManager = $this->page->getLockManager();
        
        if($lockManager && Application_LockManager::isEnabled())
        {
            ?>
                <div class="navbar-toolbars">
                	<?php echo $this->renderTemplate('frame.header.lockmanager') ?>
                </div>
            <?php 
        }
        
    ?>
</nav>
<?php
    }

    protected MetaNavigation $metaNav;

    protected function preRender() : void
    {
        $this->configureMetaNav();
        $this->_handleMetaNav();
    }

    protected function _handleMetaNav() : void
    {

    }

    private function configureMetaNav() : void
    {
        $this->metaNav = new MetaNavigation($this->getUI());
        $this->metaNav->configure();
    }
}
