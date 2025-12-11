<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application\OfflineEvents\RegisterAdminScreenFoldersEvent;
use Application_EventHandler_Event;
use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\ClassHelper;
use AppUtils\FileHelper\FolderInfo;

/**
 * Base class for offline event listeners that register admin screen folders.
 *
 * @package Application
 * @subpackage Admin
 */
abstract class BaseRegisterScreenFoldersListener extends Application_EventHandler_OfflineEvents_OfflineListener
{
    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
        $this->handleFoldersRegistration(
            ClassHelper::requireObjectInstanceOf(
                RegisterAdminScreenFoldersEvent::class,
                $event
            )
        );
    }

    protected function handleFoldersRegistration(RegisterAdminScreenFoldersEvent $event): void
    {
        foreach ($this->getAdminScreenFolders() as $folder) {
            $event->registerFolder($folder);
        }
    }

    /**
     * @return FolderInfo[]
     */
    abstract protected function getAdminScreenFolders() : array;
}
