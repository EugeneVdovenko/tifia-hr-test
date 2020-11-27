<?php

/* @var $this yii\web\View */
/* @var $referal array */

use yii\helpers\Html;

$this->title = 'Реферальное дерево';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-referal">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Выбираем рефераллов пользоаателя
    </p>
    <?php foreach ($referal as $client) {?>
    <pre><?php print_r($client) ?></pre>
    <?php } ?>


</div>
