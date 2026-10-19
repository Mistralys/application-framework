<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Writer;

use Application\Application;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;
use TestDriver\Media\FailingVerifyMediaDocumentWriter;

/**
 * Covers {@see MediaDocumentWriter}: `create()`'s validate-write-verify
 * sequence and post-write cleanup, `replaceFile()`'s size-first/backup-and-
 * restore order, `updateMetadata()` and `delete()`.
 */
final class MediaDocumentWriterTest extends MediaTestCase
{
    private static string $pngBytes;
    private static string $jpgBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $folder = __DIR__.'/../../../files/Media';

        self::$pngBytes = (string)file_get_contents($folder.'/example-image.png');
        self::$jpgBytes = (string)file_get_contents($folder.'/example-image.jpg');
    }

    private function createPNGContent(string $fileName='example.png', ?string $bytes=null) : MediaFileContent
    {
        return MediaFileContent::fromBinary($fileName, $bytes ?? self::$pngBytes);
    }

    // region: create()

    public function test_createWritesRecordAndFile() : void
    {
        $writer = new MediaDocumentWriter();

        $record = $writer->create(
            $this->createPNGContent(),
            'My Image',
            Application::getUser(),
            'A description',
            'keyword1, keyword2'
        );

        $this->testMedia[] = $record->getMediaDocument();

        $this->assertSame('My Image', $record->getRecordStringKey(MediaCollection::COL_NAME));
        $this->assertSame('Image', $record->getRecordStringKey(MediaCollection::COL_TYPE));
        $this->assertSame('png', $record->getRecordStringKey(MediaCollection::COL_EXTENSION));
        $this->assertSame(strlen(self::$pngBytes), $record->getRecordIntKey(MediaCollection::COL_SIZE));
        $this->assertSame('A description', $record->getRecordStringKey(MediaCollection::COL_DESCRIPTION));
        $this->assertSame('keyword1, keyword2', $record->getRecordStringKey(MediaCollection::COL_KEYWORDS));
        $this->assertSame(Application::getUser()->getID(), $record->getAuthorID());

        $path = $record->getMediaDocument()->getPath();
        $this->assertFileExists($path);
        $this->assertSame(strlen(self::$pngBytes), filesize($path));
    }

    public function test_createDeletesTempFileAfterward() : void
    {
        $tempFolder = Application::getTempFolder();
        $before = glob($tempFolder.'/*') ?: array();

        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'My Image', Application::getUser());

        $this->testMedia[] = $record->getMediaDocument();

        $after = glob($tempFolder.'/*') ?: array();

        $this->assertSame($before, $after, 'The temp file used to write the content must be removed afterward.');
    }

    public function test_createRejectsInvalidContent() : void
    {
        $writer = new MediaDocumentWriter();

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_CONTENT_MISMATCH);

        $writer->create(
            MediaFileContent::fromBinary('fake.png', 'not actually a png'),
            'My Image',
            Application::getUser()
        );
    }

    public function test_createFailureAfterWriteDeletesTargetFile() : void
    {
        $writer = new FailingVerifyMediaDocumentWriter();
        $name = 'My Failing Image '.$this->getTestCounter();

        try
        {
            $writer->create($this->createPNGContent(), $name, Application::getUser());
            $this->fail('Expected a MediaWriterException to be thrown.');
        }
        catch(MediaWriterException $e)
        {
            $this->assertSame(MediaWriterException::ERROR_FILE_WRITE_FAILED, $e->getCode());
        }

        $id = DBHelper::fetchKeyInt(
            MediaCollection::PRIMARY_NAME,
            "SELECT ".MediaCollection::PRIMARY_NAME.
            " FROM ".MediaCollection::TABLE_NAME.
            " WHERE ".MediaCollection::COL_NAME."=:name".
            " ORDER BY ".MediaCollection::PRIMARY_NAME." DESC LIMIT 1",
            array(':name' => $name)
        );

        $this->assertGreaterThan(0, $id, 'The record must still have been created.');

        $document = AppFactory::createMediaCollection()->getByID($id)->getMediaDocument();

        $this->assertFileDoesNotExist($document->getPath(), 'The target file must be deleted after a post-write verification failure.');
    }

    // endregion

    // region: replaceFile()

    public function test_replaceFileRequiresMatchingExtension() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'Original', Application::getUser());
        $this->testMedia[] = $record->getMediaDocument();

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED);

        $writer->replaceFile($record, MediaFileContent::fromBinary('new.jpg', self::$jpgBytes));
    }

    public function test_replaceFileWritesNewContentAndSize() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'Original', Application::getUser());
        $this->testMedia[] = $record->getMediaDocument();

        // Valid PNG content (per getimagesizefromstring()) with a different
        // size than the original, so the overwrite is actually observable.
        $newContent = $this->createPNGContent('new.png', self::$pngBytes.str_repeat('X', 50));

        $writer->replaceFile($record, $newContent);

        $this->assertSame($newContent->getSize(), $record->getRecordIntKey(MediaCollection::COL_SIZE));

        $path = $record->getMediaDocument()->getPath();
        $this->assertFileExists($path);
        $this->assertSame($newContent->getSize(), filesize($path));
        $this->assertSame($newContent->getContent(), (string)file_get_contents($path));
    }

    public function test_replaceFileFailureRestoresOriginalContent() : void
    {
        $writer = new FailingVerifyMediaDocumentWriter();
        $record = (new MediaDocumentWriter())->create($this->createPNGContent(), 'Original', Application::getUser());
        $this->testMedia[] = $record->getMediaDocument();

        $path = $record->getMediaDocument()->getPath();
        $originalContent = (string)file_get_contents($path);
        $originalSize = $record->getRecordIntKey(MediaCollection::COL_SIZE);

        $newContent = $this->createPNGContent('new.png', self::$pngBytes.str_repeat('X', 50));

        try
        {
            $writer->replaceFile($record, $newContent);
            $this->fail('Expected a MediaWriterException to be thrown.');
        }
        catch(MediaWriterException $e)
        {
            $this->assertSame(MediaWriterException::ERROR_FILE_WRITE_FAILED, $e->getCode());
        }

        $this->assertSame($originalContent, (string)file_get_contents($path), 'The original file bytes must be restored after a failed replace.');

        // NOTE: the DB size column was already updated to the new size before
        // the file write was attempted (by design, so a DB failure never
        // touches the file); only the transaction rollback restores it. The
        // writer's own guarantee is limited to the file bytes.
        $this->assertNotSame($originalSize, $record->getRecordIntKey(MediaCollection::COL_SIZE));
    }

    public function test_replaceFileDeletesBackupFileAfterward() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'Original', Application::getUser());
        $this->testMedia[] = $record->getMediaDocument();

        $tempFolder = Application::getTempFolder();
        $before = glob($tempFolder.'/*') ?: array();

        $writer->replaceFile($record, $this->createPNGContent('new.png', self::$pngBytes.str_repeat('X', 50)));

        $after = glob($tempFolder.'/*') ?: array();

        $this->assertSame($before, $after, 'The backup temp file must be removed afterward.');
    }

    // endregion

    // region: updateMetadata()

    public function test_updateMetadataChangesFields() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'Original', Application::getUser(), 'Old description', 'old');
        $this->testMedia[] = $record->getMediaDocument();

        $changed = $writer->updateMetadata($record, 'New name', 'New description', 'new keywords');

        $this->assertTrue($changed);
        $this->assertSame('New name', $record->getRecordStringKey(MediaCollection::COL_NAME));
        $this->assertSame('New description', $record->getRecordStringKey(MediaCollection::COL_DESCRIPTION));
        $this->assertSame('new keywords', $record->getRecordStringKey(MediaCollection::COL_KEYWORDS));
    }

    public function test_updateMetadataWithAllNullsReportsNoChange() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'Original', Application::getUser());
        $this->testMedia[] = $record->getMediaDocument();

        $changed = $writer->updateMetadata($record, null, null, null);

        $this->assertFalse($changed);
        $this->assertSame('Original', $record->getRecordStringKey(MediaCollection::COL_NAME));
    }

    // endregion

    // region: delete()

    public function test_deleteRemovesRecordAndFile() : void
    {
        $writer = new MediaDocumentWriter();
        $record = $writer->create($this->createPNGContent(), 'To Delete', Application::getUser());
        $document = $record->getMediaDocument();
        $path = $document->getPath();

        $this->assertFileExists($path);

        $writer->delete($record);

        $this->assertFileDoesNotExist($path);
        $this->assertFalse(AppFactory::createMedia()->idExists($record->getID()));
    }

    // endregion
}
