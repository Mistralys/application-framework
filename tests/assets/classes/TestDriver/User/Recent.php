<?php

declare(strict_types=1);

class TestDriver_User_Recent extends Application_User_Recent
{
    protected function registerCategories() : void
    {
        $this->registerCategory('foo', 'Foo');
        $this->registerCategory('bar', 'Bar');
    }
}
