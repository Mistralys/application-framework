<?php
/**
 * @package Media
 * @subpackage Writer
 */

declare(strict_types=1);

namespace Application\Media\Writer;

use Application\Application;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application_User;
use AppUtils\ClassHelper;
use AppUtils\FileHelper\FileInfo;
use DBHelper;
use Throwable;

/**
 * The single namespaced write path for media documents.
 *
 * Every mutator requires an active DB transaction. File writes and record
 * deletion are not transactional, so this class provides its own failure
 * cleanup: a `create()` that fails after writing the target file removes it,
 * and a `replaceFile()` that fails after overwriting the stored file
 * restores the previous bytes from a backup copy. The residual window after
 * `replaceFile()` has returned (e.g. a failing transaction commit) is
 * accepted and documented in `Media/API/README.md`.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaDocumentWriter
{
    private MediaFileValidator $validator;

    public function __construct(?MediaFileValidator $validator=null)
    {
        $this->validator = $validator ?? new MediaFileValidator();
    }

    /**
     * Creates a new media document from the given content.
     *
     * @param MediaFileContent $content
     * @param string $name
     * @param Application_User $author
     * @param string $description
     * @param string $keywords
     * @return MediaRecord
     * @throws MediaWriterException
     */
    public function create(MediaFileContent $content, string $name, Application_User $author, string $description='', string $keywords='') : MediaRecord
    {
        $this->requireTransaction();

        $typeID = $this->validator->validate($content);

        $record = ClassHelper::requireObjectInstanceOf(
            MediaRecord::class,
            AppFactory::createMediaCollection()->createNewRecord(array(
                MediaCollection::COL_NAME => $name,
                MediaCollection::COL_USER_ID => $author->getID(),
                MediaCollection::COL_EXTENSION => $content->getExtension(),
                MediaCollection::COL_TYPE => $typeID,
                MediaCollection::COL_SIZE => $content->getSize(),
                MediaCollection::COL_DESCRIPTION => $description,
                MediaCollection::COL_KEYWORDS => $keywords,
            ))
        );

        $temp = $content->writeTempFile();
        $targetWritten = false;

        try
        {
            $record->getMediaDocument()->setSourceFile($temp);
            $targetWritten = true;

            $this->verifyWrittenFile($record, $content);
        }
        catch(Throwable $e)
        {
            if($targetWritten) {
                $this->deleteTargetFile($record);
            }

            throw $e;
        }
        finally
        {
            $temp->delete();
        }

        return $record;
    }

    /**
     * Replaces the file content of an existing media document. The
     * extension must match the record's current extension.
     *
     * Order of operations: the new size is stored on the record first (a DB
     * failure here never touches the file), then the current file is backed
     * up, then overwritten; a failure after the overwrite restores the
     * backup before rethrowing, and the backup is always removed afterward.
     *
     * @param MediaRecord $record
     * @param MediaFileContent $content
     * @return void
     * @throws MediaWriterException
     */
    public function replaceFile(MediaRecord $record, MediaFileContent $content) : void
    {
        $this->requireTransaction();

        $currentExtension = $record->getRecordStringKey(MediaCollection::COL_EXTENSION);

        if($content->getExtension() !== $currentExtension) {
            throw new MediaWriterException(
                'Cannot change the file extension of an existing media document.',
                sprintf(
                    'The record\'s current extension is [%s], the new content\'s extension is [%s].',
                    $currentExtension,
                    $content->getExtension()
                ),
                MediaWriterException::VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED
            );
        }

        $this->validator->validate($content, $record->getRecordStringKey(MediaCollection::COL_TYPE));

        $record->setRecordKey(MediaCollection::COL_SIZE, $content->getSize());
        $record->save();

        $targetFile = FileInfo::factory($record->getMediaDocument()->getPath());
        $backup = FileInfo::factory(Application::getTempFile(null, $currentExtension));

        $targetFile->copyTo($backup->getPath());

        try
        {
            $targetFile->putContents($content->getContent());

            $this->verifyWrittenFile($record, $content);
        }
        catch(Throwable $e)
        {
            $backup->copyTo($targetFile->getPath());

            throw $e;
        }
        finally
        {
            $backup->delete();
        }

        AppFactory::createMedia()->clearCollection();
    }

    /**
     * Changes a media document's metadata. A `null` argument leaves the
     * corresponding field unchanged.
     *
     * @param MediaRecord $record
     * @param string|NULL $name
     * @param string|NULL $description
     * @param string|NULL $keywords
     * @return bool Whether anything was actually changed.
     */
    public function updateMetadata(MediaRecord $record, ?string $name, ?string $description, ?string $keywords) : bool
    {
        $this->requireTransaction();

        $changed = false;

        if($name !== null && $record->setRecordKey(MediaCollection::COL_NAME, $name)) {
            $changed = true;
        }

        if($description !== null && $record->setRecordKey(MediaCollection::COL_DESCRIPTION, $description)) {
            $changed = true;
        }

        if($keywords !== null && $record->setRecordKey(MediaCollection::COL_KEYWORDS, $keywords)) {
            $changed = true;
        }

        if($changed) {
            $record->save();
        }

        return $changed;
    }

    public function delete(MediaRecord $record) : void
    {
        $this->requireTransaction();

        AppFactory::createMediaCollection()->deleteRecord($record);
    }

    /**
     * The named post-write step of {@see self::create()} and
     * {@see self::replaceFile()}: verifies that the file actually stored on
     * disk exists and matches the expected size. Catches a silently
     * truncated copy (e.g. a full disk), which a plain `copy()`/file write
     * does not report on its own.
     *
     * `protected` and non-`final` so it also serves as the writer's test
     * seam for the post-write failure cases, which it needs regardless of
     * testing: production code calling this class benefits from the same
     * guarantee.
     *
     * @param MediaRecord $record
     * @param MediaFileContent $content
     * @return void
     * @throws MediaWriterException {@see MediaWriterException::ERROR_FILE_WRITE_FAILED}
     */
    protected function verifyWrittenFile(MediaRecord $record, MediaFileContent $content) : void
    {
        $path = $record->getMediaDocument()->getPath();

        if(!is_file($path) || filesize($path) !== $content->getSize()) {
            throw new MediaWriterException(
                'The media file could not be verified after writing it.',
                sprintf(
                    'Expected a file of [%d] byte(s) at [%s].',
                    $content->getSize(),
                    $path
                ),
                MediaWriterException::ERROR_FILE_WRITE_FAILED
            );
        }
    }

    private function deleteTargetFile(MediaRecord $record) : void
    {
        $path = $record->getMediaDocument()->getPath();

        if(is_file($path)) {
            FileInfo::factory($path)->delete();
        }
    }

    /**
     * @return void
     * @throws MediaWriterException {@see MediaWriterException::ERROR_NO_TRANSACTION}
     */
    private function requireTransaction() : void
    {
        if(DBHelper::isTransactionStarted()) {
            return;
        }

        throw new MediaWriterException(
            'A database transaction is required for this operation.',
            'Media write operations modify both database records and files, and must run within an active transaction.',
            MediaWriterException::ERROR_NO_TRANSACTION
        );
    }
}
