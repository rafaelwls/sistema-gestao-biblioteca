<?php

namespace backend\controllers;

use common\models\Livros;
use backend\models\LivrosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;

/**
 * LivrosController implements the CRUD actions for Livros model.
 */
class LivrosController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1) JWT Bearer Auth para todas as ações
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        // 2) Controle de acesso: apenas usuários logados, e regras por ação
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                // qualquer usuário autenticado pode criar (pedido ou doação)
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => ['@'],
                ],
                // só trabalhador/admin pode index/view/update/delete/resposta/aprovar
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'update', 'delete', 'resposta', 'aprovar'],
                    'matchCallback' => function ($rule, $action) {
                        /** @var \common\models\Usuarios $u */
                        $u = Yii::$app->user->identity;
                        return $u->isTrabalhador() || $u->isAdmin();
                    },
                ],
            ],
        ];

        // 3) VerbFilter para métodos HTTP
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'resposta' => ['POST'],
                'aprovar' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Lists all Livros models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LivrosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Livros model.
     * @param string $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Livros::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Livro não encontrado.');
        }
        // renderiza view em views/livros/view.php
        return $this->render('view', [
            'model' => $model,
        ]);
    }


    /**
     * Creates a new Livros model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Livros();
        $exemplar = new Exemplares();
        $post = Yii::$app->request->post();

        if (
            $model->load($post) &&
            $exemplar->load($post) &&
            $model->validate() &&
            $exemplar->validate(['quantidade', 'estado', 'codigo_barras', 'data_aquisicao'])
        ) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // salva todos os atributos do livro
                $model->save(false);

                // preenche e salva o exemplar
                $exemplar->livro_id = $model->id;
                $exemplar->status = $model->status;
                $exemplar->save(false);

                $transaction->commit();
                return $this->redirect(['index']);
            } catch (\yii\db\IntegrityException $e) {
                $transaction->rollBack();
                // tratamento de erro igual ao já implementado...
            } catch (\Exception $e) {
                $transaction->rollBack();
                // tratamento genérico...
            }
        }

        return $this->render('//livro/create', [
            'model' => $model,
            'exemplar' => $exemplar,
        ]);
    }



    /**
     * Updates an existing Livros model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['//livro/view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Livros model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Livros model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id ID
     * @return Livros the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Livros::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
