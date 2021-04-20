<?php

class Application_AjaxMethods_GetGridFullViewHTML extends Application_AjaxMethod
{
    /**
     * @var Application_AjaxMethods_GetGridFullViewHTML_Grid[]
     */
    private $grids = array();

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