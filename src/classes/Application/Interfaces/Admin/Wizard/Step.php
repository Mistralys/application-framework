<?php

use Application\Interfaces\Admin\AdminScreenInterface;

interface Application_Interfaces_Admin_Wizard_Step extends AdminScreenInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function getDataKey(string $name);
}
