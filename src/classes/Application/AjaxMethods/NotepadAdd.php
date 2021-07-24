<?php

declare(strict_types=1);

class Application_AjaxMethods_NotepadAdd extends Application_AjaxMethod
{
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