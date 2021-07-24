<?php

declare(strict_types=1);

class Application_AjaxMethods_NotepadGet extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->sendResponse($this->note->serialize());
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

         if($this->notepad->idExists($noteID))
         {
             $this->note = $this->notepad->getNoteByID($noteID);
             return;
         }

         $this->sendErrorUnknownElement(t('Notepad note'));
    }
}
