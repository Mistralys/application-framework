<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use Application_Interfaces_Admin_Wizard_Step_Confirmation;
use Application_Traits_Admin_Wizard_Step_Confirmation;
use UI_PropertiesGrid;

class ConfirmStep extends BaseCreateStep implements Application_Interfaces_Admin_Wizard_Step_Confirmation
{
    use Application_Traits_Admin_Wizard_Step_Confirmation;

    public function render(): string
    {
        return 'Confirm';
    }

    protected function preProcess(): void
    {
    }

    public function getLabel(): string
    {
        return t('Confirm');
    }

    public function getAbstract(): string
    {
        return t('Please review your settings before continuing.');
    }

    protected function createReferenceID(): string
    {
        // TODO: Implement createReferenceID() method.
    }

    protected function populateSummaryGrid(UI_PropertiesGrid $grid): void
    {
        // TODO: Implement populateSummaryGrid() method.
    }
}
