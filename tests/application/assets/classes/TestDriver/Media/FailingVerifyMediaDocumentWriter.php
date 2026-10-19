<?php
/**
 * @package TestDriver
 * @subpackage Media
 */

declare(strict_types=1);

namespace TestDriver\Media;

use Application\Media\Collection\MediaRecord;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;

/**
 * Test-local subclass of {@see MediaDocumentWriter} that calls the real
 * {@see MediaDocumentWriter::verifyWrittenFile()} and then forces a failure,
 * to drive the writer's post-write cleanup branch (`create()`) and restore
 * branch (`replaceFile()`) — the seam the protected method exists for.
 *
 * @package TestDriver
 * @subpackage Media
 */
class FailingVerifyMediaDocumentWriter extends MediaDocumentWriter
{
    protected function verifyWrittenFile(MediaRecord $record, MediaFileContent $content) : void
    {
        parent::verifyWrittenFile($record, $content);

        throw new MediaWriterException(
            'Forced failure for testing.',
            'Simulated post-write failure, to exercise the writer\'s cleanup/restore path.',
            MediaWriterException::ERROR_FILE_WRITE_FAILED
        );
    }
}
