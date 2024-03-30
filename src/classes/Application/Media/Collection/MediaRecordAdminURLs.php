<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\Admin\Area\Media\BaseViewMediaScreen;
use Application\Admin\Area\Media\View\BaseMediaSettingsScreen;
use Application\Admin\Area\Media\View\BaseMediaStatusScreen;
use Application\Admin\Area\Media\View\BaseMediaTagsScreen;
use Application\AppFactory;
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
            ->submode(BaseMediaStatusScreen::URL_NAME)
            ->string(BaseMediaStatusScreen::REQUEST_PARAM_DOWNLOAD, 'yes');
    }

    public function status() : AdminURL
    {
        return $this->view()
            ->submode(BaseMediaStatusScreen::URL_NAME);
    }

    public function tagging() : AdminURL
    {
        return $this->view()
            ->submode(BaseMediaTagsScreen::URL_NAME);
    }

    public function settings() : AdminURL
    {
        return $this->view()
            ->submode(BaseMediaSettingsScreen::URL_NAME);
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
            ->mode(BaseViewMediaScreen::URL_NAME)
            ->int(MediaCollection::PRIMARY_NAME, $this->recordID);
    }
}
