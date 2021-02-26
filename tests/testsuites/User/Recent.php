<?php

declare(strict_types=1);

/**
 * These tests can only be run from the application's
 * own tests suite, because they require the testsuite
 * user class to be used.
 *
 * @see TestDriver_User
 */
final class User_RecentTest extends UserTestCase
{
    public function test_create(): void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Create');

        $recent = $this->user->getRecent();

        $this->assertInstanceOf(TestDriver_User_Recent::class, $recent);
    }

    public function test_category_getAll() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Get all');

        $recent = $this->user->getRecent();

        $categories = $recent->getCategories();

        $this->assertCount(2, $categories);
    }

    public function test_category_getAliases() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Get Aliases');

        $recent = $this->user->getRecent();

        $aliases = $recent->getCategoryAliases();

        // Categories get sorted by label, so bar must come before foo.
        $this->assertEquals(array('bar', 'foo'), $aliases);
    }

    public function test_category_getByAlias() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Get by alias');

        $recent = $this->user->getRecent();

        $category = $recent->getCategoryByAlias('foo');

        $this->assertNotNull($category);
        $this->assertEquals('foo', $category->getAlias());
        $this->assertEquals('Foo', $category->getLabel());
    }

    public function test_category_aliasExists() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Alias exists');

        $recent = $this->user->getRecent();

        $this->assertTrue($recent->categoryAliasExists('bar'));
        $this->assertFalse($recent->categoryAliasExists('lopos'));
    }

    public function test_entry_add() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Add');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias('foo');
        $id = $this->createEntryID();

        $added = $foo->addEntry($id, 'An entry for Foo', 'http://foo.com');

        $this->assertInstanceOf(Application_User_Recent_Entry::class, $added);
    }

    public function test_entry_getByID() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Get by ID');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias('foo');
        $id = $this->createEntryID();

        $foo->addEntry($id, 'An entry for Foo', 'http://foo.com');

        $entry = $foo->getEntryByID($id);

        $this->assertInstanceOf(Application_User_Recent_Entry::class, $entry);
    }

    public function test_entry_getAll() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Get all');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias('foo');

        $entries = $foo->getEntries();

        $this->assertCount(0, $entries);

        $foo->addEntry($this->createEntryID(), 'An entry for Foo', 'http://foo.com');
        $foo->addEntry($this->createEntryID(), 'Another entry for Foo', 'http://foo.com');

        $entries = $foo->getEntries();

        $this->assertCount(2, $entries);
    }

    public function test_entry_idExists() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: ID exists');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias('foo');

        $id = $this->createEntryID();

        $this->assertFalse($foo->entryIDExists($id));

        $foo->addEntry($id, 'An entry for Foo', 'http://foo.com');

        $this->assertTrue($foo->entryIDExists($id));
    }

    public function test_save() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Save');

        $user = $this->user;
        $recent = $user->getRecent();
        $foo = $recent->getCategoryByAlias('foo');

        $id = $this->createEntryID();

        $foo->addEntry($id, 'An entry for Foo', 'http://foo.com');

        $user->clearCache();
        $recent = $user->getRecent();

        $entry = $recent->getCategoryByAlias('foo')->getEntryByID($id);

        $this->assertInstanceOf(Application_User_Recent_Entry::class, $entry);
    }

    private function skipTests() : bool
    {
        if($this->user instanceof TestDriver_User)
        {
            return false;
        }

        $this->markTestSkipped('Not in framework testsuite environment.');
        return true;
    }
}
