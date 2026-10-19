<?php
/**
 * @package Media
 * @subpackage Writer
 */

declare(strict_types=1);

namespace Application\Media\Writer;

use Application\AppFactory;
use Application_Media_Document;
use AppUtils\ClassHelper;

/**
 * Stateless, in-memory validator for media file content. It is the single
 * validation path for media writes: {@see MediaDocumentWriter::create()} and
 * {@see MediaDocumentWriter::replaceFile()} call it, and the dry-run
 * branches of the Add and Update media API methods call the same
 * {@see self::validate()} directly, so a dry run cannot drift from the real
 * write.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileValidator
{
    /**
     * Validates the given file content, in order:
     *
     * 1. the extension resolves to a known media type
     * 2. the type matches `$requiredTypeID`, when one is given
     * 3. the content is non-empty
     * 4. the content is valid per the resolved type's `isContentValid()`,
     *    entirely in memory
     *
     * No temp file is ever created by this method.
     *
     * @param MediaFileContent $content
     * @param string|NULL $requiredTypeID When given, the content's resolved
     *        type must match this type ID exactly.
     * @return string The resolved media type ID (e.g. `Image`, `PDF`).
     * @throws MediaWriterException A `VALIDATION_*` code with a client-safe message.
     */
    public function validate(MediaFileContent $content, ?string $requiredTypeID=null) : string
    {
        $extension = $content->getExtension();

        $typeID = AppFactory::createMedia()->getTypeByExtension($extension);

        if($typeID === null) {
            throw new MediaWriterException(
                'Unsupported file extension.',
                sprintf(
                    'The extension [%s] is not handled by any registered media type.',
                    $extension
                ),
                MediaWriterException::VALIDATION_UNSUPPORTED_EXTENSION
            );
        }

        if($requiredTypeID !== null && $typeID !== $requiredTypeID) {
            throw new MediaWriterException(
                'File type mismatch.',
                sprintf(
                    'Expected media type [%s], but the extension [%s] resolves to type [%s].',
                    $requiredTypeID,
                    $extension,
                    $typeID
                ),
                MediaWriterException::VALIDATION_TYPE_MISMATCH
            );
        }

        if($content->getSize() === 0) {
            throw new MediaWriterException(
                'Empty file content.',
                sprintf(
                    'The content of file [%s] is empty.',
                    $content->getFileName()
                ),
                MediaWriterException::VALIDATION_EMPTY_FILE
            );
        }

        $class = ClassHelper::requireResolvedClass(Application_Media_Document::class.'_'.$typeID);

        if(!$class::isContentValid($content->getContent(), $extension)) {
            throw new MediaWriterException(
                'File content does not match its extension.',
                sprintf(
                    'The content of file [%s] is not a valid [%s] file matching the extension [%s].',
                    $content->getFileName(),
                    $typeID,
                    $extension
                ),
                MediaWriterException::VALIDATION_CONTENT_MISMATCH
            );
        }

        return $typeID;
    }
}
