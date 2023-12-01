<?php

declare(strict_types=1);

use Application\AppFactory;

DBHelper::startTransaction();

// The example application has no countries by
// default, so we create some here.
$countries = AppFactory::createCountries();
$countries->createNewCountry('de', 'Germany');
$countries->createNewCountry('us', 'United States');
$countries->createNewCountry('es', 'Spain');

$list = $countries->getAll();

DBHelper::rollbackTransaction();

?>
<p><?php pt('Found %1$s countries.', count($list)); ?></p>
<ul>
    <?php
    foreach($list as $country)
    {
        ?>
        <li><?php echo $country->getIconLabel() ?></li>
        <?php
    }
    ?>
</ul>
