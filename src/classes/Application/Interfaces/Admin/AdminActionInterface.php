<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

interface AdminActionInterface extends AdminScreenInterface
{
    public function getSubmode() : AdminSubmodeInterface;
    public function getMode() : AdminModeInterface;

}
