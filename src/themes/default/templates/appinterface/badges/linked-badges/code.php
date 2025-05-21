<?php

echo sb()
    ->add(
        UI::badge(t('Regular badge'))
            ->makeSuccess()
            ->setTooltip(t('This is a tooltip'))
    )
    ->link(
        UI::badge(t('Linked badge'))
            ->makeSuccess()
            ->setTooltip(t('This is a tooltip')),
        'https://mistralys.eu'
    );
