<?php

namespace app\controllers;

use app\components\SquareWidget;
use app\models\History;
use JonnyW\PhantomJs\Client as ClientPh;
use mikehaertl\wkhtmlto\Image;
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
	 * @param null $var
	 * @return string
	 */
    public function actionIndex($var = null)
    {
    	if($var==1){
    		$this->layout = 'clean';
    		return $this->renderContent(SquareWidget::widget());
	    }else{
    		return $this->render('index');
	    }
    }

	public function actionImg($var)
	{
		$client = ClientPh::getInstance();

		$width  = 800;
		$height = 600;
		$top    = 0;
		$left   = 0;

		/**
		 * @see \JonnyW\PhantomJs\Http\CaptureRequest
		 **/
		$request = $client->getMessageFactory()->createCaptureRequest('http://jonnyw.me', 'GET');
		$request->setOutputFile('file.jpg');
		$request->setViewportSize($width, $height);
		$request->setCaptureDimensions($width, $height, $top, $left);

		/**
		 * @see \JonnyW\PhantomJs\Http\Response
		 **/
		$response = $client->getMessageFactory()->createResponse();

		// Send the request
		$client->send($request, $response);

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
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
