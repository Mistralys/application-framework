<?php

declare(strict_types=1);

use AppLocalize\Localization;
use function AppLocalize\tex;

abstract class Application_Admin_TranslationsArea extends Application_Admin_Area
{
    public function getURLName() : string
    {
        return 'translations';
    }

    public function getDefaultMode() : string
    {
        return '';
    }

    public function getTitle() : string
    {
        return t('UI Translation tools');
    }

    public function getNavigationTitle() : string
    {
        return t('Translation');
    }

    public function getNavigationIcon() : ?UI_Icon
    {
        return UI::icon()->translation();
    }

    public function isCore() : bool
    {
        return true;
    }

    public function getDependencies() : array
    {
        return array();
    }
    
    public function renderContent() : string
    {
        $editor = Localization::createEditor();
        $editor->addRequestParam('page', $this->getURLName());
        $editor->setAppName(tex('%1$s translations', 'Placeholder contains application name.', $this->driver->getAppNameShort()));
        $editor->setBackURL(APP_URL, t('Back to %1$s', $this->driver->getAppNameShort()));
        $editor->display();

        Application::exit('Translation editor finished');
    }
}
