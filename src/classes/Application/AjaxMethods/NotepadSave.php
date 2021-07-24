<?php

declare(strict_types=1);

use AppUtils\RegexHelper;

class Application_AjaxMethods_NotepadSave extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->note->setTitle($this->title);
        $this->note->setContent($this->content);
        $this->note->save();

        $this->sendResponse($this->note->serialize());
    }

    /**
     * @var Application_User_Notepad_Note
     */
    private $note;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    protected function validateRequest()
    {
        $notepad = Application::getUser()->getNotepad();

        $noteID = intval($this->request->registerParam('note_id')->setInteger()->get());

        if(!$notepad->idExists($noteID))
        {
            $this->sendErrorUnknownElement(t('Notepad note'));
        }

        $this->note = $notepad->getNoteByID($noteID);

        $this->title = (string)$this->request->registerParam('title')
            ->addFilterTrim()
            ->addHTMLSpecialcharsFilter()
            ->get();

        $this->content = (string)$this->request->registerParam('content')
            ->addFilterTrim()
            ->addHTMLSpecialcharsFilter()
            ->get();
    }
}
