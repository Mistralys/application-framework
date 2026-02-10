<?php

declare(strict_types=1);

namespace Application\Media\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use Application\AppFactory;
use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Traits\MediaModeInterface;
use Application\Media\Admin\Traits\MediaModeTrait;
use Application\Media\Collection\MediaCollection;
use Application\Tags\TagCollection;
use UI;
use UI_Themes_Theme_ContentRenderer;

class GlobalSettingsMode extends BaseMode implements MediaModeInterface
{
    use MediaModeTrait;

    public const string URL_NAME = 'settings';
    private TagCollection $tags;
    private MediaCollection $media;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_MEDIA_SETTINGS;
    }

    public function getNavigationTitle(): string
    {
        return t('Media settings');
    }

    public function getTitle(): string
    {
        return t('Media settings');
    }

    protected function _handleBeforeActions(): void
    {
        $this->tags = AppFactory::createTags();
        $this->media = AppFactory::createMediaCollection();
    }

    protected function _handleActions(): bool
    {
        $this->createSettingsForm();

        if($this->isFormValid()) {
            $this->saveSettings($this->getFormValues());
        }

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->media->adminURL()->settings());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('save_settings', t('Save now'))
            ->setIcon(UI::icon()->save())
            ->makePrimary()
            ->makeClickableSubmit($this);
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    // region: Form

    public const string FORM_NAME = 'media_settings';
    public const string SETTING_ROOT_TAG = 'root_media_tag';

    private function createSettingsForm() : void
    {
        $this->createFormableForm(
            self::FORM_NAME,
            array(
                self::SETTING_ROOT_TAG => $this->media->getRootTagID()
            )
        );

        $this->addSection(t('Tagging'))
            ->setIcon(UI::icon()->tags());

        $this->injectRootMediaTag();
    }

    private function injectRootMediaTag() : void
    {
        $el = $this->addElementSelect(self::SETTING_ROOT_TAG, t('Root tag'));
        $el->addClass('input-xxlarge');
        $el->setComment(sb()
            ->t('Choosing a tag here enables the tagging feature of media documents.')
            ->t('The selected tag\'s subtags will be used as the available tags for media documents.')
            ->nl()
            ->warning(sb()->noteBold())
            ->t('Changing this setting does not affect already tagged media documents, which will retain their current selection.')
        );

        $el->addOption(sb()->parentheses(sb()->t('No tag:')->t('Disable tagging features.')), '');

        $tags = $this->tags->getFilterCriteria()->selectRootTags()->getItemsObjects();

        foreach($tags as $tag) {
            $el->addOption($tag->getLabel(), $tag->getID());
        }
    }

    private function saveSettings(array $values) : void
    {
        $this->startTransaction();

        $tagID = $values[self::SETTING_ROOT_TAG] ?? '';

        if(!empty($tagID) && $this->tags->idExists((int)$tagID)) {
            $this->media->setRootTag($this->tags->getByID((int)$tagID));
        } else {
            $this->media->setRootTag(null);
        }

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t('The settings have been saved successfully at %1$s.', sb()->time()),
            $this->media->adminURL()->settings()
        );
    }

    // endregion
}
