<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\services\ClientService;
use yii\console\Controller;
use yii\console\ExitCode;

class TreeController extends Controller
{
    /**
     * @param int $id
     *
     * @return int Exit code
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCompute($id = '82824897', $dateFrom = null, $dateTo = null)
    {
        /** @var ClientService $clientService */
        $clientService = \Yii::$app->get(ClientService::class);

        $dateFrom = $dateFrom ?? date_create('2019-03-05 00:00:00')->format('Y-m-d H:i:s');
        $dateTo = $dateTo ?? date_create('2019-03-07 00:00:00')->format('Y-m-d H:i:s');

        $res = $clientService->getCompute(
            $id,
            $dateFrom,
            $dateTo
        );

        printf("`\n Прибыльность реферальной сети %d: %d\n Суммарный объем сети %d: %d\n", $id, $res['profit'], $id, $res['sum']);

        return ExitCode::OK;
    }
}
