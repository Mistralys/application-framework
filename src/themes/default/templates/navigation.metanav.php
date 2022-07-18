<?php

declare(strict_types=1);

use UI\Themes\BaseTemplates\NavigationTemplate;

class template_default_navigation_metanav extends NavigationTemplate
{
    protected function initClasses() : void
    {
        $this->nav
            ->addClass('nav')
            ->addClass('navbar-nav')
            ->addClass('navbar-meta')
            ->addClass('pull-right');
    }

    public function getElementID() : string
    {
        return 'app-metanav';
    }
}
