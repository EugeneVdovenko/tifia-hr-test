<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Построение реферального дерева
     */
    public function actionReferalTree()
    {
        $request = Yii::$app->request;
        $id = $request->get('client', '82824897');
        $deep = $request->get('deep', '3');

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
        $referal = Yii::$app->db->createCommand($sql)->queryAll();

        // преобразуем масив во влоенную структуру
        $nested = array_reduce($referal, function ($nested, $client) {
            if ($client['partner_id'] > 0) {
                $nested[$client['partner_id']][] = [$client['client_uid']];
            }
            return $nested;
        }, []);

        return $this->render('referal-tree', compact('referal', 'nested'));
    }
}
