<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Admin_Area_Mode;
use AppUtils\ClassHelper;
use AppUtils\FileHelper;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Area\TestingScreen;
use TestDriver\ClassFactory;

class TestingOverviewScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'overview';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Overview');
    }

    protected function _renderContent()
    {
        $list = $this->ui->createBigSelection()
            ->makeSmall();

        foreach($this->getScreenList() as $screenDef) {
            $list->addLink(
                $screenDef['label'],
                $this->getTestURL($screenDef['urlName'])
            );
        }

        return $this->renderer
            ->appendContent($list)
            ->makeWithoutSidebar();
    }

    private function getScreenList() : array
    {
        $result = array();

        $reference = CancelHandleActionsScreen::class;
        foreach(FileHelper::createFileFinder(__DIR__)->getPHPClassNames() as $name) {
            $class = ClassHelper::resolveClassByReference($name, $reference);
            if(is_a($class, TestingScreenInterface::class, true)) {
                $result[] = array(
                    'label' => $class::getTestLabel(),
                    'urlName' => $class::URL_NAME
                );
            }
        }

        usort($result, static function(array $a, array $b) : int {
            return strnatcasecmp($a['label'], $b['label']);
        });

        return $result;
    }

    protected function getTestURL(string $testURLName, array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = TestingScreen::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = $testURLName;

        return ClassFactory::createRequest()->buildURL($params);
    }
}
