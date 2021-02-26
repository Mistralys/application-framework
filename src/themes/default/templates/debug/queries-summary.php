<?php

/* @var $this UI_Page_Template */

echo '<div class="container" style="background:#fff;padding-top:20px;padding-bottom:100px">';

$grid = $this->ui->createPropertiesGrid();
$grid->add(t('Total query count'), DBHelper::getQueryCount());
$grid->add(t('Write operations'), DBHelper::countWriteQueries());
$grid->add(t('Select operations'), DBHelper::countSelectQueries());

$this->createSection()
->collapse()
->setTitle(t('Query information'))
->setContent($grid->render())
->display();

$queries = DBHelper::getWriteQueries();
$section = $this->createSection()
->collapse()
->setTitle(t('Write queries').' ('.DBHelper::countWriteQueries().')')
->startCapture();
foreach ($queries as $query) {
    $sql = trim($query[0]);
    $sql = DBHelper::formatQuery($sql, $query[1]);
    echo AppUtils\Highlighter::sql(AppUtils\ConvertHelper::normalizeTabs($sql, true));
}
$section->display();

echo '</div>';
