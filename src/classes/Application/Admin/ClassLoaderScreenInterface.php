<?php

declare(strict_types=1);

namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;

interface ClassLoaderScreenInterface extends AdminScreenInterface
{
    /**
     * @return class-string<AdminScreenInterface>|null
     */
    public function getDefaultSubscreenClass() : ?string;

    /**
     * @return class-string<AdminScreenInterface>|null
     */
    public function getParentScreenClass() : ?string;
}
