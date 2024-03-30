<?php

declare(strict_types=1);

namespace Application\Media;

use Application\Admin\Area\BaseMediaLibraryScreen;
use Application\Admin\Area\Media\BaseCreateMediaScreen;
use Application\Admin\Area\Media\BaseImageGalleryScreen;
use Application\Admin\Area\Media\BaseMediaListScreen;
use Application\Admin\Area\Media\BaseMediaSettingsScreen;
use UI\AdminURLs\AdminURL;

class MediaAdminURLs
{
    public function base() : AdminURL
    {
        return AdminURL::create()
            ->area(BaseMediaLibraryScreen::URL_NAME);
    }

    public function list() : AdminURL
    {
        return $this->base()
            ->mode(BaseMediaListScreen::URL_NAME);
    }

    public function gallery() : AdminURL
    {
        return $this->base()
            ->mode(BaseImageGalleryScreen::URL_NAME);
    }

    public function settings() : AdminURL
    {
        return $this->base()
            ->mode(BaseMediaSettingsScreen::URL_NAME);
    }

    public function updateSizes() : AdminURL
    {
        return $this->list()
            ->string(BaseMediaListScreen::REQUEST_PARAM_UPDATE_SIZES, 'yes');
    }

    public function create() : AdminURL
    {
        return $this->base()
            ->mode(BaseCreateMediaScreen::URL_NAME);
    }
}
