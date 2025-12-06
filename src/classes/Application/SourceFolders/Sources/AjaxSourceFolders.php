<?php

declare(strict_types=1);

namespace Application\SourceFolders\Sources;

use Application\SourceFolders\BaseSourceFolder;

class AjaxSourceFolders extends BaseSourceFolder
{
    const string SOURCE_ID = 'AJAX';

    public function __construct()
    {
        parent::__construct(self::SOURCE_ID, t('AJAX Methods'));

        $this->addFolder($this->getClassesFolder().'/AjaxMethods');
    }
}
