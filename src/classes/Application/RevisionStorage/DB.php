<?php
/**
 * File containing the {@link Application_RevisionStorage_DB} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionStorage_DB
 */

declare(strict_types=1);

use Application\Revisionable\RevisionableException;

/**
 * Base utility class for database-based revision storage.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_RevisionStorage_DB extends Application_RevisionStorage
{
    public function getTypeID() : string
    {
        return 'DB';
    }

    /**
     * Must be implemented if the revisionable is to allow copying
     * to another revisionable of the same type. The target class
     * has to extend the <code>Application_RevisionStorage_TYPE_CopyRevision</code>
     * class, where <code>TYPE</code> is the storage type ID, e.g. <code>DB</code>.
     *
     * @return class-string
     * @throws RevisionableException
     */
    abstract protected function getRevisionCopyClass() : string;
}
