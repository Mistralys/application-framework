<?php

declare(strict_types=1);

namespace Application\Media\Admin;

use Application\AppFactory;
use Application\Media\Admin\Screens\Mode\View\SettingsSubmode;
use Application\Media\Admin\Screens\Mode\View\StatusSubmode;
use Application\Media\Admin\Screens\Mode\View\TagsSubmode;
use Application\Media\Admin\Screens\Mode\ViewMode;
use Application\Media\Collection\MediaCollection;
use UI\AdminURLs\AdminURL;

class MediaRecordAdminURLs
{
    private int $recordID;

    public function __construct(int $recordID)
    {
        $this->recordID = $recordID;
    }

    public function download() : AdminURL
    {
        return $this->view()
            ->submode(StatusSubmode::URL_NAME)
            ->string(StatusSubmode::REQUEST_PARAM_DOWNLOAD, 'yes');
    }

    public function status() : AdminURL
    {
        return $this->view()
            ->submode(StatusSubmode::URL_NAME);
    }

    public function tagging() : AdminURL
    {
        return $this->view()
            ->submode(TagsSubmode::URL_NAME);
    }

    public function settings() : AdminURL
    {
        return $this->view()
            ->submode(SettingsSubmode::URL_NAME);
    }

    public function view() : AdminURL
    {
        return $this->base();
    }
    
    public function base() : AdminURL
    {
        return AppFactory::createMediaCollection()
            ->adminURL()
            ->base()
            ->mode(ViewMode::URL_NAME)
            ->int(MediaCollection::PRIMARY_NAME, $this->recordID);
    }
}
