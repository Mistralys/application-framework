<?php

declare(strict_types=1);

class Application_AjaxMethods_NotepadDelete extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->notepad->deleteNote($this->note);

        $this->sendSuccessResponse();
    }

    /**
     * @var Application_User_Notepad
     */
    private $notepad;

    /**
     * @var Application_User_Notepad_Note
     */
    private $note;

    protected function validateRequest()
    {
        $this->notepad = Application::getUser()->getNotepad();

        $noteID = intval($this->request->registerParam('note_id')->setInteger()->get());

        // Note not found? Assume it's been deleted already.
        if(!$this->notepad->idExists($noteID))
        {
            $this->sendSuccessResponse();
        }

        $this->note = $this->notepad->getNoteByID($noteID);
    }

    /**
     * @return never
     */
    private function sendSuccessResponse()
    {
        $this->sendResponse(array('success' => 'yes'));
    }
}
