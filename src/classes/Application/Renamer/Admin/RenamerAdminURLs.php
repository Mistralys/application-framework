<?php

declare(strict_types=1);

namespace Application\Renamer\Admin;

use Application\Development\Admin\Screens\DevelArea;
use Application\Renamer\Admin\Screens\Mode\RenamerMode;
use Application\Renamer\Admin\Screens\Submode\ConfigurationSubmode;
use Application\Renamer\Admin\Screens\Submode\ExportSubmode;
use Application\Renamer\Admin\Screens\Submode\ReplaceSubmode;
use Application\Renamer\Admin\Screens\Submode\ResultsSubmode;
use Application\Renamer\Admin\Screens\Submode\SearchSubmode;
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
            ->submode(SearchSubmode::URL_NAME);
    }

    public function configuration(bool $reset=false) : AdminURLInterface
    {
        $url = $this
            ->base()
            ->submode(ConfigurationSubmode::URL_NAME);

        if($reset) {
            $url->bool(ConfigurationSubmode::REQUEST_PARAM_RESET, true);
        }

        return $url;
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(DevelArea::URL_NAME)
            ->mode(RenamerMode::URL_NAME);
    }

    public function results() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ResultsSubmode::URL_NAME);
    }

    public function replace() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ReplaceSubmode::URL_NAME);
    }

    public function export() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(ExportSubmode::URL_NAME);
    }
}
