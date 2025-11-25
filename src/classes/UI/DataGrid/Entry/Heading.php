<?php

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering;

class UI_DataGrid_Entry_Heading extends UI_DataGrid_Entry
{
    protected string $title;
    private ?string $subline = null;

    public function __construct(UI_DataGrid $grid, string|StringableInterface $title)
    {
        parent::__construct($grid, array());
        $this->title = toString($title);
        $this->makeNonSortable();
    }

    public function isCountable() : bool
    {
        return false;
    }

    public function render() : string
    {
        $this->addClass('row-heading');
        
        $trAttribs = array(
            'class' => implode(' ', $this->getClasses()),
        );
        
        $tdAttribs = array(
            'colspan' => $this->grid->countColumns()
        );
        
        OutputBuffering::start();
        ?>
        <tr <?php echo compileAttributes($trAttribs) ?>>
            <td <?php echo compileAttributes($tdAttribs) ?>>
                <div class="heading">
                    <?php echo $this->title ?>
                    <?php if($this->subline !== NULL) : ?>
                        <div class="subline"><?php echo $this->subline ?></div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
        
        return OutputBuffering::get();
    }

    public function setSubline(string|StringableInterface|NULL $subline) : self
    {
        if($subline !== NULL) {
            $subline = toString($subline);
        }

        $this->subline = $subline;

        return $this;
    }
}

