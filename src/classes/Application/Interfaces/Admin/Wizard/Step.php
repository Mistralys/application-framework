<?php

interface Application_Interfaces_Admin_Wizard_Step extends Application_Admin_ScreenInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function getDataKey(string $name);
}
