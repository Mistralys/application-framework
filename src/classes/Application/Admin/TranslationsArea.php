<?php

declare(strict_types=1);

use AppLocalize\Localization;
use function AppLocalize\tex;

abstract class Application_Admin_TranslationsArea extends Application_Admin_Area
{
    public function getURLName()
    {
        return 'translations';
    }

    public function getDefaultMode()
    {
        return null;
    }

    public function getTitle()
    {
        return t('UI Translation tools');
    }

    public function getNavigationTitle()
    {
        return t('Translation');
    }

    public function getNavigationIcon() : ?UI_Icon
    {
        return UI::icon()->translation();
    }

    public function isCore()
    {
        return true;
    }

    public function getDependencies()
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
