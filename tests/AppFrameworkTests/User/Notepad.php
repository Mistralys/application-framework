<?php

declare(strict_types=1);

use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

/**
 * These tests can only be run from the application's
 * own tests suite, because they require the testsuite
 * user class to be used.
 *
 * @see TestDriver_User
 */
final class User_NotepadTest extends UserTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();

        $this->user->getNotepad()->clearNotes();
    }

    protected function tearDown() : void
    {
        $this->clearTransaction();
    }

    public function test_addNote() : void
    {
        $this->startTest('Add new note');

        $notepad = $this->user->getNotepad();

        $note = $notepad->addNote('Content here', 'Title');

        $this->assertInstanceOf(Application_User_Notepad_Note::class, $note);
        $this->assertEquals('Content here', $note->getContent());
        $this->assertEquals('Title', $note->getTitle());
    }

    public function test_countNotes() : void
    {
        $this->startTest('Add new note');

        $notepad = $this->user->getNotepad();

        $this->assertSame(0, $notepad->countNotes());

        $notepad->addNote('Content here', 'Title');

        $this->assertSame(1, $notepad->countNotes());
    }

    public function test_deleteNote() : void
    {
        $this->startTest('Add new note');

        $notepad = $this->user->getNotepad();

        $note = $notepad->addNote('Content here', 'Title');

        $notepad->deleteNote($note);

        $this->assertEmpty($notepad->getAll());
    }

    public function test_getAll() : void
    {
        $this->startTest('Get all notes');

        $notepad = $this->user->getNotepad();

        $notepad->addNote('First', 'First');
        $notepad->addNote('Second', 'Second');

        $all = $notepad->getAll();

        $this->assertCount(2, $all);

        $this->assertEquals('Second', $all[0]->getTitle(), 'Second entry must be first, since it is more recent.');
    }

    public function test_markdownRendering() : void
    {
        $this->startTest('Render markdown content');

        $notepad = $this->user->getNotepad();

        $note = $notepad->addNote('Text with **bold** style', 'Title');

        $this->assertEquals('<p>Text with <strong>bold</strong> style</p>', $note->renderContent());
    }

    public function test_specialChars() : void
    {
        $this->startTest('Render markdown content');

        $notepad = $this->user->getNotepad();

        $note = $notepad->addNote("<Text with '", "<Title with '");

        $this->assertEquals("<Text with '", $note->getContent());
        $this->assertEquals("<p>&lt;Text with '</p>", $note->renderContent());
    }
}
