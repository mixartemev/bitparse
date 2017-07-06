<?php

namespace app\controllers;

use app\models\History;
use Yii;
use yii\filters\AccessControl;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @param string $cur
     *
     * @return string|Response
     */
    public function actionSquare($cur = 'rub')
    {
        $client = new Client();
        $response = $client->createRequest()
                           ->setMethod('get')
                           ->setUrl('https://api.coinmarketcap.com/v1/ticker/')
                           ->setData(['convert' => $cur, 'limit' => 4])
                           ->send();
        if ($response->isOk) {
            $model = [];
            foreach($response->data as $ar) {
                //var_dump($ar);//die;
                $model[] = [
                    'name'               => $ar['symbol'],
                    'price'              => $ar[ 'price_' . $cur ],
                    'market_cap'         => $ar[ 'market_cap_' . $cur ],
                    'percent_change_1h'  => $ar['percent_change_1h'],
                    'percent_change_24h' => $ar['percent_change_24h'],
                    'percent_change_7d'  => $ar['percent_change_7d'],
                ];
            }
            return $this->render('_square', [
                'model' => $model,
                'cur' => $cur
            ]);
        }
        return false;
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
}
