<?php

declare(strict_types=1);

namespace UI\Themes\BaseTemplates;

use DateTime;
use JSHelper;
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

    $this->generateSeasonal();
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

    protected function generateSeasonal() : void
    {
        $date = new DateTime();
        $year = date('Y');
        $start = new DateTime($year.'-12-15');
        $end = new DateTime(($year+1).'-01-15');

        if(!($date >= $start && $date <= $end))
        {
            return;
        }

        $this->ui->addStylesheet('seasonals/winter.css');

        $id = nextJSID();

        JSHelper::tooltipify($id, JSHelper::TOOLTIP_LEFT);

        ?>
        <script>
            const winterMessage =
                '<p><?php pt('The %1$s team wishes you a merry christmas and a happy new year.', $this->driver->getAppNameShort()) ?></p>' +
                '<img id="<?php echo $id ?>" style="width: 160px;clip-path: circle(79px at center)" src="<?php echo $this->theme->getImageURL('seasonals/winter-wonderland.gif') ?>" alt="">';
        </script>
        <li class="regular">
            <a href="#" onclick="application.createDialogMessage(winterMessage, '<?php echo addslashes(t('Happy winter season!')) ?>').AddClass('dialog-winter').SetIcon(UI.Icon().Snowflake()).Show();return false;" style="padding-top:3px;padding-bottom:3px;">
                <img
                    id="<?php echo $id ?>"
                    class="clickable"
                    title="<?php pt('Happy winter season!') ?>"
                    style="width: 34px;clip-path: circle(17px at center)"
                    src="<?php echo $this->theme->getImageURL('seasonals/winter-wonderland.gif') ?>"
                    alt=""
                ></a>
        </li>
        <?php
    }
}
