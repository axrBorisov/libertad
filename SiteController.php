<?php
declare(strict_types=1);

namespace frontend\controllers;

use core\entities\Calls\CallRequest;
use core\entities\Faq\Faq;
use core\entities\OurClients\OurClients;
use core\entities\Testimonials\Testimonials;
use core\helpers\CallRequestHelper;
use core\services\Seo\OpenGraph\OgMetaTagsService;
use core\readModels\Site\StaticInfoReadRepository;
use core\useCases\Calls\RecallService;
use core\useCases\Messages\MessageService;
use frontend\forms\SendMessageForm;
use Yii;
use yii\base\Module;
use yii\helpers\Url;
use yii\httpclient\Exception;
use yii\web\Controller;
use frontend\forms\RecallForm;
use yii\web\Response;

class SiteController extends Controller
{
    private $og;
    private $staticInfoReadRepository;
    private $ourClients;
    private $testimonials;
    private $faq;
    private $recallService;
    private $messageService;

    public function __construct(
        string $id, Module $module,
        OgMetaTagsService $og,
        RecallService $recallService,
        MessageService $messageService,
        StaticInfoReadRepository $staticInfoReadRepository,
        OurClients $urClients,
        Testimonials $testimonials,
        Faq $faq,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->og = $og;
        $this->staticInfoReadRepository = $staticInfoReadRepository;
        $this->ourClients = $urClients;
        $this->testimonials = $testimonials;
        $this->faq = $faq;
        $this->recallService = $recallService;
        $this->messageService = $messageService;
    }


    public function behaviors(): array
    {
        return [
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['index'],
                'lastModified' => function ($action, $params) {
                    return (new \DateTime())->modify('+ 5 day')->getTimestamp();
                },
            ],
        ];
    }

    public function actionIndex(): string
    {
        $title = 'ЛИБЕРТАД';
        $descriptionContent = '';

        $recallForm = new RecallForm();

        $this->seo($title, $descriptionContent);
        return $this->render('index', [
            'descriptionContent' => $descriptionContent,
            'title' => $title,
            'recallForm' => $recallForm,
            'staticInfo' => $this->staticInfoReadRepository->getStatic(),
            'ourClients' => $this->ourClients,
            'testimonials' => $this->testimonials,
            'faq' => $this->faq,
        ]);
    }

    public function actionRecall(): array
    {
        $form = new RecallForm();

        if (!Yii::$app->request->isAjax) {
            throw new Exception('Некорректный формат.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            try {
                if ($this->findModel(CallRequestHelper::cleanPhoneNumber($form->phone))) {
                    return [
                        'text' => 'Ваша заявка уже принята, мы скоро Вам позвоним.',
                        'error' => true,
                    ];
                }
                $this->recallService->recallRequest($form);
                return ['text' => 'Благодарим Вас за обращение. Мы свяжемся с Вами в кротчайшие сроки.'];
            } catch (\Exception $e) {
                Yii::$app->errorHandler->logException($e);
            }
        }
    }

    public function actionSendMessage(): array
    {
        $form = new SendMessageForm();

        if (!Yii::$app->request->isAjax) {
            throw new Exception('Некорректный формат.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            try {
                $this->messageService->messageRequest($form);
                return ['text' => 'Благодарим Вас за обращение. Мы свяжемся с Вами в кротчайшие сроки.'];
            } catch (\Exception $e) {
                Yii::$app->errorHandler->logException($e);
            }
        }
    }

    protected function findModel(string $phone): ?CallRequest
    {
        return CallRequest::findOne(['phone' => $phone, 'status' => CallRequest::STATUS_CALL_WAITING]);
    }

    private function seo(string $title, string $descriptionContent): void
    {
        $this->og->facebookMetaTags([
            'og:url' => Url::canonical(),
            'og:type' => 'website',
            'og:title' => $title,
            'og:description' => $descriptionContent,
            'og:image' => Url::to('@web/image/og/default/og_default.jpeg', true),
        ]);

        $this->og->twitterMetaTags([
            'twitter:site' => Url::canonical(),
            'twitter:title' => $title,
            'twitter:description' => $descriptionContent,
            'twitter:image:src' => Url::to('@web/image/og/default/og_default.jpeg', true),
            'twitter:card' => 'summary',
        ]);

        $this->og->googlePlusMetaTags([
            'name' => $title,
            'description' => $descriptionContent,
            'image' => Url::to('@web/image/og/default/og_default.jpeg', true),
        ]);
    }
}
