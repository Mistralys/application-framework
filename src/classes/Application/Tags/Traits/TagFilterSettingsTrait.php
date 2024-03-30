<?php

declare(strict_types=1);

namespace Application\Tags\Traits;

use Application\AppFactory;
use Application\FilterSettings\SettingDef;
use Application\Tags\Interfaces\TagFilterSettingsInterface;
use Closure;

trait TagFilterSettingsTrait
{
    private ?SettingDef $tagSetting = null;

    protected function registerTagSetting(?string $name=null) : SettingDef
    {
        if(empty($name)) {
            $name = TagFilterSettingsInterface::DEFAULT_SETTING;
        }

        $setting = $this->registerSetting($name, t('Tags'))
            ->setRuntimeProperty('settingType', 'tags')
            ->setInjectCallback(Closure::fromCallable(array($this, 'inject_tags')))
            ->setConfigureCallback(Closure::fromCallable(array($this, 'configure_tags')));

        $this->tagSetting = $setting;

        return $setting;
    }

    public function getTagSetting() : ?SettingDef
    {
        return $this->tagSetting;
    }

    public function getSelectedTags() : array
    {
        $ids = $this->getSelectedTagIDs();
        $collection = AppFactory::createTags();
        $tags = array();

        foreach($ids as $id) {
            $tag = $collection->getByID($id);
            if($tag !== null) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    public function getSelectedTagIDs() : array
    {
        $setting = $this->getTagSetting();
        if($setting === null) {
            return array();
        }

        $value = $setting->getValue()->getArray();
        $result = array();

        foreach($value as $tagID) {
            $result[] = (int)$tagID;
        }

        return $result;
    }

    protected function inject_tags() : void
    {
        $el = $this->addMultiselect(TagFilterSettingsInterface::DEFAULT_SETTING);
        $el->makeMultiple();
        $el->enableFiltering();
        $el->enableSelectAll();

        foreach($this->getTagCollection()->getAvailableTags() as $tag) {
            $el->addOption($tag->getLabel(), $tag->getID());
        }
    }

    protected function configure_tags() : void
    {
        $this->getTaggableFilters()->selectTags($this->getSelectedTags());
    }
}
