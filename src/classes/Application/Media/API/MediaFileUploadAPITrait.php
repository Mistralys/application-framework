<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\APIMethodInterface;
use Application\API\Parameters\Type\Base64FileParameter;
use Application\API\Parameters\Type\StringParameter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaFileValidator;
use Application\Media\Writer\MediaWriterException;

/**
 * Trait used to implement the interface {@see MediaFileUploadAPIInterface}.
 *
 * It owns no validation codes; file validation failures are reported
 * through {@see MediaWriterException}'s `6810xx` block (design review
 * Decision 5).
 *
 * @package Media
 * @subpackage API
 * @see MediaFileUploadAPIInterface
 */
trait MediaFileUploadAPITrait
{
    private ?StringParameter $mediaFileNameParam = null;
    private ?Base64FileParameter $mediaFileContentParam = null;
    private ?MediaFileContent $mediaFileContent = null;
    private bool $mediaFileContentResolved = false;

    public function registerMediaFileParams(bool $required) : self
    {
        $this->mediaFileNameParam = $this->manageParams()
            ->addParam(MediaFileUploadAPIInterface::PARAM_FILE_NAME, 'File name')
            ->string();

        $this->mediaFileNameParam->setDescription('Name of the file being uploaded, including its extension (e.g. `hero.png`).');

        $this->mediaFileContentParam = $this->manageParams()
            ->addParam(MediaFileUploadAPIInterface::PARAM_FILE_CONTENT, 'File content')
            ->base64File();

        $this->mediaFileContentParam->setDescription('Base64-encoded file content, optionally as a `data:<mime>;base64,` URI.');

        if($required) {
            $this->mediaFileNameParam->makeRequired();
            $this->mediaFileContentParam->makeRequired();
        } else {
            $this->manageParams()->addRule()->requiredIfOtherIsSet(
                'fileContent requires fileName',
                $this->mediaFileNameParam,
                $this->mediaFileContentParam
            );

            $this->manageParams()->addRule()->requiredIfOtherIsSet(
                'fileName requires fileContent',
                $this->mediaFileContentParam,
                $this->mediaFileNameParam
            );
        }

        return $this;
    }

    public function hasMediaFile() : bool
    {
        return $this->mediaFileNameParam?->getValue() !== null
            && $this->mediaFileContentParam?->getValue() !== null;
    }

    public function getMediaFileContent() : ?MediaFileContent
    {
        if($this->mediaFileContentResolved) {
            return $this->mediaFileContent;
        }

        $this->mediaFileContentResolved = true;

        if(!$this->hasMediaFile()) {
            return null;
        }

        $fileName = (string)$this->mediaFileNameParam?->getValue();
        $content = (string)$this->mediaFileContentParam?->getDecodedContent();

        try {
            $this->mediaFileContent = MediaFileContent::fromBinary($fileName, $content);
        } catch (MediaWriterException $e) {
            $this->sendWriterValidationError($e);
        }

        return $this->mediaFileContent;
    }

    public function validateMediaFile(?string $requiredTypeID=null) : string
    {
        $content = $this->getMediaFileContent();

        if($content === null) {
            $this->errorResponse(APIMethodInterface::ERROR_NO_VALUE_AVAILABLE)
                ->setErrorMessage('No media file was submitted.')
                ->send();
        }

        try {
            return (new MediaFileValidator())->validate($content, $requiredTypeID);
        } catch (MediaWriterException $e) {
            $this->sendWriterValidationError($e);
        }
    }

    /**
     * Converts a {@see MediaWriterException} into a client error response,
     * or rethrows it to the shared execution boundary.
     *
     * - An {@see MediaWriterException::isValidationError()} exception (the
     *   client-facing `6810xx` block) is sent as a client error through its
     *   own code.
     * - Any other exception (the internal `6811xx` block) is rethrown, so
     *   it is handled by `BaseAPIMethod`'s shared execution boundary as an
     *   unexpected throwable, rather than being reported as a validation
     *   failure it is not.
     *
     * @param MediaWriterException $e
     * @return never
     * @throws MediaWriterException
     */
    protected function sendWriterValidationError(MediaWriterException $e) : never
    {
        if(!$e->isValidationError()) {
            throw $e;
        }

        $this->errorResponse($e->getCode())
            ->setErrorMessage($e->getMessage())
            ->send();
    }
}
