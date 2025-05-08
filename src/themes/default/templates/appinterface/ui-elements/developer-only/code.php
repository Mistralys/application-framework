<?php

declare(strict_types=1);

use UI\CSSClasses;
use function AppUtils\attr;

echo sb()
    ->para(
        sb()->developer('This text is only shown to developers.')
    )
    ->para(
        t(
            'Using the class %1$s, this paragraph is also only shown to developers.',
            sb()->code(CSSClasses::RIGHT_DEVELOPER)
        ),
        attr('class='.CSSClasses::RIGHT_DEVELOPER)
    );
