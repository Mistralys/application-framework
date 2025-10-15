<?php
/**
 * @package API
 * @subpackage Versioning
 */

declare(strict_types=1);

namespace Application\API\Versioning;

use AppUtils\ArrayDataCollection;

/**
 * Trait used to implement {@see VersionedAPIInterface} in an API method,
 * and add version handling using separate version classes.
 *
 * For more documentation, see {@see VersionedAPIInterface}.
 *
 * @package API
 * @subpackage Versioning
 * @see VersionedAPIInterface
 */
trait VersionedAPITrait
{
    private ?VersionCollection $versionCollection = null;

    final public function getVersionCollection() : VersionCollection
    {
        if(!isset($this->versionCollection)) {
            $this->versionCollection = new VersionCollection($this);
        }

        return $this->versionCollection;
    }

    final public function getVersions() : array
    {
        return $this->getVersionCollection()->getIDs();
    }

    final public function getChangelog() : array
    {
        $changes = array();

        foreach($this->getVersionCollection()->getAll() as $version) {
            $changes[$version->getVersion()] = $version->getChangelog();
        }

        return $changes;
    }

    final protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->_collectResponseData($response, $this->getVersionCollection()->getByID($version));
    }

    abstract protected function _collectResponseData(ArrayDataCollection $response, APIVersionInterface $version) : void;
}
