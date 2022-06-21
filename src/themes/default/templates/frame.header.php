<?php

	/* @var $this UI_Page_Template */

?>
<nav class="navbar navbar-default navbar-fixed-top" id="app-mainnav">
	<div class="container" id="nav-container">
        <?php echo $this->renderTemplate('frame.header.appswitcher'); ?>

        <?php echo $this->header->renderNavigation('main'); ?>

        <ul class="nav navbar-nav navbar-meta pull-right" id="app-metanav">
            <?php
                echo $this->renderTemplate('frame.header.developer-menu');
            
                ?>
                <li>
                    <a href="#" onclick="<?php echo Application_User_Notepad::getJSOpen() ?>">
                        <?php echo UI::icon()->notepad()
                            ->setTooltip(Application_User_Notepad::getTooltipText())
                            ->makeTooltipBottom()
                            ->setAttribute('data-placement', 'left')
                        ?>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="Driver.DialogLookup();">
                        <?php echo UI::icon()->search()
                        ->setTooltip(t('Look up an item'))
                        ->makeTooltipBottom()
                        ->setAttribute('data-placement', 'left')
                            ?>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="window.print();">
                        <?php echo UI::icon()->printer()
                        ->setTooltip(t('Print this page'))
                        ->makeTooltipBottom()
                        ->setAttribute('data-placement', 'left')
                        ?>
                    </a>
                </li>
                <?php
                echo $this->renderTemplate('frame.header.user-menu');
            ?>
        </ul>
    </div>
    <?php
        echo $this->header->renderNavigation(Application_Admin_Area::NAV_AREA_QUICK_NAVIGATION);

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