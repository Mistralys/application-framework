<?php

declare(strict_types=1);

namespace Application\Media\Admin;

use Application\Media\Admin;
use Application\Media\Admin\Screens\MediaLibraryArea;
use Application\Media\Admin\Screens\Mode\CreateMode;
use Application\Media\Admin\Screens\Mode\ListMode;
use UI\AdminURLs\AdminURL;

class MediaAdminURLs
{
    public function base() : AdminURL
    {
        return AdminURL::create()
            ->area(MediaLibraryArea::URL_NAME);
    }

    public function list() : AdminURL
    {
        return $this->base()
            ->mode(ListMode::URL_NAME);
    }

    public function gallery() : AdminURL
    {
        return $this->base()
            ->mode(Admin\Screens\Mode\ImageGalleryMode::URL_NAME);
    }

    public function settings() : AdminURL
    {
        return $this->base()
            ->mode(Admin\Screens\Mode\GlobalSettingsMode::URL_NAME);
    }

    public function updateSizes() : AdminURL
    {
        return $this->list()
            ->string(ListMode::REQUEST_PARAM_UPDATE_SIZES, 'yes');
    }

    public function create() : AdminURL
    {
        return $this->base()
            ->mode(CreateMode::URL_NAME);
    }
}
