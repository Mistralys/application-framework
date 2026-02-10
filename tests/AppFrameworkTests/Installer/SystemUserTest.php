<?php

declare(strict_types=1);

namespace AppFrameworkTests\Installer;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Application;
use Application_Users;
use DBHelper;

final class SystemUserTest extends ApplicationTestCase
{
    // region: _Tests

    /**
     * The system users must be created if they do not
     * exist in the database.
     */
    public function test_createUsers(): void
    {
        $this->startTest('Create system users if they do not exist');

        // Run the system users task
        $this->runTask();

        // Check that the users are now present
        $ids = Application::getSystemUserIDs();

        foreach ($ids as $id) {
            $found = DBHelper::createFetchKey('user_id', 'known_users')
                ->whereValue('user_id', $id)
                ->fetchInt();

            $this->assertSame($id, $found);
        }
    }

    public function test_updateUsers(): void
    {
        $this->startTest('Update users with internal data');

        AppFactory::createUsers()->createNewUser(
            'test' . $this->getTestCounter() . '@testsuite.system',
            'test' . $this->getTestCounter(),
            'test' . $this->getTestCounter(),
            'test' . $this->getTestCounter(),
            Application::USER_ID_SYSTEM
        );

        $this->assertTrue(AppFactory::createUsers()->idExists(Application::USER_ID_SYSTEM));

        // The task must detect the existing user record in the
        // database, and overwrite the columns with the data from
        // the dynamically created user instance.
        $this->runTask();

        $systemUser = Application::createSystemUser();
        $updatedData = DBHelper::fetchData(
            Application_Users::TABLE_NAME,
            array(
                Application_Users::PRIMARY_NAME => Application::USER_ID_SYSTEM
            )
        );

        $this->assertEquals($systemUser->getEmail(), $updatedData['email']);
        $this->assertEquals($systemUser->getFirstname(), $updatedData['firstname']);
        $this->assertEquals($systemUser->getLastname(), $updatedData['lastname']);
        $this->assertEquals($systemUser->getForeignID(), $updatedData['foreign_id']);
    }

    // endregion

    // region: Support methods

    private function runTask(): void
    {
        $installer = Application::createInstaller();
        $result = $installer->getTaskByID('InitSystemUsers')->process();

        $this->assertTrue($result->isValid());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        // Ensure there are no users in the DB to begin with
        $this->deleteSystemUsers();
    }

    private function deleteSystemUsers(): void
    {
        Application::log('Deleting all system users.');

        $this->cleanUpTables(array(Application_Users::TABLE_NAME));

        AppFactory::createUsers()->resetCollection();
    }

    // endregion
}
