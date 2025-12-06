<?php

declare(strict_types=1);

namespace AppFrameworkTests\Users;

use Application\AppFactory;
use Application_Users;
use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

final class CollectionTest extends UserTestCase
{
    public function test_createUserAutomaticallyHashesTheEmail() : void
    {
        $email = 'test-user@mistralys.com';

        $user = AppFactory::createUsers()->createNewUser(
            'test-user@mistralys.com',
            'Test',
            'User',
            'foreignID'
        );

        $this->assertSame(Application_Users::email2hash($email), $user->getEmailMD5());
    }

    public function test_changingEmailAddressUpdatesTheHash() : void
    {
        $collection = AppFactory::createUsers();
        $user = $collection->createNewUser(
            'test-user@mistralys.com',
            'Test',
            'User',
            'foreignID'
        );

        $newEmail = 'test-user-new@mistralys.com';

        $user->setEmail($newEmail);
        $user->save();

        // Force reload from database to ensure we get the updated value
        $collection->resetCollection();

        $reloadedUser = $collection->getByID($user->getID());

        $this->assertSame(Application_Users::email2hash($newEmail), $reloadedUser->getEmailMD5());
    }
}
