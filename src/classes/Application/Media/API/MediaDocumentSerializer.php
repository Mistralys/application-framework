<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\AppFactory;
use Application\Media\Collection\MediaRecord;
use Application\Media\ImageDocumentInterface;
use Application\Media\Writer\MediaFileContent;
use Application_Media_Document;
use Application_Media_Document_Image;
use DateTime;

/**
 * Serializes a {@see MediaRecord} (or, for a dry run, a not-yet-written
 * {@see MediaFileContent}) into the response shape declared by
 * {@see MediaAPIResponseInterface}.
 *
 * Stateless: every method is a pure static function of its arguments.
 *
 * @package Media
 * @subpackage API
 */
class MediaDocumentSerializer
{
    /**
     * Serializes an existing, persisted media document.
     *
     * @param MediaRecord $record
     * @param bool $includeContent Whether to include the base64-encoded file
     *        content under {@see MediaAPIResponseInterface::KEY_CONTENT}.
     * @return array<string, mixed>
     */
    public static function serialize(MediaRecord $record, bool $includeContent=false) : array
    {
        $document = $record->getMediaDocument();
        $typeID = $document->getTypeID();

        $data = array(
            MediaAPIResponseInterface::KEY_MEDIA_ID => $record->getID(),
            MediaAPIResponseInterface::KEY_NAME => $record->getLabel(),
            MediaAPIResponseInterface::KEY_MEDIA_TYPE => $typeID,
            MediaAPIResponseInterface::KEY_MEDIA_TYPE_LABEL => AppFactory::createMedia()->getTypeLabel($typeID),
            MediaAPIResponseInterface::KEY_EXTENSION => $document->getExtension(),
            MediaAPIResponseInterface::KEY_FILE_NAME => $document->getFilename(),
            MediaAPIResponseInterface::KEY_FILE_SIZE => $record->getFileSize(),
            MediaAPIResponseInterface::KEY_DATE_ADDED => $record->getDateAdded()->format(DateTime::ATOM),
            MediaAPIResponseInterface::KEY_AUTHOR_ID => $record->getAuthorID(),
            MediaAPIResponseInterface::KEY_DESCRIPTION => $record->getDescription(),
            MediaAPIResponseInterface::KEY_KEYWORDS => $record->getKeywords(),
            MediaAPIResponseInterface::KEY_IMAGE => self::serializeImageShape($document),
        );

        if($includeContent) {
            $data[MediaAPIResponseInterface::KEY_CONTENT] = $document->toBase64();
        }

        return $data;
    }

    /**
     * Builds the dry-run preview shape for a would-be media document that
     * has not been written to disk or the database. `mediaID` is always
     * `null`, since no record exists yet.
     *
     * @param MediaFileContent $content
     * @param string $typeID The resolved media type ID (e.g. `Image`, `PDF`).
     * @param string $name The resolved document name (already cut to the
     *        metadata piece's length limit by the caller).
     * @param string $description
     * @param string $keywords
     * @return array<string, mixed>
     */
    public static function serializePreview(
        MediaFileContent $content,
        string $typeID,
        string $name,
        string $description='',
        string $keywords=''
    ) : array
    {
        return array(
            MediaAPIResponseInterface::KEY_MEDIA_ID => null,
            MediaAPIResponseInterface::KEY_NAME => $name,
            MediaAPIResponseInterface::KEY_MEDIA_TYPE => $typeID,
            MediaAPIResponseInterface::KEY_MEDIA_TYPE_LABEL => AppFactory::createMedia()->getTypeLabel($typeID),
            MediaAPIResponseInterface::KEY_EXTENSION => $content->getExtension(),
            MediaAPIResponseInterface::KEY_FILE_NAME => $content->getFileName(),
            MediaAPIResponseInterface::KEY_FILE_SIZE => $content->getSize(),
            MediaAPIResponseInterface::KEY_DATE_ADDED => null,
            MediaAPIResponseInterface::KEY_AUTHOR_ID => null,
            MediaAPIResponseInterface::KEY_DESCRIPTION => $description,
            MediaAPIResponseInterface::KEY_KEYWORDS => $keywords,
            MediaAPIResponseInterface::KEY_IMAGE => null,
        );
    }

    /**
     * Builds a fixed, DB-free example of a serialized media document, for
     * use in the API documentation (`getExampleJSONResponse()`). Documentation
     * builds must not write media files, so this never touches the database
     * or the filesystem.
     *
     * @param bool $withImage Whether the example describes an image document
     *        (`true`) or a non-image document, e.g. a PDF (`false`).
     * @return array<string, mixed>
     */
    public static function buildExample(bool $withImage=true) : array
    {
        if($withImage) {
            return array(
                MediaAPIResponseInterface::KEY_MEDIA_ID => 142,
                MediaAPIResponseInterface::KEY_NAME => 'Autumn hero',
                MediaAPIResponseInterface::KEY_MEDIA_TYPE => 'Image',
                MediaAPIResponseInterface::KEY_MEDIA_TYPE_LABEL => 'Image',
                MediaAPIResponseInterface::KEY_EXTENSION => 'png',
                MediaAPIResponseInterface::KEY_FILE_NAME => 'autumn-hero.png',
                MediaAPIResponseInterface::KEY_FILE_SIZE => 204800,
                MediaAPIResponseInterface::KEY_DATE_ADDED => '2026-10-16T12:00:00+00:00',
                MediaAPIResponseInterface::KEY_AUTHOR_ID => 1,
                MediaAPIResponseInterface::KEY_DESCRIPTION => 'Autumn-themed hero image.',
                MediaAPIResponseInterface::KEY_KEYWORDS => 'autumn, hero, banner',
                MediaAPIResponseInterface::KEY_IMAGE => array(
                    MediaAPIResponseInterface::KEY_IMAGE_WIDTH => 1200,
                    MediaAPIResponseInterface::KEY_IMAGE_HEIGHT => 600,
                    MediaAPIResponseInterface::KEY_IMAGE_IS_VECTOR => false,
                    MediaAPIResponseInterface::KEY_IMAGE_IS_ANIMATED => false,
                ),
            );
        }

        return array(
            MediaAPIResponseInterface::KEY_MEDIA_ID => 143,
            MediaAPIResponseInterface::KEY_NAME => 'Terms and Conditions',
            MediaAPIResponseInterface::KEY_MEDIA_TYPE => 'PDF',
            MediaAPIResponseInterface::KEY_MEDIA_TYPE_LABEL => 'PDF document',
            MediaAPIResponseInterface::KEY_EXTENSION => 'pdf',
            MediaAPIResponseInterface::KEY_FILE_NAME => 'terms-and-conditions.pdf',
            MediaAPIResponseInterface::KEY_FILE_SIZE => 51200,
            MediaAPIResponseInterface::KEY_DATE_ADDED => '2026-10-16T12:00:00+00:00',
            MediaAPIResponseInterface::KEY_AUTHOR_ID => 1,
            MediaAPIResponseInterface::KEY_DESCRIPTION => '',
            MediaAPIResponseInterface::KEY_KEYWORDS => '',
            MediaAPIResponseInterface::KEY_IMAGE => null,
        );
    }

    /**
     * @param Application_Media_Document $document
     * @return array{width: int, height: int, isVector: bool, isAnimated: bool}|null
     *         `null` for a non-image document.
     */
    private static function serializeImageShape(Application_Media_Document $document) : ?array
    {
        if(!$document instanceof ImageDocumentInterface) {
            return null;
        }

        return array(
            MediaAPIResponseInterface::KEY_IMAGE_WIDTH => $document->getWidth(),
            MediaAPIResponseInterface::KEY_IMAGE_HEIGHT => $document->getHeight(),
            MediaAPIResponseInterface::KEY_IMAGE_IS_VECTOR => $document->isVector(),
            MediaAPIResponseInterface::KEY_IMAGE_IS_ANIMATED => $document instanceof Application_Media_Document_Image
                && $document->isAnimatedGIF(),
        );
    }
}
