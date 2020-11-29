<?php

namespace app\services;

class ClientService
{
    protected $tree = [];
    protected $treeHeight = 0;

    public function __construct()
    {
        //
    }

    /**
     * Все участники одной реферальной сети
     *
     * @param int $id
     *
     * @return array
     *
     * @throws \yii\db\Exception
     */
    public function getReferalTree(int $id): array
    {
        if (empty($this->tree)) {
            $sql = 'WITH RECURSIVE rels (client_uid, partner_id, id, fullname, email) AS (
                    SELECT client_uid, partner_id, id, fullname, email
                    FROM users
                    WHERE client_uid = %s
                    UNION ALL
                    SELECT u2.client_uid, u2.partner_id, u2.id, u2.fullname, u2.email
                    FROM users u2 JOIN rels u1 ON u2.partner_id = u1.client_uid
                )
                SELECT client_uid, partner_id, id, fullname, email
                FROM rels
                GROUP BY client_uid, partner_id
                ORDER BY id;';
            $sql = sprintf($sql, $id);

            /** @var array $referal */
            $this->tree = \Yii::$app->db->createCommand($sql)->queryAll();
        }

        return $this->tree;
    }

    /**
     * Массивы прямых рефералов реферального дерева
     * Нужно для построения реферального дерева
     *
     * @param array $referal
     *
     * @return mixed
     */
    public function getReferalTreeUids(array $referal)
    {
        // преобразуем масив во влоенную структуру
        return array_reduce($referal, function ($nested, $client) {
            if ($client['partner_id'] > 0) {
                $nested[$client['partner_id']][] = [$client['client_uid']];
            }
            return $nested;
        }, []);
    }

    public function getReferalTreeView(array $nested, $current, $level = 0)
    {
        $this->setTreeHeight($level);
        $tabs = ' |  ';
        $tree = sprintf("\n%s %s (глубина - %d)", str_repeat($tabs, $level), $current, $level);
        if (array_key_exists($current, $nested)) {
            foreach ($nested[$current] as $referals) {
                foreach ($referals as $referal) {
                    $tree .= $this->getReferalTreeView($nested, $referal, $level + 1);
                }
            }
        }

        return $tree;

    }

    public function getReferalCount($id, $all = false)
    {
        if ($all) {
            return count($this->getReferalTree($id));
        }

        return array_reduce($this->getReferalTree($id), function ($count, $client) use ($id) {
            if ($client['partner_id'] === $id) {
                $count++;
            }
            return $count;
        }, 0);
    }

    public function getTreeHeight(): int
    {
        return $this->treeHeight;
    }

    protected function setTreeHeight(int $height): void
    {
        if ($this->treeHeight < $height) {
            $this->treeHeight = $height;
        }
    }

    public function getCompute($id, $timeFrom, $timeTo)
    {
        $time_begin = microtime(true);
        /** @var array $profit */
        $clients = $this->getReferalTree($id);
        $uids = array_reduce($clients, function ($uids, $client) {
            $uids[] = $client['client_uid'];
            return $uids;
        }, []);
        $uids = implode(', ', $uids);

        printf("\nЗапрос клиентов выполнен за %d сек.", (microtime(true) - $time_begin));

        $sql = "WITH profit (login, profit, sum) AS (
                    SELECT t.login, t.profit, t.volume * t.coeff_h * t.coeff_cr as sum
                    FROM trades t
                    WHERE t.open_time > '%s' AND t.close_time < '%s'
                )
                SELECT t.profit, t.sum
                FROM profit t
                JOIN accounts a on t.login = a.login
                WHERE a.client_uid IN (%s)";

        $sql = sprintf($sql, $timeFrom, $timeTo, $uids);

        /** @var array $profit */
        $profit = \Yii::$app->db->createCommand($sql)->queryAll();

        printf("\nЗапрос выполнен за %d сек.", (microtime(true) - $time_begin));

        return array_reduce($profit, function ($res, $trade) {
            $res['profit'] += $trade['profit'];
            $res['sum'] += $trade['sum'];
            return $res;
        }, ['profit' => 0, 'sum' => 0]);
    }

}