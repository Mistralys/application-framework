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

        OutputBuffering::start();
        ?>
        <h1><?php echo t('%1$s API documentation', $this->driver->getAppNameShort()) ?></h1>
        <div class="method-abstract">
            <p>
            <?php
                pts('Below is a list of all API methods available in the system.');
                pts('Click on a method name to view detailed documentation.');
            ?>
            </p>
        </div>
        <?php

        echo $this->grid->render($this->compileEntries());

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function createDataGrid() : void
    {
        $grid = $this->getUI()->createDataGrid('api-methods-list');

        $grid->disableFooter();

        $grid->addColumn('name', t('Method name'));
        $grid->addColumn('accepts', t('Accepts'));
        $grid->addColumn('returns', t('Returns'));
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
                'accepts' => $method->getRequestMime(),
                'returns' => $method->getResponseMime()
            );
        }

        return $entries;
    }
}
