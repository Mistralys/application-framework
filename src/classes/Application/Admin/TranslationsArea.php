<?php

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
        $editor = \AppLocalize\Localization::createEditor();
        $editor->addRequestParam('page', $this->getURLName());
        $editor->display();
        exit;
    }
}
