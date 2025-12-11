<?php
/**
 * @package Application
 * @subpackage Administration
 * @see \Application\DeploymentRegistry\Admin\Screens\DeploymentHistoryMode
 */

declare(strict_types=1);

namespace Application\DeploymentRegistry\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\AppFactory;
use Application\DeploymentRegistry\DeploymentRegistry;
use Application\Development\Admin\DevScreenRights;
use AppUtils\ConvertHelper;
use Mistralys\AppFrameworkDocs\DocumentationHub;
use UI;
use UI_Themes_Theme_ContentRenderer;

/**
 * Abstract class for the deployment history developer screen,
 * which shows a list of the application deployments that were
 * stored, with versions and release dates.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DeploymentHistoryMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'deploy-history';
    public const string GRID_NAME = 'deployment_history';
    public const string REQUEST_PARAM_DELETE_HISTORY = 'clear_history';

    private DeploymentRegistry $registry;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_DEPLOYMENT_HISTORY;
    }

    public function getNavigationTitle(): string
    {
        return t('Deployment history');
    }

    public function getTitle(): string
    {
        return t('Deployment history');
    }

    public function getDevCategory(): string
    {
        return t('Logs');
    }

    protected function _handleActions(): bool
    {
        $this->registry = AppFactory::createDeploymentRegistry();

        if($this->request->getBool(self::REQUEST_PARAM_DELETE_HISTORY))
        {
            $this->registry->clearHistory();

            $this->redirectWithSuccessMessage(
                t('The deployment history has been deleted successfully at %1$s.', sb()->time()),
                $this->registry->getAdminURLHistory()
            );
        }

        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setTitle($this->getTitle())
            ->setAbstract(sb()
                ->t('Shows a history of the %1$s deployments.', $this->driver->getAppNameShort())
                ->note()
                ->t('This requires the deployment callback script to be called whenever the application is deployed.')
                ->t(
                    'See the %1$sdocumentation%2$s for details.',
                    sb()->linkOpen(DocumentationHub::getPageLink()->deploying(), true),
                    sb()->linkClose()
                )
            );
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('delete_history', t('Delete history...'))
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->link($this->registry->getAdminURLDeleteHistory())
            ->makeConfirm(sb()
                ->para(t('This will delete all entries in the deployment history.'))
                ->para(sb()->cannotBeUndone())
            );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $deployments = $this->registry->getHistory();

        $grid = $this->getUI()->createDataGrid(self::GRID_NAME);
        $grid->setEmptyMessage(t('No deployments found.'));
        $grid->addHiddenScreenVars();

        $grid->addColumn('version', t('Version'))->setSortingString();
        $grid->addColumn('date', t('Release date'))->setSortingDateTime('date_sort');

        $entries = array();
        foreach($deployments as $deployment)
        {
            $entries[] = array(
                'version' => $deployment->getVersion(),
                'date_sort' => $deployment->getDate(),
                'date' => ConvertHelper::date2listLabel($deployment->getDate(), true, true)
            );
        }

        return $this->renderer
            ->appendDataGrid($grid, $entries)
            ->makeWithSidebar();
    }
}
