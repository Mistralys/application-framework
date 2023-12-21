<?php

declare(strict_types=1);

$ui = UI::getInstance();

echo sb()
    ->tag('h3', 'Color styles')
    ->add($ui->createMessage('Information message.')
        ->makeInfo()
    )
    ->add($ui->createMessage(t('Warning message'))
        ->makeWarning()
    )
    ->add($ui->createMessage(t('Success message'))
        ->makeSuccess()
    )
    ->add($ui->createMessage(t('Error message'))
        ->makeError()
    )
    ->tag('h3', 'Options')
    ->add($ui->createMessage(t('Non-dimissible message'))
        ->makeNotDismissable()
    )
    ->add($ui->createMessage(t('Slim layout'))
        ->makeSlimLayout()
    )
    ->add($ui->createMessage(t('Large layout'))
        ->makeLargeLayout()
    )
    ->add($ui->createMessage(t('With automatic icon'))
        ->makeSuccess()
        ->enableIcon()
    )
    ->tag('h3', 'Inline messages')
    ->para(sb()
        ->add('Message inline with text: ')
        ->add(
            $ui->createMessage('Inline message')
                ->makeSlimLayout()
                ->makeInline()
        )
    );
