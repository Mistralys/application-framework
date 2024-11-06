<?php

declare(strict_types=1);

use Application\Media\Collection\MediaCollection;
use Application\NewsCentral\NewsCollection;
use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

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

    /**
     * Categories are not sorted by default, to allow setting
     * a specific order by registering them in the desired sequence.
     */
    public function test_category_getAliases() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Get Aliases');

        $expected = array(
            MediaCollection::RECENT_ITEMS_CATEGORY,
            NewsCollection::RECENT_ITEMS_CATEGORY
        );

        $this->assertEquals($expected, $this->user->getRecent()->getCategoryAliases());
    }

    public function test_category_getByAlias() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Get by alias');

        $recent = $this->user->getRecent();

        $category = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);

        $this->assertNotNull($category);
        $this->assertEquals(NewsCollection::RECENT_ITEMS_CATEGORY, $category->getAlias());
    }

    public function test_category_aliasExists() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Categories: Alias exists');

        $recent = $this->user->getRecent();

        $this->assertTrue($recent->categoryAliasExists(NewsCollection::RECENT_ITEMS_CATEGORY));
        $this->assertFalse($recent->categoryAliasExists('lopos'));
    }

    public function test_entry_add() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Add');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);
        $id = $this->createEntryID();

        $added = $foo->addEntry($id, 'An entry for news', 'https://mistralys.com');

        $this->assertInstanceOf(Application_User_Recent_Entry::class, $added);
    }

    public function test_entry_getByID() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: Get by ID');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);
        $id = $this->createEntryID();

        $foo->addEntry($id, 'An entry for news', 'https://mistralys.com');

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
        $category = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);

        $entries = $category->getEntries();

        $this->assertCount(0, $entries);

        $category->addEntry($this->createEntryID(), 'An entry for news', 'https://mistralys.com');
        $category->addEntry($this->createEntryID(), 'Another entry for news', 'https://mistralys.com');

        $entries = $category->getEntries();

        $this->assertCount(2, $entries);
    }

    public function test_entry_idExists() : void
    {
        if($this->skipTests()) {
            return;
        }

        $this->logHeader('Entries: ID exists');

        $recent = $this->user->getRecent();
        $foo = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);

        $id = $this->createEntryID();

        $this->assertFalse($foo->entryIDExists($id));

        $foo->addEntry($id, 'An entry for news', 'https://mistralys.com');

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
        $foo = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY);

        $id = $this->createEntryID();

        $foo->addEntry($id, 'An entry for news', 'https://mistralys.com');

        $user->clearCache();
        $recent = $user->getRecent();

        $entry = $recent->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY)->getEntryByID($id);

        $this->assertInstanceOf(Application_User_Recent_Entry::class, $entry);
    }

    public function test_emptyPinnedNotes() : void
    {
        if($this->skipTests()) {
            return;
        }

        $user = $this->user;
        $recent = $this->user->getRecent();

        $this->assertEmpty($recent->getPinnedNoteIDs());
    }

    public function test_pinNote() : void
    {
        if($this->skipTests()) {
            return;
        }

        $user = $this->user;
        $recent = $user->getRecent();
        $notepad = $user->getNotepad();

        $note = $notepad->addNote('Content');

        $recent->pinNote($note);

        $this->assertCount(1, $recent->getPinnedNoteIDs());
    }

    public function test_unpinNote() : void
    {
        if($this->skipTests()) {
            return;
        }

        $user = $this->user;
        $recent = $user->getRecent();
        $notepad = $user->getNotepad();

        $note = $notepad->addNote('Content');

        $recent->pinNote($note);
        $recent->unpinNote($note);

        $this->assertCount(0, $recent->getPinnedNoteIDs());
    }

    public function test_noteIsPinned() : void
    {
        if($this->skipTests()) {
            return;
        }

        $user = $this->user;
        $recent = $user->getRecent();
        $notepad = $user->getNotepad();

        $note = $notepad->addNote('Content');

        $recent->pinNote($note);

        $this->assertTrue($note->isPinned());

        $recent->unpinNote($note);

        $this->assertFalse($note->isPinned());
    }

    private function skipTests() : bool
    {
        if($this->user instanceof TestDriver_User)
        {
            return false;
        }

        $this->markTestSkipped('Not in framework testsuite environment.');
    }
}
