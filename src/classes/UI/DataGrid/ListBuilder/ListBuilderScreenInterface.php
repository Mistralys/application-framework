<?php

declare(strict_types=1);

namespace UI\DataGrid\ListBuilder;

use Application\Interfaces\Admin\AdminScreenInterface;
use UI\Interfaces\ListBuilderInterface;

interface ListBuilderScreenInterface extends AdminScreenInterface
{
    public function createListBuilder() : ListBuilderInterface;
    public function getListID() : string;
}
