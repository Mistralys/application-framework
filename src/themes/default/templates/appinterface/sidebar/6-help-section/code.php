<?php

declare(strict_types=1);

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addHelp(
    'Look here for help',
    sb()
        ->para('This is a help section.')
        ->para(sb()
            ->add('It is typically used to provide help for the current page.')
            ->add('It can also link to more extensive documentation, or show a list of tips and tricks.')
        )
);

echo $sidebar;
