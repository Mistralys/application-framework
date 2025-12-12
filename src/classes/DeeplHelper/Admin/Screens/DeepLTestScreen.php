<?php

declare(strict_types=1);

namespace DeeplHelper\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\AppFactory;
use Application\Development\DevManager;
use DeeplHelper;
use DeeplHelper\Admin\DeeplScreenRights;
use UI;
use UI_Themes_Theme_ContentRenderer;

class DeepLTestScreen extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'deepl-test';
    public const string FORM_NAME = 'deepl_test';
    public const string FIELD_TEXT = 'text_to_translate';
    public const string FIELD_SOURCE_LANGUAGE = 'source_language';
    public const string FIELD_TARGET_LANGUAGE = 'target_language';
    private DeeplHelper $helper;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DeeplScreenRights::SCREEN_TEST;
    }

    public function getNavigationTitle(): string
    {
        return t('DeepL Test');
    }

    public function getTitle(): string
    {
        return t('DeepL Test');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->translation());

        $this->renderer
            ->setAbstract(sb()
                ->t('This allows testing the DeepL translation service.')
                ->t('PHP exceptions are not caught, so that connection errors can be identified.')
                ->nl()
                ->note()
                ->t(
                    'The configuration settings are viewable in the %1$s screen.',
                    sb()->link(t('Application configuration'), DevManager::getInstance()->adminURL()->appConfiguration())
                )
            );
    }

    protected function _handleActions(): bool
    {
        $this->helper = AppFactory::createDeeplHelper();

        $this->createSettingsForm();

        if($this->isFormValid())
        {
            $values = $this->getFormValues();

            $this->handleTest(
                $values[self::FIELD_TEXT],
                $values[self::FIELD_SOURCE_LANGUAGE],
                $values[self::FIELD_TARGET_LANGUAGE]
            );
        }

        return true;
    }

    private function handleTest(string $text, string $sourceLocale, string $targetLocale) : void
    {
        $sourceCountry = AppFactory::createLocales()->getByID($sourceLocale)->getCountry();
        $targetCountry = AppFactory::createLocales()->getByID($targetLocale)->getCountry();

        $translator = AppFactory::createDeeplHelper()->createTranslator($sourceCountry, $targetCountry);
        $translator->addString('target_text', $text);
        $translator->translate();

        $translated = $translator->getStringByID('target_text')->getTranslatedText();

        $this->renderer->appendContent($this->ui->createSection(t('Translation result'))
            ->setContent($translated)
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->helper->adminURL()->testing());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('test_connection', t('Test now'))
            ->setIcon(UI::icon()->send())
            ->makeClickableSubmit($this);
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function getDefaultFormValues() : array
    {
        return array(
            self::FIELD_TEXT => 'Der braune Fuchs springt über den faulen Hund.',
            self::FIELD_SOURCE_LANGUAGE => 'de_DE',
            self::FIELD_TARGET_LANGUAGE => 'en_US'
        );
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->injectText();
        $this->injectSourceLanguage();
        $this->injectTargetLanguage();
    }

    private function injectText() : void
    {
        $el = $this->addElementTextarea(self::FIELD_TEXT, t('Text to translate'))
            ->addClass('input-xxlarge')
            ->addFilterTrim()
            ->setRows(5);

        $this->makeRequired($el);
    }

    private function injectSourceLanguage() : void
    {
        $el = $this->addElementSelect(self::FIELD_SOURCE_LANGUAGE, t('Source language'));

        foreach(AppFactory::createLocales()->getAll() as $locale) {
            $el->addOption($locale->getLabel(), $locale->getID());
        }
    }

    private function injectTargetLanguage() : void
    {
        $el = $this->addElementSelect(self::FIELD_TARGET_LANGUAGE, t('Target language'));

        foreach(AppFactory::createLocales()->getAll() as $locale) {
            $el->addOption($locale->getLabel(), $locale->getID());
        }
    }
}
