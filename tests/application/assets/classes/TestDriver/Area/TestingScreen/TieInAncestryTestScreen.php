<?php
/**
 * @package TestDriver
 * @subpackage Testing
 */

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Collection\Admin\BaseRecordSelectionTieIn;
use Application_Admin_Area_Mode;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use TestDriver\Collection\Admin\MythologicalRecordSelectionTieIn;
use TestDriver\TestDBRecords\TestDBCollection;
use TestDriver\TestDBRecords\TestDBRecordSelectionTieIn;
use UI;
use UI_Themes_Theme_ContentRenderer;

/**
 * Screen used to test the chaining of screen tie-ins.
 * See the base tie-in class for documentation:
 * {@see BaseRecordSelectionTieIn}
 *
 * @package TestDriver
 * @subpackage Testing
 */
class TieInAncestryTestScreen
    extends Application_Admin_Area_Mode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'tiein-ancestry-test';
    private TestDBRecordSelectionTieIn $parentTieIn;
    private MythologicalRecordSelectionTieIn $childTieIn;

    public static function getTestLabel(): string
    {
        return t('Tie-in ancestry');
    }

    protected function _handleBeforeActions(): void
    {
        $this->startTransaction();
        TestDBCollection::getInstance()->populateWithTestRecords();
        $this->endTransaction();

        $this->parentTieIn = new TestDBRecordSelectionTieIn($this);
        $this->childTieIn = new MythologicalRecordSelectionTieIn($this, null, $this->parentTieIn);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setAbstract(sb()
            ->t('This screen shows how screen tie-ins can be chained.')
            ->t('Setting a parent tie-in will enable that tie-in only once its parent has a record selected.')
            ->t('Additionally, the URLs will automatically inherit the parent tie-in URL parameters for their whole ancestry.')
        );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent(
                $this->ui->createMessage(t(
                    'The records %1$s and %2$s have been selected successfully.',
                    sb()->bold($this->parentTieIn->requireRecord()->getLabel()),
                    sb()->bold($this->childTieIn->requireRecord()->getLabel())
                ))
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
