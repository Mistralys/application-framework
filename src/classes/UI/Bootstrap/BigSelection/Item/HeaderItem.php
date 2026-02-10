<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection\Item;

use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering;
use UI\Bootstrap\BigSelection\BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS;
use UI_Exception;

class HeaderItem extends BaseItem
{
    private string $title = '';

    /**
     * @param string|int|float|StringableInterface $title
     * @throws UI_Exception
     */
    public function setTitle(string|int|float|StringableInterface $title): self
    {
        $this->title = toString($title);
        return $this;
    }

    protected function resolveSearchWords(): string
    {
        return '';
    }

    protected function _render(): string
    {
        OutputBuffering::start();

        $this->addClass(BigSelectionCSS::ITEM_HEADER);

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>">
            <?php echo $this->renderTitle() ?>
        </li>
        <?php

        return OutputBuffering::get();
    }

    private function renderTitle(): string
    {
        $icon = $this->getIcon();

        return (string)sb()
                ->ifNotEmpty($icon, $icon)
                ->add($this->title);
    }
}
