<?php
/**
 * File containing the {@link Application_RevisionStorage_DB} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionStorage_DB
 */

/**
 * The base class for revision storage types
 * @see Application_RevisionStorage
 */
require_once 'Application/RevisionStorage.php';

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
}
