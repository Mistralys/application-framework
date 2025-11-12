<?php

declare(strict_types=1);

namespace Application\Revisionable\Changelog;

use Application;
use Application\Interfaces\ChangelogableInterface;
use Application_Changelog;
use Application_User;

/**
 * @see ChangelogableInterface
 * @see \Application_RevisionableStateless
 */
trait RevisionableChangelogTrait
{
    protected int $changelogStringMaxLength = 40;

    /**
     * Formats a string for use in changelog text placeholders.
     * Cuts them to a specific size if too long, and converts
     * all special characters to HTML entities.
     *
     * @param string $string
     * @param integer $maxLength If set to 0, use the global length setting, see {@link $changelogStringMaxLength}.
     * @return string String with HTML code
     */
    protected function formatForChangelog(string $string, int $maxLength=0) : string
    {
        if($maxLength < 1) {
            $maxLength = $this->changelogStringMaxLength;
        }

        if(mb_strlen($string) > $maxLength)
        {
            $result = mb_substr($string, 0, $maxLength);
            $result = '<b>'.htmlentities($result).'</b>';
            $result .= ' <span class="muted">[...]</span>';
        }
        else
        {
            $result = '<b>'.htmlentities($string).'</b>';
        }

        return $result;
    }

    /**
     * Retrieves the current changelog queue of the revisionable
     * item. This is an indexed array with the following structure:
     *
     * <pre>
     * array(
     *     array(
     *         'type' => 'changelog_type',
     *         'data' => [mixed]
     *     ),
     *     ...
     * )
     *
     * @see ChangelogableInterface::getChangelogQueue()
     */
    public function getChangelogQueue() : array
    {
        return $this->changelogQueue;
    }

    /**
     * Retrieves a list of all the changelog operation types
     * that have been queued until now.
     *
     * @return string[]
     */
    public function getChangelogQueueTypes() : array
    {
        $result = array();

        foreach($this->changelogQueue as $entry)
        {
            $result[] = $entry['type'];
        }

        return $result;
    }

    public function getChangelogOwner() : Application_User
    {
        return Application::createUser($this->getRevisionAuthorID());
    }

    /**
     * Retrieves the changelog for the current revision.
     * @return Application_Changelog
     */
    public function getChangelog() : Application_Changelog
    {
        $changelog = $this->revisions->getPrivateKey('changelog');

        if($changelog instanceof Application_Changelog) {
            return $changelog;
        }

        $changelog = new Application_Changelog($this);

        $changelog->onQueueCommitted(function () {
            $this->clearChangelogQueue();
        });

        $this->revisions->setPrivateKey('changelog', $changelog);

        return $changelog;
    }

    /**
     * Counts the number of changelog entries in the current revision.
     * @return integer
     */
    public function countChangelogEntries() : int
    {
        return $this->getChangelog()->countEntries();
    }

    protected array $changelogQueue = array();

    /**
     * Adds a changelog entry to the pending queue. Nothing is
     * actually modified yet, it is just added internally to be
     * committed later if the user wishes to commit his changes.
     *
     * @param string $type
     * @param mixed $data The data to store (JSON encoded automatically)
     */
    protected function enqueueChangelog(string $type, $data = null) : void
    {
        if (!$this->isChangelogEnabled()) {
            return;
        }

        $this->log('Changelog | '.$type);

        $this->changelogQueue[] = array(
            'type' => $type,
            'data' => $data
        );
    }

    /**
     * Commits the changelog queue. Extend this in your class if it
     * needs a specific implementation.
     */
    protected function commitChangelog() : void
    {
        $this->getChangelog()->commitQueue();
    }

    /**
     * Whether changelog entries should be added for this
     * product type instance.
     *
     * @var boolean
     * @see self::enableChangelog()
     * @see self::disableChangelog()
     */
    protected bool $changelogEnabled = true;

    /**
     * Enables the changelog again after disabling it
     * using the {@see self::disableChangelog()} method.
     *
     * @see self::disableChangelog()
     * @see self::addChangelog()
     * @return $this
     */
    public function enableChangelog() : self
    {
        return $this->setChangelogEnabled();
    }

    /**
     * Disables the changelog. This is useful in cases where some
     * operations should not be added to the changelog.
     *
     * @see self::enableChangelog()
     * @see self::addChangelog()
     * @return $this
     */
    public function disableChangelog() : self
    {
        return $this->setChangelogEnabled(false);
    }

    /**
     * Enables or disables the changelog.
     * @param boolean $enabled
     * @return $this
     */
    public function setChangelogEnabled(bool $enabled=true) : self
    {
        if($enabled !== true) {
            $enabled = false;
        }

        if($this->changelogEnabled === $enabled) {
            return $this;
        }

        if($enabled !== true) {
            $this->log('Changelog | Disabling the changelog. New entries will be ignored.');
        } else {
            $this->log('Changelog | Enabling the changelog.');
        }

        $this->changelogEnabled = $enabled;

        return $this;
    }

    /**
     * Checks whether the changelog is currently enabled.
     * @return boolean
     */
    public function isChangelogEnabled() : bool
    {
        return $this->changelogEnabled;
    }

    public function clearChangelogQueue() : void
    {
        $this->changelogQueue = array();
    }
}
