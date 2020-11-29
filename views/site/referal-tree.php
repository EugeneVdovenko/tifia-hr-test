<?php

/* @var $this yii\web\View */
/* @var $time_begin float */
/* @var $nested array */
/* @var $profit int */
/* @var $sumVolumeTree int */
/* @var $tree string */
/* @var $allReferalsCount int */
/* @var $nearReferalsCount int */
/* @var $heightTree int */
use yii\helpers\Html;

$this->title = 'Реферальное дерево';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-referal">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Выбираем рефераллов пользоаателя</p>

    <p>Количество прямых рефералов <?= $nearReferalsCount ?></p>
    <p>Количество рефералов <?= $allReferalsCount ?></p>
    <p>Высота: <?= $heightTree ?></p>
    <p>Прибыльность: <?= $profit ?></p>
    <p>Cуммарный объем: <?= $sumVolumeTree ?></p>
<pre>
<?= $tree  ?>
</pre>
</div>

<hr>

<p>Время генерации страницы: около <?= round((microtime(true) - $time_begin)) ?> секунд</p>
