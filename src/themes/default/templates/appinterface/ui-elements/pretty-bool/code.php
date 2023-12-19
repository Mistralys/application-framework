<?php

    $booleans = array(
        array(
            'label' => (string)sb()->t('Success')->parentheses(t('Default')),
            'true' => UI::prettyBool(true),
            'false' => UI::prettyBool(false)
        ),
        array(
            'label' => t('Warning'),
            'true' => UI::prettyBool(true)->makeWarning(),
            'false' => UI::prettyBool(false)->makeWarning()
        ),
        array(
            'label' => t('Dangerous'),
            'true' => UI::prettyBool(true)->makeDangerous(),
            'false' => UI::prettyBool(false)->makeDangerous()
        ),
        array(
            'label' => t('Inverted'),
            'true' => UI::prettyBool(true)->makeColorsInverted(),
            'false' => UI::prettyBool(false)->makeColorsInverted()
        ),
        array(
            'label' => t('False value also colored'),
            'true' => UI::prettyBool(true)->enableFalseColor(),
            'false' => UI::prettyBool(false)->enableFalseColor()
        ),
        array(
            'label' => sb()->t('False value also colored')->parentheses(t('Inverted')),
            'true' => UI::prettyBool(true)->makeDangerous()->enableFalseColor(),
            'false' => UI::prettyBool(false)->makeDangerous()->enableFalseColor()
        ),
        array(
            'label' => t('Neutral colors'),
            'true' => UI::prettyBool(true)->makeColorsNeutral(),
            'false' => UI::prettyBool(false)->makeColorsNeutral()
        ),
        array(
            'label' => t('Icon with label'),
            'true' => UI::prettyBool(true)->makeIcon(),
            'false' => UI::prettyBool(false)->makeIcon()
        ),
        array(
            'label' => t('Icon only'),
            'true' => UI::prettyBool(true)->makeIcon(false),
            'false' => UI::prettyBool(false)->makeIcon(false)
        ),
        array(
            'label' => sb()->t('Without icon')->parentheses(t('Badge only')),
            'true' => UI::prettyBool(true)->disableIcon(),
            'false' => UI::prettyBool(false)->disableIcon()
        ),
        array(
            'label' => t('Enabled / disabled'),
            'true' => UI::prettyBool(true)->makeEnabledDisabled(),
            'false' => UI::prettyBool(false)->makeEnabledDisabled()
        ),
        array(
            'label' => t('Yes / no'),
            'true' => UI::prettyBool(true)->makeYesNo(),
            'false' => UI::prettyBool(false)->makeYesNo()
        ),
        array(
            'label' => t('Active / inactive'),
            'true' => UI::prettyBool(true)->makeActiveInactive(),
            'false' => UI::prettyBool(false)->makeActiveInactive()
        )
    );

?><table class="table">
    <thead>
        <tr>
            <th class="align-right"><?php pt('Description') ?></th>
            <th class="align-center"><?php pt('True value') ?></th>
            <th class="align-center"><?php pt('False value') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($booleans as $boolean)
        {
            ?>
            <tr>
                <td class="align-right"><?php echo $boolean['label'] ?></td>
                <td class="align-center"><?php echo $boolean['true']; ?></td>
                <td class="align-center"><?php echo $boolean['false']; ?></td>
            </tr>
            <?php
        }
    ?>
    </tbody>
</table>