<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

interface AdminModeInterface extends AdminScreenInterface
{
    public function getDefaultSubmode() : string;
    public function hasSubmodes() : bool;
    public function getSubmode() : ?AdminSubmodeInterface;
}
