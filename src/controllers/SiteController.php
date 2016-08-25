<?php
/**
 * Created by Navatech.
 * @project    Yii2 Multi Language
 * @author     Phuong
 * @email      phuong17889[at]gmail.com
 * @created    04/02/2016 2:34 CH
 * @updated    03/03/2016 00:38 SA
 * @since      2.0.0
 */
namespace navatech\language\controllers;

use navatech\language\models\Language;
use navatech\language\models\search\LanguageSearch;
use navatech\language\Module;
use navatech\language\Translate;
use navatech\language\web\LanguageController;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Controller is the base class of web controllers.
 */
class SiteController extends LanguageController {

	/**
	 * Returns a list of behaviors that this component should behave as.
	 *
	 * Child classes may override this method to specify the behaviors they want to behave as.
	 *
	 * The return value of this method should be an array of behavior objects or configurations
	 * indexed by behavior names. A behavior configuration can be either a string specifying
	 * the behavior class or an array of the following structure:
	 *
	 * ```php
	 * 'behaviorName' => [
	 *     'class' => 'BehaviorClass',
	 *     'property1' => 'value1',
	 *     'property2' => 'value2',
	 * ]
	 * ```
	 *
	 * Note that a behavior class must extend from [[Behavior]]. Behavior names can be strings
	 * or integers. If the former, they uniquely identify the behaviors. If the latter, the corresponding
	 * behaviors are anonymous and their properties and methods will NOT be made available via the component
	 * (however, the behaviors can still respond to the component's events).
	 *
	 * Behaviors declared in this method will be attached to the component automatically (on demand).
	 *
	 * @return array the behavior configurations.
	 */
	public function behaviors() {
		$behaviors = [
			'verbs' => [
				'class'   => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
				],
			],
		];
		if (Module::hasUserRole()) {
			$behaviors['role'] = [
				'class'   => \navatech\role\filters\RoleFilter::className(),
				'name'    => Translate::x_management([Translate::language()]),
				'actions' => [
					'list'   => Translate::lists(),
					'create' => Translate::create(),
					'update' => Translate::update(),
					'delete' => Translate::delete(),
				],
			];
		}
		return $behaviors;
	}

	/**
	 * @return string
	 * @since 1.0.0
	 * @throws InvalidParamException if the model cannot be found
	 */
	public function actionIndex() {
		$searchModel  = new LanguageSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		return $this->render('/language/list', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Creates a new Language model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 * @since 1.0.0
	 * @throws InvalidParamException if the model cannot be found
	 */
	public function actionCreate() {
		$model = new Language();
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->getSession()->setFlash('message', 'Created');
				return $this->redirect(['list']);
			} else {
				Yii::$app->getSession()->setFlash('message', 'Something went wrong!');
			}
		}
		return $this->render('/language/create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Language model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @since 1.0.0
	 * @throws NotFoundHttpException|InvalidParamException if the model cannot be found
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->getSession()->setFlash('message', 'Updated!');
				return $this->redirect(['list']);
			} else {
				Yii::$app->getSession()->setFlash('message', 'Something went wrong!');
			}
		}
		return $this->render('/language/update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Financial model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 */
	public function actionDelete($id) {
		$this->findModel($id)->delete();
		if (!Yii::$app->request->get('project') && !Yii::$app->request->isAjax) {
			return $this->redirect(Yii::$app->request->referrer);
		}
		return false;
	}

	/**
	 * Finds the Language model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Language the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 * @since 1.0.0
	 */
	protected function findModel($id) {
		if (($model = Language::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}