<?php

	/* @var $this UI_Page_Template */

?>
<nav class="navbar navbar-default navbar-fixed-top" id="app-mainnav">
	<div class="container" id="nav-container">
        <?php echo $this->renderTemplate('frame.header.appswitcher'); ?>

        <?php $this->header->getNavigation('main')->display(); ?>

        <ul class="nav navbar-nav navbar-meta pull-right" id="app-metanav">
            <?php
                echo $this->renderTemplate('frame.header.developer-menu');
            
                ?>
                <li>
                    <a href="#" onclick="Driver.DialogNotepad();">
                        <?php UI::icon()->notepad()
                            ->setTooltip(t('Your personal notepad for taking notes.'))
                            ->makeTooltipBottom()
                            ->makeInformation()
                            ->setAttribute('data-placement', 'left')
                        ?>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="Driver.DialogLookup();">
                        <?php UI::icon()->search()
                        ->setTooltip(t('Look up an item'))
                        ->makeTooltipBottom()
                        ->setAttribute('data-placement', 'left')
                            ?>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="window.print();">
                        <?php UI::icon()->printer()
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
        
        $lockManager = $this->page->getLockManager();
        
        if($lockManager && $lockManager->isEnabled()) 
        {
            ?>
                <div class="navbar-toolbars">
                	<?php echo $this->renderTemplate('frame.header.lockmanager') ?>
                </div>
            <?php 
        }
        
    ?>
</nav>