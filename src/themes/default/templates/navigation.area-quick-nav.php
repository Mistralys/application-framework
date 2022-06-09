<?php

declare(strict_types=1);

class template_default_navigation_area_quick_nav extends UI_Page_Template_Custom
{
    private UI_Page_Navigation $nav;

    protected function generateOutput() : void
    {
        $items = $this->nav->getItems();

        $this->nav->addClass('area-quick-nav');

        ?>
        <div id="app-quicknav">
            <div class="container">
                <ul class="<?php echo implode(' ', $this->nav->getClasses()) ?>">
                    <?php
                    foreach($items as $item)
                    {
                        if(!$item->isValid()) {
                            continue;
                        }

                        $this->generateItem($item);
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }

    protected function preRender() : void
    {
        $this->nav = $this->getObjectVar('navigation', UI_Page_Navigation::class);
    }

    private function generateItem(UI_Page_Navigation_Item $item) : void
    {
        $classes = array();

        if($item->isActive()) {
            $classes[] = 'item-active';
        }

        ?>
        <li class="<?php echo implode(' ', $classes) ?>">
            <?php echo $item->render(); ?>
        </li>
        <?php
    }
}
