<?php

declare(strict_types=1);

use Application\Application;

class Application_AjaxMethods_NotepadGetIDs extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'NotepadGetIDs';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

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
