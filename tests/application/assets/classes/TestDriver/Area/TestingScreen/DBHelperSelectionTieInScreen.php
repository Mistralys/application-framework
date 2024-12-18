<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application_Admin_Area_Mode;
use DBHelper\Admin\BaseRecordSelectionTieIn;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;
use UI;
use UI_Themes_Theme_ContentRenderer;

class DBHelperSelectionTieInScreen
    extends Application_Admin_Area_Mode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'dbhelper-selection-tiein';
    private TestDBRecordSelectionTieIn $selection;

    public static function getTestLabel(): string
    {
        return t('%1$s selection tie-in', 'DBHelper');
    }

    protected function _handleBeforeActions(): void
    {
        $this->startTransaction();
            TestDBCollection::getInstance()->populateWithTestRecords();
        $this->endTransaction();

        $this->selection = new TestDBRecordSelectionTieIn($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setAbstract(sb()
            ->t(
                'This screen demonstrates the use of the %1$s class.',
                sb()->code(BaseRecordSelectionTieIn::class)
            )
            ->t('The selection list is displayed automatically if no record ID is present in the request.')
            ->t('It effectively takes over rendering from the screen, so no additional work is required.')
        );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent(
                $this->ui->createMessage(t(
                    'The record %1$s has been selected successfully.',
                    sb()->bold($this->selection->requireItem()->getLabel()))
                )
                    ->makeNotDismissable()
                    ->makeSuccess()
            )
            ->appendContent(
                UI::button(t('Back to the selection'))
                    ->setIcon(UI::icon()->back())
                    ->link($this->getURL())
            );
    }
}
