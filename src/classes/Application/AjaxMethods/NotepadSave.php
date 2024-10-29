<?php

declare(strict_types=1);

class Application_AjaxMethods_NotepadSave extends Application_AjaxMethod
{
    public const METHOD_NAME = 'NotepadSave';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

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
            ->get();

        $this->content = (string)$this->request->registerParam('content')
            ->addFilterTrim()
            ->get();
    }
}
