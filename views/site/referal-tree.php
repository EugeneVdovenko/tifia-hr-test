<?php

/* @var $this yii\web\View */
/* @var $referal array */
/* @var $nested array */

use yii\helpers\Html;

$this->title = 'Реферальное дерево';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-referal">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Выбираем рефераллов пользоаателя
    </p>
<pre>
<?php printRef($nested, getFirstKey($nested)); ?>
</pre>

</div>

<?php
function printRef($all, $current, $tabs = '')
{
    print("\n{$tabs} $current");
    if (array_key_exists($current, $all)) {
        $tabs = " |   " . $tabs;
        foreach ($all[$current] as $refs) {
            foreach ($refs as $ref) {
                printRef($all, $ref, $tabs);
            }
        }
    }
}

function getFirstKey(array $all)
{
    foreach ($all as $k => $v) {
        return $k;
    }
}

?>