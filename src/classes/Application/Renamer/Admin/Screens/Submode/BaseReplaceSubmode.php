<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\Development\DevScreenRights;
use Application\Renamer\Index\RenamerIndex;
use Application\Renamer\RenamingManager;
use DBHelper;
use Maileditor\Renamer\RenamerConfig;
use UI;
use UI\CSSClasses;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseReplaceSubmode extends BaseSubmode
{
    public const string URL_NAME = 'replace';
    public const string SETTING_REPLACE = 'replace';


    private RenamerConfig $config;
    private RenamerIndex $collection;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RENAMER_REPLACE;
    }

    public function getNavigationTitle(): string
    {
        return t('Replace');
    }

    public function getTitle(): string
    {
        return t('Replace');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->config = BaseConfigurationSubmode::requireConfig();
        $this->collection = RenamingManager::getInstance()->createCollection();

        $this->createSettingsForm();

        if($this->isFormValid()) {
            $values = $this->getFormValues();
            $this->handleReplace($values[self::SETTING_REPLACE]);
        }

        return true;
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm('renamer_replace');

        $this->addSection(t('Replace Settings'))
            ->setIcon(UI::icon()->settings())
            ->expand();

        $this->injectReplace();

        $this->addSection(t('Columns reference'))
            ->setIcon(UI::icon()->information())
            ->setAbstract(t('The following database columns will be affected by the replace operation.'));

        $this->injectColumns();
    }

    protected function _handleHelp(): void
    {
        BaseResultsSubmode::configureSubline($this->renderer, $this->config);

        $this->renderer->setAbstract(sb()
            ->t('This will replace all matches with the specified replacement text in the selected database columns.')
            ->note()
            ->t('This action will be logged in the application\'s message log.')
        );
    }

    private function injectColumns() : void
    {
        $this->addElementStatic(t('Columns'), $this->renderColumnList());
    }

    private function renderColumnList() : string
    {
        $columns = array();
        foreach($this->config->getColumns() as $column) {
            $columns[] = (string)sb()
                ->add($column->getLabel())
                ->muted('-')
                ->muted($column->getTableName().'.'.$column->getColumnName());
        }

        return (string)sb()->ul($columns);
    }

    private function injectReplace() : void
    {
        $el = $this->addElementText(self::SETTING_REPLACE, t('Replacement'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->setComment(sb()
            ->t('The text to replace all matches with.')
        );

        $this->makeRequired($el);
    }

    private function handleReplace(string $replace) : void
    {
        $this->startTransaction();

        $search = $this->config->getSearch();

        $ids = $this->collection->getFilterCriteria()->getIDs();
        $counter = 0;
        foreach($ids as $id)
        {
            $counter++;

            $record = $this->collection->getByID($id);
            $record->processReplace($search, $replace);

            // Reset the collection periodically to free memory
            if ($counter % 30 === 0) {
                $this->collection->resetCollection();
            }
        }

        $this->endTransaction();

        BaseConfigurationSubmode::clearConfig();

        DBHelper::startTransaction();

        AppFactory::createMessageLog()->addInfo(sprintf(
            'DB Renamer: Replaced all matches for search [%1$s] with [%2$s] in %3$s records in the database columns: [%4$s].',
            $search,
            $replace,
            count($ids),
            implode(', ', $this->config->getColumnIDs())
        ));

        DBHelper::commitTransaction();

        $this->redirectWithSuccessMessage(
            t(
                '%1$s matches have been successfully replaced in the database at %2$s.',
                count($ids),
                sb()->time()
            ),
            RenamingManager::getInstance()->adminURL()->configuration()
        );
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('replace', t('Replace now'))
            ->makePrimary()
            ->setIcon(UI::icon()->generate())
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('back', t('Back to results'))
            ->setIcon(UI::icon()->back())
            ->link(RenamingManager::getInstance()->adminURL()->results());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }
}
