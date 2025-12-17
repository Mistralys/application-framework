<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Screens\Mode;

use Application\Tags\Admin\TagScreenRights;
use Application\Tags\Admin\Traits\TagModeInterface;
use Application\Tags\Admin\Traits\TagModeTrait;
use Application\Tags\TagCollection;
use Application\Tags\TagCriteria;
use Application\Tags\TagRecord;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;
use UI;
use UI_DataGrid_Action;

/**
 * @property TagCriteria $filters
 */
class ListMode extends BaseRecordListMode implements TagModeInterface
{
    use TagModeTrait;

    public const string URL_NAME = 'list';

    public const string COL_LABEL = 'label';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TagScreenRights::SCREEN_LIST;
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Delete tags') => TagScreenRights::SCREEN_LIST_DELETE,
        );
    }

    /**
     * @param DBHelperRecordInterface $record
     * @param DBHelper_BaseFilterCriteria_Record $entry
     * @return array<string,mixed>
     * @throws BaseClassHelperException
     */
    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $tag = ClassHelper::requireObjectInstanceOf(
            TagRecord::class,
            $record
        );

        return array(
            self::COL_LABEL => sb()->add($tag->getLabelLinked())
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_LABEL, t('Label'))
            ->setSortable(true, TagCollection::COL_LABEL);
    }

    protected function configureFilters(): void
    {
        $this->filters->selectRootTags();
    }

    protected function configureActions(): void
    {
        $this->grid->addAction('delete-tags', t('Delete...'))
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
            ->setCallback($this->multiDeleteTags(...))
            ->requireRight(TagScreenRights::SCREEN_LIST_DELETE)
            ->makeConfirm(sb()
                ->para(sb()
                    ->bold(t('This will delete the selected tags.'))
                )
                ->para(sb()
                    ->cannotBeUndone()
                )
            );
    }

    private function multiDeleteTags(UI_DataGrid_Action $action) : void
    {
        $collection = $this->createCollection();

        $action->createRedirectMessage($collection->getAdminListURL())
            ->single(t('The tag %1$s has been deleted at %2$s.', '$label', '$time'))
            ->none(t('No tags selected that could be deleted.'))
            ->multiple(t('%1$s tags have been deleted at %2$s.', '$amount', '$time'))
            ->processDeleteDBRecords($collection)
            ->redirect();
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminURL();
    }

    public function getNavigationTitle(): string
    {
        return t('List');
    }

    public function getTitle(): string
    {
        return t('Available root tags');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setAbstract(sb()
            ->t('This list shows all available root-level tags.')
            ->t('Klick on a tag to manage its nested tags tree.')
        );

        $this->help
            ->setSummary(t('Working with tags'));

        $this->help->addPara(sb()
            ->t(
                'Tags can be used to categorize any elements that support tagging in %1$s.',
                sb()->italic($this->driver->getAppNameShort())
            )
        );

        $this->help->addHeader(t('Usage recommendations'));

        $this->help->addPara(sb()
            ->t('The tagging management distinguishes between root-level and regular tags:')
        );

        $this->help->addPara(sb()
            ->t(
                '%1$sRoot-level tags%2$s are the topmost tags in the hierarchy, and are meant to be used as the main element categories.',
                '<strong>',
                '</strong>'
            )
            ->t(
                'In practice, you will typically create a root-level tag for each element type in %1$s that you wish to tag.',
                sb()->italic($this->driver->getAppNameShort())
            )
        );

        $this->help->addPara(sb()
            ->t('%1$sRegular tags%2$s are nested under root tags, and are those you will use to assign to elements.',
                '<strong>',
                '</strong>'
            )
        );
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-tag', t('Create tag...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->createCollection()->getAdminCreateURL())
            ->requireRight(TagScreenRights::SCREEN_CREATE)
            ->makePrimary();

        $this->sidebar->addSeparator();

        parent::_handleSidebar();
    }
}
