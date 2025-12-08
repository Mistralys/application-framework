<?php
/**
 * @package Messaging
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Messaging;

use Application;
use Application_Messaging_Message;
use Application_User;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\StringableInterface;
use DateTime;
use DBHelper;
use DBHelper_BaseCollection;
use UI;

/**
 * Helper class for managing messages between application users.
 *
 * @package Messaging
 * @subpackage Collection
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class MessagingCollection extends DBHelper_BaseCollection
{
    /**
     * The amount of time to wait between requests to the server
     * to update the messages list.
     *
     * @var integer The time, in seconds
     */
    public const int UI_PULL_DELAY = 20;

    public const string PRIORITY_NORMAL = 'normal';
    public const string PRIORITY_HIGH = 'high';
    const string COL_DATE_SENT = 'date_sent';
    const string COL_MESSAGE = 'message';
    const string TABLE_NAME = 'app_messaging';
    const string PRIMARY_NAME = 'message_id';
    const string RECORD_TYPE_NAME = 'message';
    const string COL_FROM_USER = 'from_user';
    const string COL_TO_USER = 'to_user';
    const string COL_PRIORITY = 'priority';
    public const string COL_RESPONSE = 'response';
    public const string COL_LOCK_ID = 'lock_id';
    public const string COL_CUSTOM_DATA = 'custom_data';
    public const string COL_DATE_RESPONDED = 'date_responded';
    public const string COL_IN_REPLY_TO = 'in_reply_to';
    public const string COL_DATE_RECEIVED = 'date_received';

    protected static array $injected = array();

    /**
     * Injects the javascript required to make the messaging work
     * clientside, with all needed includes and statements.
     *
     * @param UI $ui
     */
    public function injectJS(UI $ui): void
    {
        $uiKey = $ui->getInstanceKey();

        if (isset(self::$injected[$uiKey])) {
            return;
        }

        $ui->addJavascript('application/messaging.js');
        $ui->addJavascriptHeadVariable('Application_Messaging.PullDelay', self::UI_PULL_DELAY);
        $ui->addJavascriptOnload('Application_Messaging.Start()');

        self::$injected[$uiKey] = true;
    }

    protected array $messages = array();

    /**
     * Retrieves a message by its ID.
     *
     * @param int $record_id
     * @return Application_Messaging_Message
     */
    public function getByID(int $record_id): Application_Messaging_Message
    {
        if (isset($this->messages[$record_id])) {
            return $this->messages[$record_id];
        }

        $message = new Application_Messaging_Message($record_id, $this);
        $this->messages[$record_id] = $message;

        return $message;
    }

    /**
     * @var array<string,string>|null
     */
    protected static ?array $priorities = null;

    /**
     * Retrieves a list of all available priorities with their
     * human-readable labels.
     *
     * @return array<string,string>
     */
    public static function getPriorities(): array
    {
        if (!isset(self::$priorities)) {
            self::$priorities = array(
                self::PRIORITY_NORMAL => t('Normal priority'),
                self::PRIORITY_HIGH => t('High priority')
            );
        }

        return self::$priorities;
    }

    public static function priorityExists(string $priority): bool
    {
        $priorities = self::getPriorities();
        return isset($priorities[$priority]);
    }

    public static function requirePriorityExists(string $priority): void
    {
        if (self::priorityExists($priority)) {
            return;
        }

        $priorities = self::getPriorities();

        throw new MessagingException(
            'Invalid message priority',
            sprintf(
                'Tried adding a message with an invalid priority [%s]. Valid priorities are: [%s].',
                ConvertHelper::var_dump($priority),
                implode(', ', array_keys($priorities))
            ),
            MessagingException::ERROR_INVALID_MESSAGE_PRIORITY
        );
    }

    /**
     * Adds a new message, and returns the message instance. Use this
     * to configure it further as needed.
     *
     * @param Application_User $toUser
     * @param string|StringableInterface $message
     * @param string $priority
     * @param Application_User|NULL $fromUser
     * @return Application_Messaging_Message
     */
    public function addMessage(Application_User $toUser, string|StringableInterface $message, string $priority = self::PRIORITY_NORMAL, ?Application_User $fromUser = null): Application_Messaging_Message
    {
        DBHelper::requireTransaction('Add a message');

        self::requirePriorityExists($priority);

        if ($fromUser === null) {
            $fromUser = Application::getUser();
        }

        if ($fromUser->getID() === $toUser->getID()) {
            throw new MessagingException(
                'Source and target users cannot be the same',
                sprintf(
                    'The to and from users are the same, [%s].',
                    $toUser->getID()
                ),
                MessagingException::ERROR_TO_AND_FROM_USERS_IDENTICAL
            );
        }

        $now = new DateTime();

        $message_id = (int)DBHelper::insert(
            "INSERT INTO
                `app_messaging`
            SET
                `from_user`=:from_user,
                `to_user`=:to_user,
                `message`=:message,
                `priority`=:priority,
                `date_sent`=:date_sent",
            array(
                self::COL_FROM_USER => $fromUser->getID(),
                self::COL_TO_USER => $toUser->getID(),
                self::COL_MESSAGE => toString($message),
                self::COL_PRIORITY => $priority,
                self::COL_DATE_SENT => $now->format('Y-m-d H:i:s')
            )
        );

        return $this->getByID($message_id);
    }

    public static function getPriorityLabel(string $priority): string
    {
        self::requirePriorityExists($priority);
        $priorities = self::getPriorities();
        return $priorities[$priority];
    }

    public function getRecordClassName(): string
    {
        return Application_Messaging_Message::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return MessagingFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return MessagingFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_DATE_SENT;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_MESSAGE => t('Message text')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE_NAME;
    }

    public function getCollectionLabel(): string
    {
        return t('Application messages');
    }

    public function getRecordLabel(): string
    {
        return t('Application message');
    }
}
