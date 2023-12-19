<?php

declare(strict_types=1);

echo sb()
    ->para(t('Regular'))
    ->para(sb()->secondary('Secondary'))
    ->para(sb()->muted('Muted'))
    ->para(sb()->danger('Dangerous')->add('|')->dangerXXL('Very Dangerous'))
    ->para(sb()->warning('Warning'))
    ->para(sb()->success('Success'))
    ->para(sb()->info('Information'))
    ->para(sb()->link(t('Internal link'), '#'))
    ->para(sb()->link(t('External link'), '#', true))
    ->para(sb()->muted(sb()->t(
        'This %1$slink is nested%2$s in muted text',
        sb()->linkOpen('#'),
        sb()->linkClose()
    )))
    ->para(sb()->code(t('Code')))
    ->para(sb()->spanned(sb()->inverted('&nbsp;'.t('Inverted').'&nbsp;'), 'bg-inverted'));
