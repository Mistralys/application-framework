<?php

declare(strict_types=1);

use AppUtils\FileHelper;

abstract class Application_RequestLog_AbstractFolderContainer extends Application_RequestLog_AbstractLogContainer
{
    abstract protected function isValidFolder(string $folder) : bool;

    protected function _load() : void
    {
        $storageFolder = $this->getStorageFolder();

        $folders = FileHelper::getSubfolders($storageFolder);

        foreach($folders as $folder)
        {
            if($this->isValidFolder($folder))
            {
                $this->addContainer($folder, $storageFolder . '/' . $folder);
            }
        }
    }
}
