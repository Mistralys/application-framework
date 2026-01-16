<?php

declare(strict_types=1);

class Application_User_Recent_NoteCategory extends Application_User_Recent_Category
{
    public const string REQUEST_PARAM_UNPIN_NOTE = 'unpin-note';

    private Application_User_Notepad_Note $note;

    public function __construct(Application_User_Recent $recent, Application_User_Notepad_Note $note)
    {
        $this->note = $note;

        $alias = 'note-'.$note->getID();

        parent::__construct($recent, $alias, $this->resolveTitle($note));
    }

    /**
     * @return Application_User_Notepad_Note
     */
    public function getNote() : Application_User_Notepad_Note
    {
        return $this->note;
    }

    private function resolveTitle(Application_User_Notepad_Note $note) : string
    {
        $title = $note->getTitle();

        if(!empty($title)) {
            return $title;
        }

        return t('Pinned note');
    }

    public function renderContent() : string
    {
        return $this->note->renderContent();
    }

    public function getIcon() : ?UI_Icon
    {
        return UI::icon()->notepad();
    }

    public function getAdminURLUnpin(array $params=array()) : string
    {
        $params[self::REQUEST_PARAM_UNPIN_NOTE] = $this->note->getID();

        return $this->recent->getAdminURL($params);
    }
}
