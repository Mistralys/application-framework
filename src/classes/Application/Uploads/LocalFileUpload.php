<?php
/**
 * @package Application
 * @subpackage Uploads
 * @see \Application\Uploads\LocalFileUpload
 */

declare(strict_types=1);

namespace Application\Uploads;

use Application\Application;
use Application_Uploads_Upload;
use AppUtils\FileHelper\FileInfo;
use DBHelper;

/**
 * In general, media documents can be added only via
 * documents uploaded in the UI with the upload element.
 * This class allows creating an upload using a local
 * file path.
 *
 * @package Application
 * @subpackage Uploads
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LocalFileUpload extends Application_Uploads_Upload
{
    /**
     * Creates a new upload from the target file.
     *
     * NOTE: Creates a new upload every time, even if the
     * file is the same.
     *
     * @param FileInfo $sourceFile
     * @return LocalFileUpload
     */
    public static function create(FileInfo $sourceFile) : LocalFileUpload
    {
        return new self($sourceFile);
    }

    public function __construct(FileInfo $sourceFile)
    {
        $upload_id = DBHelper::insertInt(
            "INSERT INTO
                `uploads`
            SET
                `upload_date`=NOW(),
                `user_id`=:user_id,
                `upload_name`=:upload_name,
                `upload_extension`=:upload_extension",
            array(
                'user_id' => Application::getUser()->getID(),
                'upload_name' => $sourceFile->getBaseName(),
                'upload_extension' => $sourceFile->getExtension()
            )
        );

        parent::__construct($upload_id);

        $sourceFile->copyTo($this->getPath());
    }
}
