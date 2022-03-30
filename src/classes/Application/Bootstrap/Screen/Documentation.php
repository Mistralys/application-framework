<?php
/**
 * File containing the class {@see Application_Bootstrap_Screen_Documentation}.
 *
 * @package Application
 * @subpackage Bootstrap
 * @see Application_Bootstrap_Screen_Documentation
 */

declare(strict_types=1);

use AppUtils\FileHelper;
use Mistralys\MarkdownViewer\DocsManager;
use Mistralys\MarkdownViewer\DocsViewer;

/**
 * Documentation bootstrapper, which loads both the application's
 * documentation files (if any), and those of the framework.
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Bootstrap_Screen_Documentation extends Application_Bootstrap_Screen
{
    private DocsManager $manager;

    public function getDispatcher() : string
    {
        return 'documentation/index.php';
    }

    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->createEnvironment();

        $this->manager = new DocsManager();

        $this->addFilesFromFolder(APP_ROOT.'/documentation');

        $frameworkFolder = sprintf(
            '%s/%s/docs',
            $this->app->getVendorFolder(),
            PackageInfo::PROJECT_COMPOSER_ID
        );

        $this->addFilesFromFolder($frameworkFolder);

        // The viewer needs to know the URL to the vendor/ folder, relative
        // to the script. This is needed to load the clientside dependencies,
        // like jQuery and Bootstrap.
        (new DocsViewer($this->manager, '../vendor'))
            ->setTitle(sprintf('%1$s Documentation', $this->driver->getAppNameShort()))
            ->display();

        Application::exit('Documentation displayed');
    }

    private function addFilesFromFolder(string $targetPath) : void
    {
        if(!is_dir($targetPath))
        {
            return;
        }

        $files = $this->findDocumentationFiles($targetPath);

        foreach ($files as $file)
        {
            $this->manager->addFile(FileHelper::removeExtension($file), $file);
        }
    }

    private function findDocumentationFiles(string $targetPath) : array
    {
        return FileHelper::createFileFinder($targetPath)
            ->includeExtension('md')
            ->makeRecursive()
            ->setPathmodeAbsolute()
            ->getAll();
    }
}
