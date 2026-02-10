<?php

declare(strict_types=1);

namespace Application\Maintenance\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\Application;
use Application\Maintenance\Admin\MaintenanceScreenRights;
use Application\Maintenance\Admin\Traits\MaintenanceSubmodeInterface;
use Application\Maintenance\Admin\Traits\MaintenanceSubmodeTrait;
use Application_Maintenance;
use AppUtils\ConvertHelper;
use UI;
use UI_DataGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property MaintenanceMode $mode
 */
class ListSubmode extends BaseSubmode implements MaintenanceSubmodeInterface
{
    use MaintenanceSubmodeTrait;

    public const string URL_NAME = 'list';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MaintenanceScreenRights::SCREEN_LIST;
    }

    public function getNavigationTitle(): string
    {
        return t('Maintenance plans');
    }

    public function getTitle(): string
    {
        return t('Maintenance plans');
    }

    protected Application_Maintenance $maintenance;

    protected function _handleActions(): bool
    {
        $this->maintenance = AppFactory::createMaintenance();

        if ($this->request->getBool('simulate_plan')) {
            $this->handleSimulate();
        }

        if ($this->request->getBool('delete')) {
            $this->handleDelete();
        }

        $this->createDataGrid();

        return true;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        // do some cleanup on the occasion to have a clean list every time.
        $this->maintenance->cleanUp();
        $this->maintenance->save();

        $plans = $this->maintenance->getPlans();

        $entries = array();
        foreach ($plans as $plan) {
            $entries[] = array(
                'start' => ConvertHelper::date2listLabel($plan->getStart(), true, true),
                'end' => ConvertHelper::date2listLabel($plan->getEnd(), true, true),
                'duration' => ConvertHelper::interval2string($plan->getDuration()),
                'enabled' => $plan->getEnabledBadge(),
                'actions' =>
                    UI::button()
                        ->setIcon(UI::icon()->delete())
                        ->link($this->getURL(array('delete' => 'yes', 'plan_id' => $plan->getID())))
                        ->makeMini()
                        ->makeDangerous() . ' ' .
                    UI::button()
                        ->makeMini()
                        ->setIcon(UI::icon()->view())
                        ->setTooltip(t('Preview the maintenance screen for regular users'))
                        ->link($this->getURL(array('simulate_plan' => 'yes', 'plan_id' => $plan->getID())), '_blank')
            );
        }

        return $this->renderer
            ->makeWithSidebar()
            ->appendDataGrid($this->grid, $entries);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('add_plan', t('Add new plan'))
            ->setIcon(UI::icon()->add())
            ->makePrimary()
            ->makeLinked($this->area->getURL(array('mode' => 'maintenance', 'submode' => 'create')));

        $this->sidebar->addSeparator();

        $this->sidebar->addInfoMessage(
            t('Current server time:') . ' ' .
            date('H:i')
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle());
    }

    protected UI_DataGrid $grid;

    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid('maintenance-plans');

        $grid->addColumn('start', t('Start'));
        $grid->addColumn('duration', t('Duration'));
        $grid->addColumn('end', t('End'));
        $grid->addColumn('enabled', t('Enabled?'));
        $grid->addColumn('actions', '')->setCompact()->roleActions();

        $this->grid = $grid;
    }

    protected function handleDelete() : never
    {
        $plan = $this->getPlan();

        $this->maintenance->delete($plan);
        $this->maintenance->save();

        $this->redirectWithSuccessMessage(
            t('The maintenance plan was removed successfully.'),
            $this->getURL()
        );
    }

    protected function handleSimulate() : never
    {
        $plan = $this->getPlan();

        echo $this->renderTemplate('maintenance', array('plan' => $plan));

        Application::exit('Displayed rendered template.');
    }

    protected function getPlan()
    {
        $id = $this->request->registerParam('plan_id')->setInteger()->setCallback(array($this->maintenance, 'idExists'))->get();

        if (empty($id)) {
            $this->redirectWithErrorMessage(
                t('Unknown maintenance plan specified.'),
                $this->getURL()
            );
        }

        return $this->maintenance->getByID($id);
    }
}