<?php

declare(strict_types=1);

namespace Application\SourceFolders\Sources;

use Application\SourceFolders\BaseSourceFolder;

class DeploymentTaskFolders extends BaseSourceFolder
{
    const string SOURCE_ID = 'DeploymentTask';

    public function __construct()
    {
        parent::__construct(self::SOURCE_ID, t('Deployment Tasks'));

        $this->addFolder($this->getClassesFolder() . '/DeploymentTasks');
    }
}
