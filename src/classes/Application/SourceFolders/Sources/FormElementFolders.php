<?php

declare(strict_types=1);

namespace Application\SourceFolders\Sources;

use Application\SourceFolders\BaseSourceFolder;

/**
 * @see \UI_Form::getClassFolders()
 */
class FormElementFolders extends BaseSourceFolder
{
    const string SOURCE_ID = 'FormElement';

    public function __construct()
    {
        parent::__construct(self::SOURCE_ID, t('Form Elements'));

        $this->addFolder($this->getClassesFolder().'/FormElements');
    }
}
