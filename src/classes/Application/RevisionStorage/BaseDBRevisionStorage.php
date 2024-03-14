<?php
/**
 * File containing the {@link BaseDBRevisionStorage} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see BaseDBRevisionStorage
 */

declare(strict_types=1);

namespace Application\RevisionStorage;

use Application_RevisionStorage;

/**
 * Base utility class for database-based revision storage.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseDBRevisionStorage extends Application_RevisionStorage
{
    public function getTypeID(): string
    {
        return 'DB';
    }
}
