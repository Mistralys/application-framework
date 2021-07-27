<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

class Application_User_Notepad
{
    const ERROR_NOTE_NOT_FOUND = 89901;

    const KEY_NOTES_INDEX = 'notepad-index';
    const KEY_NOTE_ID = 'notepad-note-%s';

    /**
     * @var Application_User
     */
    private $user;

    /**
     * @var array<string,mixed>
     */
    private $index;

    /**
     * @var array<string,mixed>
     */
    private $defaultIndex = array(
        'counter' => 0,
        'notes' => array()
    );

    public function __construct(Application_User $user)
    {
        $this->user = $user;

        $this->reset();
    }

    public function reset() : void
    {
        $this->index = $this->getIndex();
    }

    public function clearNotes() : void
    {
        DBHelper::delete(
            "DELETE FROM
                `user_settings`
            WHERE
                `setting_name` LIKE 'notepad-%'"
        );

        $this->reset();
    }

    /**
     * @return Application_User
     */
    public function getUser() : Application_User
    {
        return $this->user;
    }

    /**
     * Retrieves all available notes, sorted by most recent
     * to oldest.
     *
     * @return Application_User_Notepad_Note[]
     * @throws Application_Exception
     */
    public function getAll() : array
    {
        $ids = $this->getNoteIDs();
        $result = array();

        foreach($ids as $noteID)
        {
            $result[] = $this->getNoteByID($noteID);
        }

        usort($result, function (Application_User_Notepad_Note $a, Application_User_Notepad_Note $b)
        {
            return $b <=> $a;
        });

        return $result;
    }

    public function addNote(string $content, string $title='') : Application_User_Notepad_Note
    {
        $id = $this->getNextNoteID();

        Application_User_Notepad_Note::insertNew(
            $this,
            $id,
            $content,
            $title
        );

        return $this->getNoteByID($id);
    }

    public function getNoteKey(int $id) : string
    {
        return sprintf(
            self::KEY_NOTE_ID,
            $id
        );
    }

    /**
     * Fetches a note by its ID.
     *
     * @param int $id
     * @return Application_User_Notepad_Note
     * @throws Application_Exception
     *
     * @see Application_User_Notepad::ERROR_NOTE_NOT_FOUND
     */
    public function getNoteByID(int $id) : Application_User_Notepad_Note
    {
        if(in_array($id, $this->index['notes'], true))
        {
            return new Application_User_Notepad_Note(
                $this,
                $id
            );
        }

        throw new Application_Exception(
            'Note does not exist',
            sprintf(
                'No note found for ID [%s]',
                $id
            ),
            self::ERROR_NOTE_NOT_FOUND
        );
    }

    private function getNextNoteID() : int
    {
        $this->index['counter']++;
        $id = $this->index['counter'];
        $this->index['notes'][] = $id;

        $this->saveIndex();

        return $id;
    }

    private function saveIndex() : void
    {
        $this->user->setSetting(self::KEY_NOTES_INDEX, json_encode($this->index));
        $this->user->saveSettings();
    }

    private function getIndex() : array
    {
        $index = $this->user->getSetting(self::KEY_NOTES_INDEX);
        if(empty($index)) {
            return $this->defaultIndex;
        }

        $data = json_decode($index, true);

        if($data !== false) {
            return $data;
        }

        return $this->defaultIndex;
    }

    public function deleteNote(Application_User_Notepad_Note $note) : void
    {
        $deleteID = $note->getID();

        $this->user->removeSetting($this->getNoteKey($deleteID));

        $this->index['notes'] = ConvertHelper::arrayRemoveValues($this->index['notes'], array($deleteID));

        $this->saveIndex();
    }

    public function getNoteIDs() : array
    {
        return $this->index['notes'];
    }

    public function countNotes() : int
    {
        return count($this->index['notes']);
    }

    public function idExists(int $noteID) : bool
    {
        return in_array($noteID, $this->index['notes']);
    }

    public static function getJSOpen() : string
    {
        return 'Driver.DialogNotepad();';
    }

    public static function getTooltipText() : string
    {
        return t('Your personal notepad for taking notes.');
    }
}
