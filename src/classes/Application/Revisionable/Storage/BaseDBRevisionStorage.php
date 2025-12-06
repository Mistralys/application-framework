<?php
/**
 * File containing the {@link \Application\Revisionable\Storage\BaseDBRevisionStorage} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see \Application\Revisionable\Storage\BaseDBRevisionStorage
 */

declare(strict_types=1);

namespace Application\Revisionable\Storage;

/**
 * Base utility class for database-based revision storage.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseDBRevisionStorage extends BaseRevisionStorage
{
    public function getTypeID(): string
    {
        return 'DB';
    }
}
