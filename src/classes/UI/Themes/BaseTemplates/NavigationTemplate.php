<?php

declare(strict_types=1);

namespace UI\Themes\BaseTemplates;

use UI_Page_Navigation;
use UI_Page_Navigation_Item;
use UI_Page_Navigation_Item_DropdownMenu;
use UI_Page_Template_Custom;

abstract class NavigationTemplate extends UI_Page_Template_Custom
{
    protected UI_Page_Navigation $nav;

    abstract protected function initClasses() : void;
    abstract public function getElementID() : string;

    protected function preRender() : void
    {
        $this->nav = $this->getObjectVar('navigation', UI_Page_Navigation::class);

        $this->initClasses();
    }

    protected function generateOutput() : void
    {
        $items = $this->nav->getItems();
?>
<!-- start <?php echo $this->nav->getID() ?> navigation -->
<ul class="<?php echo implode(' ', $this->nav->getClasses()) ?>" id="<?php $this->getElementID() ?>">
    <?php
    foreach ($items as $item)
    {
        if(!$item->isValid())
        {
            continue;
        }

        $this->generateItem($item);
    }
    ?>
</ul>
<!-- end <?php echo $this->nav->getID() ?> navigation -->
<?php
    }

    protected function generateItem(UI_Page_Navigation_Item $item) : void
    {
        if($item instanceof UI_Page_Navigation_Item_DropdownMenu)
        {
            echo $item->render();
            return;
        }

        $classes = $item->getContainerClasses();

        if ($item->isActive())
        {
            $classes[] = 'active';
        }
        else
        {
            $classes[] = 'regular';
        }

        ?>
        <li class="<?php echo implode(' ', $classes) ?>">
            <?php echo $item->render() ?>
        </li>
        <?php
    }
}
