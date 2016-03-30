<?php

namespace Plugin\Point\Controller;

use Eccube\Application;
use Plugin\Point\Form\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointController
 * ポイント設定画面用コントローラー
 * Class FrontPointController
 * @package Plugin\Point\Controller
 */
class FrontPointController
{
    /** @var Application */
    protected $app;

    /**
     * FrontPointController constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * 利用ポイント入力画面
     * @param Application $app
     * @param Request $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function usePoint(Application $app, Request $request)
    {
        // 権限判定
        if (!$this->app->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new HttpException\NotFoundHttpException;
        }

        $cartService = $this->app['eccube.service.cart'];

        // カートチェック
        if (!$cartService->isLocked()) {
            // カートが存在しない、カートがロックされていない時はエラー
            return $this->app->redirect($this->app->url('cart'));
        }

        $Order = $this->app['eccube.service.shopping']->getOrder($this->app['config']['order_processing']);
        if (!$Order) {
            $this->app->addError('front.shopping.order.error');

            return $this->app->redirect($this->app->url('shopping_error'));
        }

        // 最終保存のポイント設定情報取得
        $usePoint = 0;
        if ($this->app['session']->has('usePoint')) {
            $usePoint = $this->app['session']->get('usePoint');
        }

        // 必要エンティティ取得
        $customer = $this->app['security']->getToken()->getUser();

        // 計算用ヘルパー呼び出し
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算に必要なエンティティを格納
        $calculator->addEntity('Customer', $customer);
        $calculator->addEntity('Order', $Order);
        $calculator->setUsePoint($usePoint);

        // 保有ポイント
        $point = $calculator->getPoint();
        // 加算ポイント
        $addPoint = $calculator->getAddPointByOrder();
        // 合計金額
        $total = number_format($calculator->getTotalAmount());


        // ポイント換算レート
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        if (empty($pointInfo)) {
            return false;
        }
        $pointRate = $pointInfo->getPlgBasicPointRate();

        //フォーム生成
        $form = $this->app['form.factory']
            ->createBuilder()->add(
                'plg_use_point',
                'text',
                array(
                    'label' => '利用ポイント',
                    'required' => false,
                    'mapped' => false,
                    'empty_data' => '',
                    'data' => $usePoint,
                    'attr' => array(
                        'placeholder' => '使用するポイントを入力 例. 1',
                    ),
                    'constraints' => array(
                        new Assert\LessThanOrEqual(
                            array(
                                'value' => $point,
                                'message' => 'front.point.enter.usepointe.error',
                            )
                        ),
                        new Assert\Regex(
                            array(
                                'pattern' => "/^\d+$/u",
                                'message' => 'form.type.numeric.invalid',
                            )
                        ),
                    ),
                )
            )->getForm();

        $form->handleRequest($request);
        // 保存処理
        if ($form->isSubmitted() && $form->isValid()) {
            $saveData = $form->get('plg_use_point')->getData();
            $this->app['session']->set('usePoint', $saveData);

            return $this->app->redirect($this->app->url('shopping'));
        }

        // フォーム項目名称描画用文字配
        return $app->render(
            'Point/Resource/template/default/point_use.twig',
            array(
                'form' => $form->createView(),
                'usePoint' => $usePoint,
                'pointRate' => $pointRate,
                'point' => $point,
                'total' => $total,
            )
        );
    }
}
