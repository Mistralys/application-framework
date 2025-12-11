<?php

declare(strict_types=1);

namespace UI\CSSGenerator;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use DateTime;

class CSSGenFile implements StringPrimaryRecordInterface
{
    public const int ERROR_SOURCE_FILE_NO_MODIFIED_DATE = 148401;

    private FileInfo $sourceFile;
    private string $id;
    private CSSGenLocation $location;
    private FileInfo $targetFile;

    public function __construct(FileInfo $cssSourceFile, CSSGenLocation $location)
    {
        $this->sourceFile = $cssSourceFile;
        $this->targetFile = FileInfo::factory(str_replace('.'.CSSGen::CSS_TEMPLATE_EXTENSION, '.css', $cssSourceFile->getPath()));
        $this->location = $location;
        $this->id = md5($this->sourceFile->getPath());
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->sourceFile->getBasename();
    }

    public function getLocation() : CSSGenLocation
    {
        return $this->location;
    }

    public function getTargetFile(): FileInfo
    {
        return $this->targetFile;
    }

    public function getRelativePath() : string
    {
        $relative = FileHelper::relativizePath(
            $this->sourceFile->getPath(),
            $this->location->getCSSFolder()->getPath()
        );

        return '/'.ltrim(str_replace($this->sourceFile->getName(), '', $relative), '/');
    }

    public function getModifiedDate() : DateTime
    {
        $date = $this->sourceFile->getModifiedDate();

        if($date !== null) {
            return $date;
        }

        throw new CSSGenException(
            'Source CSS file has no modified date',
            '',
            self::ERROR_SOURCE_FILE_NO_MODIFIED_DATE
        );
    }

    public function getStatusPretty() : string
    {
        $dateTarget = $this->targetFile->getModifiedDate();

        if($dateTarget === null) {
            return (string)sb()->warning(t('Not generated'));
        }

        if($dateTarget < $this->getModifiedDate()) {
            return (string)sb()->warning(t('Update needed'));
        }

        return (string)sb()->success(t('Up to date'));
    }

    public function generate() : self
    {
        $options = array(
            'versioning' => false,
            'minify' => false,
            'output_file' => $this->targetFile->getName()
        );

        csscrush_file($this->sourceFile->getPath(), $options);
        return $this;
    }
}
