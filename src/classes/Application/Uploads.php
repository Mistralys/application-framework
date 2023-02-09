<?php
/**
 * File containing the {@link Application_Uploads} class.
 *
 * @package Application
 * @subpackage Uploads
 * @see Application_Uploads
 */

use Application\Uploads\LocalFileUpload;
use AppUtils\FileHelper\FileInfo;

/**
 * Upload manager: used to store uploaded files temporarily until
 * they can be converted to full-fledged media files. Keeps track
 * of uploaded files in a dedicated database table, and offers a
 * simple API to handle the files or to clean up old uploads.
 *
 * @package Application
 * @subpackage Uploads
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Uploads
{
    public const ERROR_NO_UPLOAD_TMP_DIR = 42801;
    public const ERROR_UPLOAD_STOPPED_BY_EXTENSION = 42802;
    
    protected static ?Application_Uploads $instance = null;

    /**
     * The full path to the storage folder.
     * @see getStorageFolder()
     * @var string
     */
    protected string $storageFolder;

    /**
     * Retrieves the global instance of the uploads manager. Creates
     * the instance as needed.
     *
     * @return Application_Uploads
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_Uploads();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->storageFolder = Application::getTempFolder();
    }

    /**
     * Retrieves the full path to the folder where uploads are stored.
     * @return string
     */
    public function getStorageFolder() : string
    {
        return $this->storageFolder;
    }

    protected array $messages = array();

    /**
     * Resets all internal messages, discards all existing messages.
     */
    protected function resetMessages()
    {
        $this->messages = array();
    }

    public function addFromLocalFile(FileInfo $file) : LocalFileUpload
    {
        return LocalFileUpload::create($file);
    }

    /**
     * Adds a file from a file upload with the specified upload field name.
     *
     * @param string $variableName
     * @return boolean|Application_Uploads_Upload
     */
    public function addFromFileUpload($variableName)
    {
        $this->resetMessages();

        if (!isset($_FILES[$variableName])) {
            $this->addMessage(t('Could not detect any uploaded file.'));

            return false;
        }

        if ($_FILES[$variableName]['error']) 
        {
            switch ($_FILES[$variableName]['error']) 
            {
                case UPLOAD_ERR_INI_SIZE:
                    $this->addMessage(t('The uploaded file exceeds the maximum size of %1$s.', ini_get('upload_max_filesize')));
                    break;

                case UPLOAD_ERR_FORM_SIZE:
                    $this->addMessage(t('The uploaded file exceeds the maximum size defined in the upload form.'));
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $this->addMessage(t('The file was uploaded only partially, please try again.'));
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $this->addMessage(t('No file was uploaded, please try again.'));
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Application_Exception(
                        'File upload failed because no temporary folder exists on PHP\'s side.',
                        '',
                        self::ERROR_NO_UPLOAD_TMP_DIR
                    );

                case UPLOAD_ERR_CANT_WRITE:
                    $this->addMessage(t('File cannot be written to disk.'));
                    break;

                case UPLOAD_ERR_EXTENSION:
                    throw new Application_Exception(
                        'A file upload was stopped by a PHP extension.',
                        '',
                        self::ERROR_UPLOAD_STOPPED_BY_EXTENSION
                    );

                default:
                    $this->addMessage(t('An unknown error has occurred during the upload, with code %1$s.', $_FILES[$variableName]['error']));
                    break;
            }
            return false;
        }

        if (!file_exists($this->storageFolder) && !@mkdir($this->storageFolder, 0777, true)) {
            $this->addMessage(t('The storage folder does not exist and cannot be created.'));

            return false;
        }

        $info = pathinfo($_FILES[$variableName]['name']);

        if (!isset($info['extension']) || empty($info['extension'])) {
            $this->addMessage(t('The file has no extension.'));

            return false;
        }

        $extension = strtolower($info['extension']);
        $name = explode('.', $info['basename']);
        array_pop($name);
        $name = implode('.', $name);


        if (!preg_match('^[a-zA-Z0-9_]{1,}$^', $name)) {
            $this->addMessage(t('May only contain letters, numbers and the characters - and _.'));
            return false;
        }

        if (!$this->validate($variableName)) {
            return false;
        }

        DBHelper::startTransaction();

        try {
            $upload = $this->insertNew($name, $extension);
            $targetFile = $upload->getPath();
        } catch (Exception $e) {
            $this->addMessage('Exception with message [' . $e->getMessage() . ']');

            return false;
        }

        if (!@move_uploaded_file($_FILES[$variableName]['tmp_name'], $targetFile)) {
            $this->addMessage(t('File could not be moved to destination.') . ' ' . t('Review access rights to the application storage folder.'));

            return false;
        }

        DBHelper::commitTransaction();

        return $upload;
    }

    /**
     * Validates the file by matching its extension to the file content
     * to avoid uploading documents that have the wrong extension.
     *
     * @param string $variableName
     * @return boolean
     */
    protected function validate($variableName)
    {
        $fileName = $_FILES[$variableName]['name'];
        $filePath = $_FILES[$variableName]['tmp_name'];


        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'csv':
                return true;
                
            case 'svg':
                return true;

            case 'jpg':
            case 'gif':
            case 'jpeg':
            case 'png':
                if (!getimagesize($filePath)) {
                    $this->addMessage(t('The file\'s extension does not match its contents.'));

                    return false;
                }

                return true;
        }

        $this->addMessage(t('Unsupported file extension %1$s.', $extension));

        return false;
    }

    /**
     * Adds a message to the message pool.
     * @param string $message
     */
    protected function addMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * Retrieves the last message that has been added.
     * @return NULL|string
     */
    public function getLastMessage()
    {
        if (empty($this->messages)) {
            return null;
        }

        return $this->messages[count($this->messages) - 1];
    }

    /**
     * Creates a new upload by adding it to the database. Does
     * not handle moving or creating the corresponding file.
     *
     * @param string $name
     * @param string $extension
     * @return Application_Uploads_Upload
     */
    protected function insertNew($name, $extension)
    {
        $user = Application::getUser();

        $upload_id = DBHelper::insertInt(
            "INSERT INTO
                `uploads`
            SET
                `upload_date`=NOW(),
                `user_id`=:user_id,
                `upload_name`=:upload_name,
                `upload_extension`=:upload_extension",
            array(
                ':user_id' => $user->getID(),
                ':upload_name' => $name,
                ':upload_extension' => $extension
            )
        );

        return $this->getByID($upload_id);
    }

    /**
     * Retrieves an upload object by its ID.
     * Throws an exception if it does not exist in the database.
     *
     * @param int $upload_id
     * @return Application_Uploads_Upload
     * @throws Application_Exception
     */
    public function getByID($upload_id)
    {
        require_once 'Application/Uploads/Upload.php';

        return new Application_Uploads_Upload($upload_id);
    }

    /**
     * Checks whether the specified upload ID exists.
     *
     * @param int $upload_id
     * @return boolean
     */
    public function idExists($upload_id)
    {
        $entry = DBHelper::fetch(
            "SELECT
                `upload_id`
            FROM
                `uploads`
            WHERE
                `upload_id`=:upload_id",
            array(
                ':upload_id' => $upload_id
            )
        );

        if (is_array($entry) && isset($entry['upload_id'])) {
            return true;
        }

        return false;
    }
}