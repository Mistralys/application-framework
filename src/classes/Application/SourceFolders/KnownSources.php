<?php
/**
 * @package Source Folders
 */

declare(strict_types=1);

namespace Application\SourceFolders;

use Application\SourceFolders\Sources\AjaxSourceFolders;
use Application\SourceFolders\Sources\APISourceFolders;
use Application\SourceFolders\Sources\DeploymentTaskFolders;
use Application\SourceFolders\Sources\FormElementFolders;
use AppUtils\ClassHelper;

/**
 * Utility class used to access known source folders.
 *
 * @package Source Folders
 */
class KnownSources
{
    private SourceFoldersManager $manager;

    public function __construct(SourceFoldersManager $manager)
    {
        $this->manager = $manager;
    }

    public function AJAX() : AjaxSourceFolders
    {
        return ClassHelper::requireObjectInstanceOf(
            AjaxSourceFolders::class,
            $this->manager->getByID(AjaxSourceFolders::SOURCE_ID)
        );
    }

    public function API() : APISourceFolders
    {
        return ClassHelper::requireObjectInstanceOf(
            APISourceFolders::class,
            $this->manager->getByID('API')
        );
    }

    public function deploymentTasks() : DeploymentTaskFolders
    {
        return ClassHelper::requireObjectInstanceOf(
            DeploymentTaskFolders::class,
            $this->manager->getByID(DeploymentTaskFolders::SOURCE_ID)
        );
    }

    public function formElements() : FormElementFolders
    {
        return ClassHelper::requireObjectInstanceOf(
            FormElementFolders::class,
            $this->manager->getByID(FormElementFolders::SOURCE_ID)
        );
    }
}
