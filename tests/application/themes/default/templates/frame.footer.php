<?php

declare(strict_types=1);

class driver_template_default_frame_footer extends template_default_frame_footer
{
    protected function registerAboutItems(): void
    {
        $this->addItemHTML(t('This is an application used to showcase and test framework features.'));
    }

    protected function registerAppItems(): void
    {
        $this->addItemURL(
            t('Documentation'),
            $this->request->buildURL(array(), Application_Bootstrap_Screen_Documentation::DISPATCHER_NAME)
        );
    }
}
