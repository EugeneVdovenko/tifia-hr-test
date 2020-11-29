<?php

namespace app\controllers;

use app\services\ClientService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /** @var ClientService */
    protected $clientService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->clientService = \Yii::$app->get(ClientService::class);
    }

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
        $time_begin = microtime(true);
        $request = Yii::$app->request;

        $id = $request->get('client', '82824897');
        $referalTree = $this->clientService->getReferalTree($id);

        // преобразуем масив
        $nested = $this->clientService->getReferalTreeUids($referalTree);
        $tree = $this->clientService->getReferalTreeView($nested, $id);

        $allReferalsCount = $this->clientService->getReferalCount($id, true);
        $nearReferalsCount = $this->clientService->getReferalCount($id, false);
        $heightTree = $this->clientService->getTreeHeight($id);
        $profit = 'Просчитывается отдельно';
        $sumVolumeTree = 'Просчитывается отдельно';

        return $this->render('referal-tree', compact(
            'time_begin',
            'tree',
            'allReferalsCount',
            'nearReferalsCount',
            'heightTree',
            'profit',
            'sumVolumeTree'));
    }
}
