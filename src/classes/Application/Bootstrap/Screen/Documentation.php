<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use Mistralys\MarkdownViewer\DocsManager;
use Mistralys\MarkdownViewer\DocsViewer;

class Application_Bootstrap_Screen_Documentation extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'documentation/index.php';
    }

    protected function _boot()
    {
        $this->enableScriptMode();
        $this->createEnvironment();

        $manager = new DocsManager();
        $appDocFolder = APP_ROOT.'/documentation';

        if(is_dir($appDocFolder))
        {
            $files = FileHelper::createFileFinder($appDocFolder)
                ->includeExtension('md')
                ->makeRecursive()
                ->setPathmodeAbsolute()
                ->getAll();

            foreach ($files as $file) {
                $manager->addFile(FileHelper::removeExtension($file), $file);
            }
        }

        $manager->addFile(
            'Framework documentation',
            $this->app->getVendorFolder().'/1and1/application_framework/docs/Documentation.md'
        );

        // The viewer needs to know the URL to the vendor/ folder, relative
        // to the script. This is needed to load the clientside dependencies,
        // like jQuery and Bootstrap.
        (new DocsViewer($manager, '../vendor'))
            ->setTitle(sprintf('%1$s Documentation', $this->driver->getAppNameShort()))
            ->display();

        Application::exit('Documentation displayed');
    }
}
