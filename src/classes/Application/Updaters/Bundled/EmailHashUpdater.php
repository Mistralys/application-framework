<?php
/**
 * @package Users
 * @subpackage Maintenance
 */

declare(strict_types=1);

namespace Application\Updaters\BaseUpdaters;

use Application\AppFactory;
use Application\Updaters\BaseUpdater;
use Application_Users;
use AppUtils\OutputBuffering;
use UI;

/**
 * Base abstract updater class that goes through all known users
 * and re-hashes their email addresses using the current hashing
 * implementation.
 *
 * Extend this class to create a concrete updater in the application.
 *
 * @package Users
 * @subpackage Maintenance
 */
class EmailHashUpdater extends BaseUpdater
{
    public const string UPDATER_ID = 'EmailHasher';
    public const string REQUEST_PARAM_CONFIRM = 'confirm';
    private Application_Users $collection;

    public function getID() : string
    {
        return self::UPDATER_ID;
    }

    public function start(): string
    {
        $this->collection = AppFactory::createUsers();

        if($this->request->getBool(self::REQUEST_PARAM_CONFIRM)) {
            return $this->processHashing();
        }

        OutputBuffering::start();

        ?>
        <p>
            Found <b><?php echo $this->collection->countRecords() ?></b> known users.
        </p>
        <p>
            Note: This can be done anytime without any adverse effects.
        </p>
        <p>
            <?php
            UI::button('Hash email addresses')
                ->link($this->buildURL(array(self::REQUEST_PARAM_CONFIRM => 'yes')))
                ->setIcon(UI::icon()->refresh())
                ->makePrimary()
                ->display();
            ?>
        </p>
        <?php

        return $this->renderPage(OutputBuffering::get());
    }

    private function processHashing() : string
    {
        $this->startTransaction();

        $updated = 0;
        foreach($this->collection->getAll() as $user) {
            if($user->updateEmailAddressHash()) {
                $updated++;
            }
        }

        if($updated === 0) {
            $message = 'Congratulations, all email addresses are already up to date.';
        } else {
            $message = sprintf(
                '%1$s email addresses have been updated successfully.',
                $updated
            );
        }

        $this->endTransaction();

        return $this->renderPage(
            $this->renderSuccessMessage($message)
        );
    }

    public function getLabel(): string
    {
        return 'Email Hasher';
    }

    public function getDescription() : string
    {
        return 'Goes through all known users and re-hashes their email addresses using the current hashing implementation.';
    }

    public function getValidVersions() : string
    {
        return '*';
    }
}
