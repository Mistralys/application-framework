<?php

declare(strict_types=1);

use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;

final class Installer_SystemUsersTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->disableLogging();
    }

    /**
     * The system users must be created if they do not
     * exist in the database.
     */
    public function test_createUsers() : void
    {
        $this->startTest('Create system users if they do not exist');

        // Ensure there are no users in the DB to begin with
        $this->deleteSystemUsers();

        // Run the system users task
        $this->runTask();

        // Check that the users are now present
        $users = Application_Driver::createUsers();
        $ids = Application::getSystemUserIDs();

        foreach ($ids as $id)
        {
            $found = DBHelper::createFetchKey('user_id', 'known_users')
            ->whereValue('user_id', $id)
            ->fetchInt();

            $this->assertSame($id, $found);
        }
    }

    public function test_updateUsers() : void
    {
        $this->startTest('Update users with internal data');

        $this->deleteSystemUsers();

        // Insert the system user entry with completely different
        // data than the one it should have.
        DBHelper::insertDynamic(
            'known_users',
            array(
                'user_id' => Application::USER_ID_SYSTEM,
                'email' => 'test'.$this->getTestCounter().'@testsuite.system',
                'firstname' => 'test'.$this->getTestCounter(),
                'lastname' => 'test'.$this->getTestCounter(),
                'foreign_id' => 'test'.$this->getTestCounter()
            )
        );

        // The task must detect the existing user record in the
        // database, and overwrite the columns with the data from
        // the dynamically created user instance.
        $this->runTask();

        $systemUser = Application::createSystemUser();
        $updatedData = DBHelper::fetchData(
            'known_users',
            array(
                'user_id' => Application::USER_ID_SYSTEM
            )
        );

        $this->assertEquals($systemUser->getEmail(), $updatedData['email']);
        $this->assertEquals($systemUser->getFirstname(), $updatedData['firstname']);
        $this->assertEquals($systemUser->getLastname(), $updatedData['lastname']);
        $this->assertEquals($systemUser->getForeignID(), $updatedData['foreign_id']);
    }

    private function runTask() : void
    {
        $installer = Application::createInstaller();
        $result = $installer->getTaskByID('InitSystemUsers')->process();

        $this->assertTrue($result->isValid());
    }

    private function deleteSystemUsers() : void
    {
        Application::log('Deleting all system users.');

        $ids = Application::getSystemUserIDs();
        $users = Application_Driver::createUsers();

        // Delete the system users from the database
        foreach ($ids as $id)
        {
            if($users->idExists($id))
            {
                $users->deleteRecord($users->getByID($id));
            }
        }
    }
}
