<?php
/**
 * Created by Navatech.
 * @project    nic
 * @author     Phuong
 * @email      phuong17889[at]gmail.com
 * @created    04/02/2016 2:34 CH
 * @updated    03/03/2016 00:38 SA
 * @since      2.0.0
 */
namespace navatech\language\controllers;

use navatech\language\web\LanguageController;
use navatech\language\models\Language;
use navatech\language\models\Phrase;
use navatech\language\models\search\PhraseSearch;
use navatech\language\models\PhraseTranslate;
use navatech\language\Module;
use navatech\language\Translate;
use Yii;
use yii\base\ExitException;
use yii\base\InvalidParamException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * Controller is the base class of web controllers.
 */
class PhraseController extends LanguageController {

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
					'index'  => Translate::lists(),
					'delete' => Translate::delete(),
				],
			];
		}
		return $behaviors;
	}

	/**
	 * @return string
	 * @since 1.0.0
	 * @throws ExitException|InvalidParamException
	 */
	public function actionIndex() {
		$searchModel  = new PhraseSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		if (Yii::$app->request->post('hasEditable')) {
			$post        = Yii::$app->request->post();
			$out         = Json::encode([
				'output'  => '',
				'message' => '',
			]);
			$language_id = Language::getIdByCode($post['editableAttribute']);
			$phrase_id   = $post['editableKey'];
			if ($language_id !== 0 && $phrase_id !== 0) {
				$model = PhraseTranslate::findOne([
					'phrase_id'   => $phrase_id,
					'language_id' => $language_id,
				]);
				if ($model === null) {
					$model              = new PhraseTranslate();
					$model->language_id = $language_id;
					$model->phrase_id   = $phrase_id;
				}
				$model->value = $post['Phrase'][$post['editableIndex']][$post['editableAttribute']];
				$model->save();
				$out = Json::encode([
					'output'  => $model->value,
					'message' => '',
				]);
			}
			echo $out;
			Yii::$app->end();
		}
		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
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
	 * @return Phrase the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 * @since 1.0.0
	 */
	protected function findModel($id) {
		if (($model = Phrase::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}