<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIMethodInterface;
use AppUtils\OutputBuffering;
use UI_DataGrid;
use UI_Page_Template_Custom;

class APIMethodsOverviewTmpl extends UI_Page_Template_Custom
{
    public const string PARAM_METHODS = 'methods';

    private UI_DataGrid $grid;

    protected function preRender(): void
    {
        $this->createDataGrid();
    }

    protected function generateOutput(): void
    {
        $this->page->setTitle(t('%1$s API documentation', $this->driver->getAppNameShort()));

        echo $this->renderCleanFrame($this->grid->render($this->compileEntries()));
    }

    private function createDataGrid() : void
    {
        $grid = $this->getUI()->createDataGrid('api-methods-list');

        $grid->addColumn('name', t('Method name'));
        $grid->addColumn('version', t('Version'))->alignRight();

        $this->grid = $grid;
    }

    private function compileEntries() : array
    {
        $entries = array();

        foreach($this->getArrayVar(self::PARAM_METHODS) as $method)
        {
            if(!$method instanceof APIMethodInterface) {
                continue;
            }

            $entries[] = array(
                'name' => sb()->link($method->getMethodName(), $method->getDocumentationURL()),
                'version' => $method->getCurrentVersion(),
            );
        }

        return $entries;
    }
}
