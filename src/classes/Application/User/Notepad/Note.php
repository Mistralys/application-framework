<?php

declare(strict_types=1);

use AppUtils\Microtime;

class Application_User_Notepad_Note
{
    const ERROR_NO_DATA_FOUND = 90001;

    const KEY_DATE = 'date';
    const KEY_TITLE = 'title';
    const KEY_CONTENT = 'content';
    const KEY_ID = 'id';

    /**
     * @var Application_User_Notepad
     */
    private $notepad;

    /**
     * @var string
     */
    private $keyName;

    /**
     * @var int
     */
    private $id;

    /**
     * @var array<string,mixed>
     */
    private $data;

    /**
     * Creates a note by its ID.
     *
     * @param Application_User_Notepad $notepad
     * @param int $id
     * @throws Application_Exception
     *
     * @see Application_User_Notepad_Note::ERROR_NO_DATA_FOUND
     */
    public function __construct(Application_User_Notepad $notepad, int $id)
    {
        $this->id = $id;
        $this->notepad = $notepad;
        $this->keyName = $this->notepad->getNoteKey($id);

        $json = $notepad->getUser()->getSetting($this->keyName);

        $data = json_decode($json, true);
        if($data === false || empty($data))
        {
            throw new Application_Exception(
                'Missing note data set',
                sprintf(
                    'No data found for notepad note [%s] stored in user key [%s].',
                    $this->id,
                    $this->keyName
                ),
                self::ERROR_NO_DATA_FOUND
            );
        }

        $this->data = $data;
    }

    public function getID() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return strval($this->data[self::KEY_TITLE]);
    }

    public function getContent() : string
    {
        return strval($this->data[self::KEY_CONTENT]);
    }

    public function renderContent() : string
    {
        return Parsedown::instance()->parse($this->getContent());
    }

    public function getDate() : Microtime
    {
        return new Microtime(strval($this->data[self::KEY_DATE]));
    }

    public function setTitle(string $title) : void
    {
        $this->data[self::KEY_TITLE] = $title;
    }

    public function setContent(string $content) : void
    {
        $this->data[self::KEY_CONTENT] = $content;
    }

    public function save() : void
    {
        $user = $this->notepad->getUser();

        $user->setSetting($this->keyName, json_encode($this->data));
        $user->saveSettings();
    }

    public static function insertNew(Application_User_Notepad $notepad, int $id, string $content, string $title='') : void
    {
        $data = array(
            self::KEY_ID => $id,
            self::KEY_CONTENT => $content,
            self::KEY_TITLE => $title,
            self::KEY_DATE => (new Microtime())->getMySQLDate()
        );

        $user = $notepad->getUser();

        $user->setSetting($notepad->getNoteKey($id), json_encode($data));
        $user->saveSettings();
    }

    public function serialize() : array
    {
        return array(
            self::KEY_ID => $this->getID(),
            self::KEY_CONTENT => $this->getContent(),
            'html' => $this->renderContent(),
            self::KEY_TITLE => $this->getTitle(),
            self::KEY_DATE => $this->getDate()->getISODate()
        );
    }
}
