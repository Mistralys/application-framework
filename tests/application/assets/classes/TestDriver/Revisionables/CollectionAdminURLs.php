<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use TestDriver\Area\RevisionableScreen;
use TestDriver\Area\RevisionableScreen\CreateRevisionableScreen;
use TestDriver\Area\RevisionableScreen\RevisionableListScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class CollectionAdminURLs
{
    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(RevisionableScreen::URL_NAME);
    }

    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(RevisionableListScreen::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateRevisionableScreen::URL_NAME);
    }
}
