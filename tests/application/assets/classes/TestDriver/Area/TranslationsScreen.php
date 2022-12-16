<?php

declare(strict_types=1);

namespace TestDriver\Area;
use Application_Admin_TranslationsArea;

class TranslationsScreen extends Application_Admin_TranslationsArea
{
    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function isUserAllowed(): bool
    {
        return true;
    }
}
