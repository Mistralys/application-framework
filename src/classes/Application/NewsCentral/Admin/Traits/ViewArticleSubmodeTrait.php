<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Traits;

use Application\AppFactory;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticleMode;
use Application\NewsCentral\NewsCollection;
use UI\AdminURLs\AdminURLInterface;

trait ViewArticleSubmodeTrait
{
    public function getParentScreenClass() : string
    {
        return ViewArticleMode::class;
    }

    public function createCollection() : NewsCollection
    {
        return AppFactory::createNews();
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->manage()->list();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->manage()->list();
    }
}
