<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application\Countries\CountriesCollection;
use Application\Locales;
use Application\SourceFolders\Sources\APISourceFolders;

/**
 * Utility class used to register framework-internal API method folders.
 *
 * @package API
 * @subpackage Core
 */
class APIFoldersManager
{
    private APISourceFolders $folders;

    public function __construct(APISourceFolders $folders)
    {
        $this->folders = $folders;
    }

    public function register() : void
    {
        $this->folders
            ->addFolder(Locales::getAPIMethodsFolder())
            ->addFolder(CountriesCollection::getAPIMethodsFolder());
    }
}
