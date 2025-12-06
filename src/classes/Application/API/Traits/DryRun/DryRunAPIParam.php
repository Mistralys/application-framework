<?php

declare(strict_types=1);

namespace Application\API\Traits\DryRun;

use Application\API\Parameters\Type\BooleanParameter;

class DryRunAPIParam extends BooleanParameter
{
    public function __construct()
    {
        parent::__construct('dryRun', 'Dry run');

        $this->setDescription(sb()
            ->add('If enabled, a dry run of the operation will be performed.')
            ->add('The process will run as usual, but no actual changes will be made.')
            ->add('Note:')
            ->add('Any IDs and other data types returned will not correspond to real entities.')
        );
    }
}
