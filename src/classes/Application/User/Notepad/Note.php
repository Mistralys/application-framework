<?php

declare(strict_types=1);

use Application\MarkdownRenderer\MarkdownRenderer;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Microtime;

class Application_User_Notepad_Note
{
    public const int ERROR_NO_DATA_FOUND = 90001;

    public const string KEY_DATE = 'date';
    public const string KEY_TITLE = 'title';
    public const string KEY_CONTENT = 'content';
    public const string KEY_ID = 'id';

    private Application_User_Notepad $notepad;
    private string $keyName;
    private int $id;

    /**
     * @var array<string,mixed>
     */
    private array $data;

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

        $data = JSONConverter::json2array($json);
        if(empty($data))
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
        return (string)$this->data[self::KEY_TITLE];
    }

    public function getContent() : string
    {
        return (string)$this->data[self::KEY_CONTENT];
    }

    public function renderContent() : string
    {
        return MarkdownRenderer::create()->render($this->getContent());
    }

    public function getDate() : Microtime
    {
        return new Microtime((string)$this->data[self::KEY_DATE]);
    }

    public function setTitle(string $title) : void
    {
        $this->data[self::KEY_TITLE] = self::filterText($title);
    }

    public function setContent(string $content) : void
    {
        $this->data[self::KEY_CONTENT] = self::filterText($content);
    }

    private static function filterText(string $text) : string
    {
        return $text;
    }

    public function save() : void
    {
        $user = $this->notepad->getUser();

        $user->setSetting($this->keyName, JSONConverter::var2json($this->data));
        $user->saveSettings();
    }

    public static function insertNew(Application_User_Notepad $notepad, int $id, string $content, string $title='') : void
    {
        $data = array(
            self::KEY_ID => $id,
            self::KEY_CONTENT => self::filterText($content),
            self::KEY_TITLE => self::filterText($title),
            self::KEY_DATE => new Microtime()->getMySQLDate()
        );

        $user = $notepad->getUser();

        $user->setSetting($notepad->getNoteKey($id), JSONConverter::var2json($data));
        $user->saveSettings();
    }

    public function serialize() : array
    {
        return array(
            self::KEY_ID => $this->getID(),
            self::KEY_CONTENT => $this->getContent(),
            'html' => $this->renderContent(),
            self::KEY_TITLE => $this->getTitle(),
            self::KEY_DATE => $this->getDate()->getISODate(),
            'isPinned' => $this->isPinned()
        );
    }

    public function isPinned() : bool
    {
        $recent = $this->notepad->getUser()->getRecent();

        return in_array($this->getID(), $recent->getPinnedNoteIDs(), true);
    }
}
