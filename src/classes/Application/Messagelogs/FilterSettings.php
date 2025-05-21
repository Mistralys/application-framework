<?php

declare(strict_types=1);

use Application\AppFactory;

/**
 * @property Application_Messagelogs $collection
 * @property Application_Messagelogs_FilterCriteria $filters
 */
class Application_Messagelogs_FilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_CATEGORY = 'category';
    public const SETTING_TYPE = 'type';
    public const SETTING_DATE = 'date';
    public const SETTING_SEARCH = 'search';
    public const SETTING_USER = 'user';

    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::registerSettings()
     */
    protected function registerSettings() : void
    {
        $this->registerSetting(self::SETTING_CATEGORY, t('Category'), '');
        $this->registerSetting(self::SETTING_TYPE, t('Type'), '');
        $this->registerSetting(self::SETTING_USER, t('User'), '');
        $this->registerSetting(self::SETTING_DATE, t('Date'));
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
    }

    protected function injectElements(HTML_QuickForm2_Container $container) : void
    {
        $this->injectCategories();
        $this->injectTypes();
        $this->injectUser();

        $this->addElementDateSearch(self::SETTING_DATE, $container);
    }

    private function injectUser() : void
    {
        $collection = AppFactory::createUsers();
        $ids = $this->collection->getAvailableUserIDs();

        $users = $this->addSelect(self::SETTING_USER);

        $users->addOption(t('Any user'), '');

        foreach($ids as $id) {
            $user = $collection->getByID($id);
            $users->addOption($user->getLabel(), $id);
        }
    }

    private function injectTypes() : void
    {
        $types = $this->addSelect(self::SETTING_TYPE);
        $types->addOption(t('All types'), '');

        $list = $this->collection->getAvailableTypes();
        foreach($list as $type) {
            $types->addOption($type, $type);
        }
    }

    private function injectCategories() : void
    {
        $categories = $this->addSelect(self::SETTING_CATEGORY);
        $categories->addOption(t('All categories'), '');

        $list = $this->collection->getAvailableCategories();
        foreach($list as $category) {
            $categories->addOption($category, $category);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::_configureFilters()
     */
    protected function _configureFilters() : void
    {
        $this->filters->selectDate((string)$this->getSetting(self::SETTING_DATE));
        $this->filters->selectCategory((string)$this->getSetting(self::SETTING_CATEGORY));
        $this->filters->selectType((string)$this->getSetting(self::SETTING_TYPE));
        $this->filters->setSearch((string)$this->getSetting(self::SETTING_SEARCH));

        $this->configureUser();
    }

    private function configureUser() : void
    {
        $userID = (int)$this->getSetting(self::SETTING_USER);
        $collection = AppFactory::createUsers();

        if($userID > 0 && $collection->idExists($userID)) {
            $this->filters->selectUser($userID);
        }
    }
}
