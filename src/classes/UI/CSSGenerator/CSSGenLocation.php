<?php

declare(strict_types=1);

namespace UI\CSSGenerator;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

class CSSGenLocation
{
    private string $id;
    private string $label;
    private FolderInfo $baseFolder;
    private FolderInfo $cssFolder;

    public function __construct(string $id, string $label, FolderInfo $baseFolder, FolderInfo $cssFolder)
    {
        $this->id = $id;
        $this->label = $label;
        $this->baseFolder = $baseFolder;
        $this->cssFolder = $cssFolder;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getBaseFolder() : FolderInfo
    {
        return $this->baseFolder;
    }

    public function getCSSFolder() : FolderInfo
    {
        return $this->cssFolder;
    }

    public function getRelativePath() : string
    {
        return FileHelper::relativizePath(
            $this->cssFolder->getPath(),
            $this->baseFolder->getPath()
        );
    }
}