<?php
/**
 * @package Media
 * @subpackage Writer
 */

declare(strict_types=1);

namespace Application\Media\Writer;

use Application\Application;
use AppUtils\FileHelper\FileInfo;

/**
 * Immutable value object carrying file content and its file name, used
 * throughout the media writer services ({@see MediaFileValidator},
 * {@see MediaDocumentWriter}).
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileContent
{
    private string $fileName;
    private string $content;

    private function __construct(string $fileName, string $content)
    {
        self::requireValidFileName($fileName);

        $this->fileName = $fileName;
        $this->content = $content;
    }

    /**
     * Creates an instance from raw binary content and a file name.
     *
     * @param string $fileName
     * @param string $content
     * @return self
     * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
     */
    public static function fromBinary(string $fileName, string $content) : self
    {
        return new self($fileName, $content);
    }

    /**
     * Creates an instance by reading an existing file from disk.
     *
     * @param FileInfo $file
     * @return self
     * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
     */
    public static function fromFile(FileInfo $file) : self
    {
        return new self($file->getName(), $file->getContents());
    }

    /**
     * @param string $fileName
     * @return void
     * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
     */
    private static function requireValidFileName(string $fileName) : void
    {
        if($fileName === '') {
            throw new MediaWriterException(
                'Invalid media file name.',
                'The file name must not be empty.',
                MediaWriterException::VALIDATION_INVALID_FILE_NAME
            );
        }

        if(str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            throw new MediaWriterException(
                'Invalid media file name.',
                sprintf(
                    'The file name [%s] must not contain path separators.',
                    $fileName
                ),
                MediaWriterException::VALIDATION_INVALID_FILE_NAME
            );
        }

        if(pathinfo($fileName, PATHINFO_EXTENSION) === '') {
            throw new MediaWriterException(
                'Invalid media file name.',
                sprintf(
                    'The file name [%s] is missing a file extension.',
                    $fileName
                ),
                MediaWriterException::VALIDATION_INVALID_FILE_NAME
            );
        }
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }

    /**
     * The file name without its extension.
     * @return string
     */
    public function getBaseName() : string
    {
        return pathinfo($this->fileName, PATHINFO_FILENAME);
    }

    /**
     * The file extension, lowercased, without the leading dot.
     * @return string
     */
    public function getExtension() : string
    {
        return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getSize() : int
    {
        return strlen($this->content);
    }

    /**
     * Writes the content to a new temporary file and returns it. Used only
     * by {@see MediaDocumentWriter} for the `setSourceFile()` copy in a real
     * write; validation never calls this.
     *
     * @return FileInfo
     */
    public function writeTempFile() : FileInfo
    {
        $path = Application::getTempFile(null, $this->getExtension());

        return FileInfo::factory($path)->putContents($this->content);
    }
}
