<?php

/* @var $this UI_Page_Template */

use Application\AppFactory;

$grid = $this->ui->createDataGrid('flag-icons');
$grid->addColumn('label', t('Label'));
$grid->addColumn('icon', t('Icon'));

$countries = AppFactory::createCountries()->getAll(false);

$entries = array();
foreach($countries as $country)
{
    $entries[] = array(
        'label' => $country->getLocalizedLabel(),
        'icon' => $country->getIcon()
    );
}

echo $grid->render($entries);
