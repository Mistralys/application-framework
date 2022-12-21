<?php
/**
 * @package Application
 * @subpackage Administration
 * @see \Application\Admin\Area\Devel\BaseDeploymentHistoryScreen
 */

declare(strict_types=1);

namespace Application\Admin\Area\Devel;

use Application;
use Application\DeploymentRegistry;
use Application_Admin_Area_Mode;
use AppUtils\ConvertHelper;
use UI;

/**
 * Abstract class for the deployment history developer screen,
 * which shows a list of the application deployments that were
 * stored, with versions and release dates.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseDeploymentHistoryScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'deploy-history';
    public const GRID_NAME = 'deployment_history';
    public const REQUEST_PARAM_DELETE_HISTORY = 'clear_history';

    private DeploymentRegistry $registry;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Deployment history');
    }

    public function getTitle(): string
    {
        return t('Deployment history');
    }

    protected function _handleActions(): bool
    {
        $this->registry = Application::createDeploymentRegistry();

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
                    sb()->linkOpen(APP_FRAMEWORK_DOCUMENTATION_URL, true),
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

    protected function _renderContent()
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
