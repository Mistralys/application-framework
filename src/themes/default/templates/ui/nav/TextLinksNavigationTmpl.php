<?php
/**
 * @package UI
 * @subpackage Navigation
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\UI\Nav;

use UI_Page_Navigation;
use UI_Page_Navigation_Item;
use UI_Page_Navigation_Item_DropdownMenu;
use UI_Page_Template_Custom;

/**
 * Renders a text links navigation bar.
 *
 * @package UI
 * @subpackage Navigation
 */
class TextLinksNavigationTmpl extends UI_Page_Template_Custom
{
    private UI_Page_Navigation $nav;

    protected function preRender(): void
    {
        $this->ui->addStylesheet('ui/navs/meta-text-links.css');

        $this->nav = $this->getObjectVar('navigation', UI_Page_Navigation::class);
        $this->nav->addClass('meta-text-links');
    }

    protected function generateOutput() : void
    {
        $items = $this->nav->getItems();
        ?>
        <ul class="<?php echo implode(' ', $this->nav->getClasses()) ?>">
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
