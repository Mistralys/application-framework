<?php

    /* @var $this UI_Page_Template */

    $total = $this->getVar('total');
    $batchSize = $this->getVar('batch-size');

    $this->createSection()
    ->setAbstract(t('The %1$s entries you selected are being processed.', $total).' '.
        t('Please be patient, depending on the type of operation and the amount of entries, this can take a while.').' '.
        t('For performance reasons, the entries are processed in batches of %1$s.', $batchSize))
    ->setContent('<div id="batch-progressbar"></div>')
    ->display();