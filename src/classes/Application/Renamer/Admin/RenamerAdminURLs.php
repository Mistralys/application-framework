<?php

declare(strict_types=1);

namespace Application\Renamer\Admin;

use Application\Renamer\Admin\Screens\Mode\BaseRenamerMode;
use Application\Renamer\Admin\Screens\Submode\BaseConfigurationSubmode;
use Application\Renamer\Admin\Screens\Submode\BaseExportSubmode;
use Application\Renamer\Admin\Screens\Submode\BaseReplaceSubmode;
use Application\Renamer\Admin\Screens\Submode\BaseResultsSubmode;
use Application\Renamer\Admin\Screens\Submode\BaseSearchSubmode;
use Application_Admin_Area_Devel;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class RenamerAdminURLs
{
    public function __construct()
    {
    }

    public function search() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseSearchSubmode::URL_NAME);
    }

    public function configuration(bool $reset=false) : AdminURLInterface
    {
        $url = $this
            ->base()
            ->submode(BaseConfigurationSubmode::URL_NAME);

        if($reset) {
            $url->bool(BaseConfigurationSubmode::REQUEST_PARAM_RESET, true);
        }

        return $url;
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(Application_Admin_Area_Devel::URL_NAME)
            ->mode(BaseRenamerMode::URL_NAME);
    }

    public function results() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseResultsSubmode::URL_NAME);
    }

    public function replace() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseReplaceSubmode::URL_NAME);
    }

    public function export() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseExportSubmode::URL_NAME);
    }
}
