<?php
declare(strict_types=1);

namespace backend\controllers;

use core\entities\Testimonials\Testimonials;
use core\forms\manage\testimonials\TestimonialsForm;
use core\readModels\Testimonials\TestimonialsReadRepository;
use core\useCases\manage\TestimonialsService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TestimonialsController extends Controller
{
    private $service;
    private $readRepository;
    private $testimonials;

    public function __construct(
        $id, $module,
        TestimonialsService $service,
        TestimonialsReadRepository $readRepository,
        Testimonials $testimonials,
        array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->readRepository = $readRepository;
        $this->service = $service;
        $this->testimonials = $testimonials;
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = $this->readRepository->getAll();

        return $this->render('view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $form = new TestimonialsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->create($form);
                Yii::$app->session->setFlash('success', 'Отзыв добавлен.');
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    public function actionUpdate($id)
    {
        $testimonials = $this->findModel($id);
        $form = new TestimonialsForm($testimonials);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($testimonials, $form);
                Yii::$app->session->setFlash('success', 'Отзыв отредактирован.');
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('update', [
            'model' => $form,
            'testimonials' => $testimonials,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
            Yii::$app->session->setFlash('success', 'Отзыв удален.');
        } catch (\DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    protected function findModel($id): Testimonials
    {
        if (($model = Testimonials::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Отзыв не найден.');
    }
}