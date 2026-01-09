<?php

declare(strict_types=1);

use Application\Application;

class Application_AjaxMethods_NotepadAdd extends Application_AjaxMethod
{
    public const METHOD_NAME = 'NotepadAdd';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $note = $this->notepad->addNote('', '');

        $payload = array(
            'note_id' => $note->getID()
        );

        $this->sendResponse($payload);
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