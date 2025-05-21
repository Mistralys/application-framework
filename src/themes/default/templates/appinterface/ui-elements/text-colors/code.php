<?php

declare(strict_types=1);

use function AppUtils\attr;

echo sb()
    ->h2(t('Text styles'), attr('class=example'))

    ->para(t('Regular'))
    ->para(sb()->bold('Bold text'))
    ->para(sb()->italic('Italic text'))
    ->para(sb()->reference('Reference text'))
    ->para(sb()->code(t('Code formatted text')))
    ->para(sb()->link(t('Internal link'), '#'))
    ->para(sb()->link(t('External link'), '#', true))
    ->para(sb()->muted(sb()->t(
        'This %1$slink is nested%2$s in muted text',
        sb()->linkOpen('#'),
        sb()->linkClose()
    )))

    ->h2(t('Text colors'), attr('class=example'))

    ->para(sb()->secondary('Secondary'))
    ->para(sb()->muted('Muted'))
    ->para(sb()->danger('Dangerous'))
    ->para(sb()->dangerXXL('Very Dangerous'))
    ->para(sb()->warning('Warning'))
    ->para(sb()->success('Success'))
    ->para(sb()->info('Information'))
    ->para(sb()->spanned(sb()->inverted('&nbsp;'.t('Inverted').'&nbsp;'), 'bg-inverted'));

