<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

class UI_Bootstrap_BigSelection_Item_Header extends UI_Bootstrap_BigSelection_Item
{
    /**
     * @var string
     */
    private $title = '';

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @throws UI_Exception
     */
    public function setTitle($title) : UI_Bootstrap_BigSelection_Item_Header
    {
        $this->title = toString($title);
        return $this;
    }

    protected function resolveSearchWords() : string
    {
        return '';
    }

    protected function _render() : string
    {
        OutputBuffering::start();

        $this->addClass('bigselection-header');

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>">
            <?php echo $this->renderTitle() ?>
        </li>
        <?php

        return OutputBuffering::get();
    }

    private function renderTitle() : string
    {
        $icon = $this->getIcon();

        return (string)sb()
            ->ifNotEmpty($icon, $icon)
            ->add($this->title);
    }
}
