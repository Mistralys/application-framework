<?php

/* @var $this UI_Page_Template */
/* @var $monitor Application_HealthMonitor */


$monitor = $this->getVar('monitor');
$states = $monitor->getStateDefs();

ob_start();
?>
<div class="pull-right">
    <a href="?format=text" class="btn">Plaintext</a>
    <a href="?format=xml" class="btn">XML</a>
</div>
<h1><?php echo $this->driver->getAppNameShort() ?> system monitor</h1>
<section>
    <table class="table">
        <tbody>
        <tr>
            <th>Global status</th>
            <td width="80%"><?php echo $states[$monitor->getGlobalState()] ?></td>
        </tr>
        <tr>
            <th>Errors</th>
            <td><?php echo $monitor->countErrors() ?></td>
        </tr>
        <tr>
            <th>Warnings</th>
            <td><?php echo $monitor->countWarnings() ?></td>
        </tr>
        </tbody>
    </table>
    <div>
        <b>Note:</b> By default, viewing this URL with a browser will display the HTML version. By sending <code>text/xml</code>
        in the request accept header with the highest priority, the XML version will be served. Alternatively, you
        may use the buttons above to view an alternate format in your browser.
    </div>
</section>

<h1>Systems</h1>
<section>
    <?php
    $components = $monitor->getComponents();
    
    foreach ($components as $component) {
        ?>
        <h2><?php echo $component->getName() ?></h2>
        <table class="table">
            <tbody>
            <tr>
                <th>Status</th>
                <td width="80%"><?php echo $states[$component->getState()] ?></td>
            </tr>
            <?php if ($component->hasMessage()) { ?>
                <tr>
                    <th>Message</th>
                    <td><?php echo $component->getMessage() ?></td>
                </tr>
            <?php } ?>
            <?php if ($component->hasException()) { ?>
                <tr>
                    <th>Exception</th>
                    <td><?php echo $component->getException() ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th>Description</th>
                <td><?php echo $component->getDescription() ?></td>
            </tr>
            <tr>
                <th>Severity</th>
                <td><?php echo $component->getSeverity() ?></td>
            </tr>
            <tr>
                <th>Yellow pages URL</th>
                <td><a href="<?php echo $component->getYellowPagesURL() ?>"><?php echo $component->getYellowPagesURL() ?></a>
                </td>
            </tr>
            <?php if ($component->hasDuration()) { ?>
                <tr>
                    <th>Duration</th>
                    <td><?php echo $component->getDuration() ?> ms</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php
    }
    ?>
</section>
<?php 

echo $this->renderCleanFrame(ob_get_clean(), 'System monitor - '.$this->driver->getAppNameShort());

