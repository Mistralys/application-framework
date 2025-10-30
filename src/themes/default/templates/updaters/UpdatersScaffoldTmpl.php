<?php

declare(strict_types=1);

namespace Application\Themes\Default\Templates\Updaters;

use Application\Updaters\UpdaterInterface;
use UI_Page_Template_Custom;

class UpdatersScaffoldTmpl extends UI_Page_Template_Custom
{
    public const string VAR_CONTENT = 'content';
    public const string VAR_TITLE = 'title';
    public const string VAR_ACTIVE_UPDATER = 'active-updater';
    private ?UpdaterInterface $activeUpdater = null;

    protected function preRender(): void
    {
        $updater = $this->getVar(self::VAR_ACTIVE_UPDATER);

        if($updater instanceof UpdaterInterface) {
            $this->activeUpdater = $updater;
        }
    }

    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('ui-updaters.css');


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $this->getVar(self::VAR_TITLE) ?></title>
    <link rel="shortcut icon" href="favicon.ico"/>
    <?php
    echo $this->ui->renderHeadIncludes();
    ?>
</head>
<body>
    <div class="container">
        <br/>
        <div class="navbar">
            <div class="navbar-inner">
                <span class="pull-right instance-label">
                    <?php pts('Instance:');
                    echo strtoupper(APP_INSTANCE_ID) ?>
                    |
                    <?php pts('Version:');
                    echo $this->driver->getExtendedVersion() ?>
                </span>
                <a class="brand"
                   href="<?php echo $this->buildURL() ?>"><?php pt('%1$s maintenance', $this->driver->getAppNameShort()) ?></a>
                <ul class="nav">
                    <li><a href="xml/monitor"><?php pt('Health Monitor') ?></a></li>
                    <li><a href="./"><?php pt('Back to %1$s', $this->driver->getAppNameShort()) ?></a></li>
                </ul>
            </div>
        </div>
        <?php

        echo $this->ui->getPage()->renderMessages();

        if (isset($this->activeUpdater)) {
            ?>
            <ul class="breadcrumb">
                <li>
                    <a href="<?php echo $this->buildURL() ?>"><?php pt('Dashboard') ?></a>
                    <span class="divider">/</span>
                </li>
                <li class="active">
                    <?php echo $this->activeUpdater->getLabel() ?>
                </li>
            </ul>
            <h1><?php echo $this->activeUpdater->getLabel() ?></h1>
            <p class="abstract">
                <?php echo $this->activeUpdater->getDescription() ?>
            </p>
            <hr>
            <?php
        }
        ?>

        <?php echo $this->getVar(self::VAR_CONTENT) ?>

    </div>
    <p><br></p>
</body>
</html>
    <?php
    }
}
