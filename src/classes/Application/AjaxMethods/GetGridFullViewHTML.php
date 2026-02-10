<?php
/**
 * File containing the class {@see Application_AjaxMethods_GetGridFullViewHTML}.
 *
 * @package Application
 * @subpackage AjaxMethods
 * @see Application_AjaxMethods_GetGridFullViewHTML
 */

declare(strict_types=1);

/**
 * Called by the clientside datagrid to generate the HTML for the
 * full view window, which shows a grid in full screen view.
 *
 * It gets the title of the datagrid, as well as the body HTML,
 * and transforms this into a full HTML document with adequate
 * styling.
 *
 * @package Application
 * @subpackage AjaxMethods
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_ui_datagrid_fullview
 * @see Application_AjaxMethods_GetGridFullViewHTML_Grid
 * @see Theme: js/ui/datagrid.js
 * @see JavaScript: UI_DataGrid.Maximize()
 */
class Application_AjaxMethods_GetGridFullViewHTML extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'GetGridFullViewHTML';

    /**
     * @var Application_AjaxMethods_GetGridFullViewHTML_Grid[]
     */
    private $grids = array();

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $ui = UI::createInstance($this->driver->getApplication());
        $ui->getResourceManager()->clearLoadkeys(); // force all client resources to load in the template

        $this->loadGrids();

        $template = $ui->getPage()->createTemplate('ui/datagrid/fullview');
        $template->setVar('grids', $this->grids);

        $this->sendResponse(array(
            'html' => $template->render()
        ));
    }

    private function loadGrids() : void
    {
        $data = $this->request->registerParam('grids')->setArray()->get();

        if(!is_array($data) || empty($data)) {
            return;
        }

        foreach($data as $gridDef) {
            $this->grids[] = new Application_AjaxMethods_GetGridFullViewHTML_Grid(
                $gridDef['title'],
                $gridDef['html']
            );
        }
    }
}
