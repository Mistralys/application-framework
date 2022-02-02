<?php

declare(strict_types=1);

use AppUtils\FileHelper;

abstract class Application_RequestLog_AbstractFileContainer extends Application_RequestLog_AbstractLogContainer
{
    abstract protected function isValidFile(string $file) : bool;

    protected function _load() : void
    {
        $storageFolder = $this->getStorageFolder();

        $files = FileHelper::createFileFinder($storageFolder)
            ->setPathmodeStrip()
            ->getAll();

        foreach($files as $file)
        {
            if($this->isValidFile($file))
            {
                $this->addContainer($file, $storageFolder);
            }
        }
    }
}


