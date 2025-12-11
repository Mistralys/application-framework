<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application\Admin\Index\AdminScreenIndexer;
use Application\CacheControl\BaseRegisterScreenFoldersListener;
use Application_EventHandler_Event;
use AppUtils\FileHelper\FolderInfo;

/**
 * This offline event is triggered when the admin screen indexer
 * builds the sitemap of all available admin screens. It allows
 * the application's components to register their own admin screen
 * folders.
 *
 * ## Usage
 *
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseRegisterScreenFoldersListener}.
 * 3. Implement the missing methods.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see AdminScreenIndexer
 */
class RegisterAdminScreenFoldersEvent extends Application_EventHandler_Event
{
    public const string EVENT_NAME = 'RegisterAdminScreenFolders';

    public function registerFolder(FolderInfo $folder): void
    {
        AdminScreenIndexer::registerFolder($folder);
    }
}
