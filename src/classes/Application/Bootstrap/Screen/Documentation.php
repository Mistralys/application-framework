<?php
/**
 * File containing the class {@see Application_Bootstrap_Screen_Documentation}.
 *
 * @package Application
 * @subpackage Bootstrap
 * @see Application_Bootstrap_Screen_Documentation
 */

declare(strict_types=1);

use Application\Application;
use Application\Framework\PackageInfo;
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
    public const string DISPATCHER_NAME = 'documentation/index.php';

    private DocsManager $manager;

    public function getDispatcher() : string
    {
        return self::DISPATCHER_NAME;
    }

    protected function _boot() : void
    {
        $this->enableScriptMode();
        $this->createEnvironment();

        $this->manager = new DocsManager();

        $this->addFilesFromFolder(APP_ROOT.'/documentation', Application_Driver::getInstance()->getAppNameShort());

        $this->addFilesFromFolder($this->resolveFrameworkFolder(), PackageInfo::getNameShort());

        // The viewer needs to know the URL to the vendor/ folder, relative
        // to the script. This is needed to load the clientside dependencies,
        // like jQuery and Bootstrap.
        (new DocsViewer($this->manager, $this->resolveVendorURL()))
            ->setTitle(sprintf('%1$s Documentation', $this->driver->getAppNameShort()))
            ->display();

        Application::exit('Documentation displayed');
    }

    private function resolveFrameworkFolder() : string
    {
        if(defined('APP_BUNDLED_DOCUMENTATION') && APP_BUNDLED_DOCUMENTATION === true) {
            return __DIR__.'/../../../../../docs/documentation';
        }

        return sprintf(
            '%s/%s/docs/documentation',
            $this->app->getVendorFolder(),
            PackageInfo::getComposerID()
        );
    }

    private function resolveVendorURL() : string
    {
        if(defined('APP_BUNDLED_DOCUMENTATION') && APP_BUNDLED_DOCUMENTATION === true) {
            return '../../../vendor';
        }

        return '../vendor';
    }

    private function addFilesFromFolder(string $targetPath, string $category) : void
    {
        if(!is_dir($targetPath))
        {
            return;
        }

        $files = $this->findDocumentationFiles($targetPath);

        foreach ($files as $file)
        {
            $this->manager->addFile($category.' - '.basename($file), $file);
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
