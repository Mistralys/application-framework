<?php

declare(strict_types=1);

namespace Application\SourceFolders\Sources;

use Application\SourceFolders\BaseSourceFolder;

class APISourceFolders extends BaseSourceFolder
{
    const string SOURCE_ID = 'API';

    public function __construct()
    {
        parent::__construct(self::SOURCE_ID, t('API Methods'));

        $this->addFolder($this->getClassesFolder() . '/API/');
    }
}
