<?php

class TestDriver_User extends Application_User_Extended
{
    public function getRightGroups(): array
    {
        return array();
    }

    protected function registerRoles(): void
    {

    }
}
