<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

class NewsAdminURLs
{
    public function manage() : ManageNewsAdminURLs
    {
        return new ManageNewsAdminURLs();
    }

    public function read() : ReadNewsAdminURLs
    {
        return new ReadNewsAdminURLs();
    }
}
