<?php

declare(strict_types=1);

use Application\Application;

class Application_AjaxMethods_NotepadPin extends Application_AjaxMethod
{
    public const METHOD_NAME = 'NotepadPin';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $recent = $this->user->getRecent();

        $recent->pinNote($this->note);

        $this->sendResponse(array('success' => 'yes'));
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
