<?php

declare(strict_types=1);

class Application_AjaxMethods_NotepadGetIDs extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->sendResponse($this->notepad->getNoteIDs());
    }

    /**
     * @var Application_User_Notepad
     */
    private $notepad;

    protected function validateRequest()
    {
        $this->notepad = Application::getUser()->getNotepad();
    }
}
